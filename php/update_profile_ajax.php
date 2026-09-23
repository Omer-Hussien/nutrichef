<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ============================================================
// php/update_profile_ajax.php — AJAX Profile Quick-Update
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
startSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$user  = getCurrentUser();
$pdo   = getDB();
$field = $_POST['field'] ?? '';
$value = sanitize($_POST['value'] ?? '');

$allowed = [
    'health_goal'        => ['Lose Weight','Maintain Weight','Gain Weight','Build Muscle'],
    'dietary_preference' => ['None','Vegetarian','Vegan','Gluten-Free','Keto','Paleo'],
    'activity_level'     => ['Sedentary','Lightly Active','Moderately Active','Very Active','Extra Active'],
    'weight_kg'          => null,   // numeric
    'height_cm'          => null,   // numeric
];

if (!array_key_exists($field, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Field not allowed.']);
    exit;
}

// Validate ENUM fields
if (is_array($allowed[$field]) && !in_array($value, $allowed[$field])) {
    echo json_encode(['success' => false, 'message' => 'Invalid value.']);
    exit;
}

// Validate numeric fields
if ($allowed[$field] === null) {
    $value = (float)$value;
    if ($value <= 0 || $value > 500) {
        echo json_encode(['success' => false, 'message' => 'Value out of range.']);
        exit;
    }
}

$stmt = $pdo->prepare("UPDATE users SET $field = ? WHERE user_id = ?");
$stmt->execute([$value, $user['user_id']]);

// Return updated daily calories
$updatedUser = getCurrentUser();
$dailyCal    = calculateDailyCalories($updatedUser);
$bmiData     = ($updatedUser['weight_kg'] && $updatedUser['height_cm'])
    ? calculateBMI((float)$updatedUser['weight_kg'], (float)$updatedUser['height_cm'])
    : null;

echo json_encode([
    'success'    => true,
    'message'    => ucfirst(str_replace('_',' ',$field)) . ' updated!',
    'daily_cal'  => $dailyCal,
    'bmi'        => $bmiData['bmi']      ?? null,
    'bmi_cat'    => $bmiData['category'] ?? null,
]);

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
