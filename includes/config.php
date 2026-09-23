<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ============================================================
// Smart Recipe Recommendation and Nutrition Analysis System
// Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_recipe_db');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'NutriChef');
define('SITE_URL', 'http://localhost/DS1G_Smart_Recipe_Recommendation_and_Nutrition_Analysis_System/Program Files/Codes');
define('UPLOAD_PATH', __DIR__ . '/../images/');
define('SESSION_TIMEOUT', 3600); // 1 hour

/**
 * Get PDO database connection
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed. Please try again later.']));
        }
    }
    return $pdo;
}
