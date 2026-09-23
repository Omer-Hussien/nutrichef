<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ============================================================
// Authentication & Session Functions
// ============================================================
require_once __DIR__ . '/config.php';

/**
 * Start secure session
 */
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_TIMEOUT,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login - redirect if not authenticated
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit;
    }
}

/**
 * Get current user data
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Register new user (Enforces required physical stats)
 */
function registerUser(array $data): array {
    $pdo = getDB();

    // Validate account AND physical inputs strictly
    if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['full_name']) || empty($data['age']) || empty($data['weight']) || empty($data['height'])) {
        return ['success' => false, 'message' => 'All account details and physical information fields must be completed.'];
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address format.'];
    }
    if (strlen($data['password']) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }
    if (!preg_match('/[A-Z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
        return ['success' => false, 'message' => 'Password must contain at least one uppercase letter and one number.'];
    }

    // Check uniqueness
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$data['username'], $data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email is already registered.'];
    }

    $hash = password_hash($data['password'], PASSWORD_BCRYPT);
    
    // Explicitly binding all 11 columns to preserve user Activity Level:
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, age, gender, weight_kg, height_cm, activity_level, dietary_preference, health_goal) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        htmlspecialchars(trim($data['username'])),
        trim($data['email']),
        $hash,
        htmlspecialchars(trim($data['full_name'])),
        (int)$data['age'],
        $data['gender'] ?? 'Other',
        (float)$data['weight'],
        (float)$data['height'],
        $data['activity_level'] ?? 'Moderately Active',
        $data['dietary_preference'] ?? 'None',
        $data['health_goal'] ?? 'Maintain Weight'
    ]);

    return ['success' => true, 'message' => 'Registration successful! Please log in.'];
}

/**
 * Login user
 */
function loginUser(string $username, string $password): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }

    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];

    return ['success' => true, 'message' => 'Login successful!'];
}

/**
 * Logout
 */
function logoutUser(): void {
    startSession();
    $_SESSION = [];
    session_destroy();
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

/**
 * Sanitize string input
 */
function sanitize(string $input): string {
    return strip_tags(trim($input));
}

/**
 * Calculate BMI
 */
function calculateBMI(float $weight, float $height): array {
    if ($height <= 0) return ['bmi' => 0, 'category' => 'Unknown'];
    $heightM = $height / 100;
    $bmi = round($weight / ($heightM * $heightM), 1);
    $category = match(true) {
        $bmi < 18.5 => 'Underweight',
        $bmi < 25.0 => 'Normal weight',
        $bmi < 30.0 => 'Overweight',
        default     => 'Obese'
    };
    return ['bmi' => $bmi, 'category' => $category];
}

/**
 * Calculate daily calorie needs (Mifflin-St Jeor)
 */
function calculateDailyCalories(array $user): float {
    $w = (float)$user['weight_kg'];
    $h = (float)$user['height_cm'];
    $a = (int)$user['age'];

    if ($user['gender'] === 'Male') {
        $bmr = (10 * $w) + (6.25 * $h) - (5 * $a) + 5;
    } else {
        $bmr = (10 * $w) + (6.25 * $h) - (5 * $a) - 161;
    }

    $multiplier = match($user['activity_level']) {
        'Sedentary'          => 1.2,
        'Lightly Active'     => 1.375,
        'Moderately Active'  => 1.55,
        'Very Active'        => 1.725,
        'Extra Active'       => 1.9,
        default              => 1.55
    };

    return round($bmr * $multiplier);
}
