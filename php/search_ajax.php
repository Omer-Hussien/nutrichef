<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// php/search_ajax.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
startSession();

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode([]); exit; }

$userId = isLoggedIn() ? (getCurrentUser()['user_id'] ?? null) : null;
$results = searchRecipes($q, $userId);
echo json_encode(array_slice($results, 0, 6));
