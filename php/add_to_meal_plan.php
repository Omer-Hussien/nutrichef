<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// php/add_to_meal_plan.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

startSession();
requireLogin();

$user     = getCurrentUser();
$recipeId = (int)($_POST['recipe_id'] ?? 0);
$date     = $_POST['plan_date'] ?? date('Y-m-d');
$mealType = $_POST['meal_type'] ?? 'Lunch';

$allowed = ['Breakfast','Lunch','Dinner','Snack'];
if (!$recipeId || !in_array($mealType, $allowed)) {
    header('Location: ../pages/recipe_detail.php?id=' . $recipeId . '&error=Invalid+input');
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("INSERT INTO meal_plans (user_id, recipe_id, plan_date, meal_type) VALUES (?,?,?,?)");
    $stmt->execute([$user['user_id'], $recipeId, $date, $mealType]);
    header("Location: ../pages/meal_plan.php?date={$date}&msg=added");
} catch (Exception $e) {
    header("Location: ../pages/recipe_detail.php?id={$recipeId}&error=Could+not+add+to+plan");
}
exit;
