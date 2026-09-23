<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// php/toggle_save.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
startSession();

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Please log in to save recipes.']);
    exit;
}

$recipeId = (int)($_GET['recipe_id'] ?? 0);
if (!$recipeId) {
    echo json_encode(['error' => 'Invalid recipe.']);
    exit;
}

$user   = getCurrentUser();
$result = toggleSaveRecipe($user['user_id'], $recipeId);
echo json_encode($result);
