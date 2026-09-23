<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// php/submit_review.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$user     = getCurrentUser();
$recipeId = (int)($_POST['recipe_id'] ?? 0);
$rating   = (int)($_POST['rating'] ?? 0);
$text     = $_POST['review_text'] ?? '';

$result = submitReview($user['user_id'], $recipeId, $rating, $text);
if ($result['success']) {
    header("Location: ../pages/recipe_detail.php?id={$recipeId}&msg=review_added");
} else {
    header("Location: ../pages/recipe_detail.php?id={$recipeId}&error=" . urlencode($result['message']));
}
exit;
