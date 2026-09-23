<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ============================================================
// php/delete_meal.php — Remove a Meal Plan Entry
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

startSession();

// Support both AJAX (JSON) and regular POST (redirect)
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isLoggedIn()) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    } else {
        header('Location: ../pages/login.php');
    }
    exit;
}

$planId  = (int)($_POST['plan_id'] ?? $_GET['plan_id'] ?? 0);
$user    = getCurrentUser();
$referer = $_SERVER['HTTP_REFERER'] ?? '../pages/meal_plan.php';

if (!$planId) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid plan ID.']);
    } else {
        header("Location: $referer");
    }
    exit;
}

$pdo  = getDB();
$stmt = $pdo->prepare("DELETE FROM meal_plans WHERE plan_id = ? AND user_id = ?");
$stmt->execute([$planId, $user['user_id']]);
$deleted = $stmt->rowCount();

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $deleted > 0,
        'message' => $deleted > 0 ? 'Meal removed from plan.' : 'Entry not found or not authorised.',
    ]);
} else {
    header("Location: $referer");
}
exit;
