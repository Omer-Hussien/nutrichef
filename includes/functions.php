<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ============================================================
// Recipe & Nutrition Helper Functions
// ============================================================
require_once __DIR__ . '/config.php';

/**
 * Get all recipes with optional filters
 */
function getRecipes(array $filters = [], int $limit = 12, int $offset = 0): array {
    $pdo = getDB();
    $sql = "SELECT r.*, c.category_name, c.icon, c.color_hex, u.username,
                   COALESCE(AVG(rv.rating), 0) AS rating_avg,
                   COUNT(DISTINCT rv.review_id) AS review_count
            FROM recipes r
            LEFT JOIN categories c ON r.category_id = c.category_id
            LEFT JOIN users u ON r.user_id = u.user_id
            LEFT JOIN reviews rv ON r.recipe_id = rv.recipe_id
            WHERE 1=1";
    $params = [];

    if (!empty($filters['category_id'])) {
        $sql .= " AND r.category_id = ?";
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['dietary_type'])) {
        $sql .= " AND r.dietary_type = ?";
        $params[] = $filters['dietary_type'];
    }
    if (!empty($filters['difficulty'])) {
        $sql .= " AND r.difficulty = ?";
        $params[] = $filters['difficulty'];
    }
    if (!empty($filters['search'])) {
        $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
    }
    if (!empty($filters['max_calories'])) {
        $sql .= " AND r.total_calories <= ?";
        $params[] = $filters['max_calories'];
    }
    if (isset($filters['featured']) && $filters['featured']) {
        $sql .= " AND r.is_featured = 1";
    }

    $sql .= " GROUP BY r.recipe_id";

    $orderBy = match($filters['sort'] ?? 'newest') {
        'calories_asc'  => 'r.total_calories ASC',
        'calories_desc' => 'r.total_calories DESC',
        'rating'        => 'rating_avg DESC',
        'popular'       => 'r.view_count DESC',
        default         => 'r.created_at DESC'
    };
    $sql .= " ORDER BY {$orderBy} LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get single recipe with full nutrition
 */
function getRecipeById(int $id): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        "SELECT r.*, c.category_name, c.icon, c.color_hex, u.username, u.full_name
         FROM recipes r
         LEFT JOIN categories c ON r.category_id = c.category_id
         LEFT JOIN users u ON r.user_id = u.user_id
         WHERE r.recipe_id = ?"
    );
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
    if (!$recipe) return null;

    // Get ingredients
    $stmt2 = $pdo->prepare(
        "SELECT ri.*, i.ingredient_name, i.calories_per_100g, i.protein_per_100g,
                i.carbs_per_100g, i.fat_per_100g, i.fiber_per_100g
         FROM recipe_ingredients ri
         JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
         WHERE ri.recipe_id = ?"
    );
    $stmt2->execute([$id]);
    $recipe['ingredients'] = $stmt2->fetchAll();

    // Get reviews
    $stmt3 = $pdo->prepare(
        "SELECT rv.*, u.username, u.profile_picture
         FROM reviews rv
         JOIN users u ON rv.user_id = u.user_id
         WHERE rv.recipe_id = ? ORDER BY rv.created_at DESC"
    );
    $stmt3->execute([$id]);
    $recipe['reviews'] = $stmt3->fetchAll();

    // Increment view count
    $pdo->prepare("UPDATE recipes SET view_count = view_count + 1 WHERE recipe_id = ?")->execute([$id]);

    return $recipe;
}

/**
 * Get smart recommendations based on user profile
 */
function getRecommendations(array $user, int $limit = 6): array {
    $pdo = getDB();
    $params = [];
    $sql = "SELECT r.*, c.category_name, c.icon, c.color_hex,
                   COALESCE(AVG(rv.rating), 0) AS rating_avg
            FROM recipes r
            LEFT JOIN categories c ON r.category_id = c.category_id
            LEFT JOIN reviews rv ON r.recipe_id = rv.recipe_id
            WHERE r.recipe_id NOT IN (
                SELECT recipe_id FROM saved_recipes WHERE user_id = ?
            )";
    $params[] = $user['user_id'];

    if ($user['dietary_preference'] !== 'None') {
        $sql .= " AND (r.dietary_type = ? OR r.dietary_type = 'None')";
        $params[] = $user['dietary_preference'];
    }

    $dailyCal = calculateDailyCalories($user);
    $mealCal  = $dailyCal / 3;
    if ($user['health_goal'] === 'Lose Weight') {
        $sql .= " AND r.total_calories <= ?";
        $params[] = $mealCal * 0.85;
    } elseif ($user['health_goal'] === 'Build Muscle') {
        $sql .= " AND r.total_protein >= ?";
        $params[] = 25;
    }

    $sql .= " GROUP BY r.recipe_id ORDER BY rating_avg DESC, r.view_count DESC LIMIT ?";
    $params[] = $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get all categories
 */
function getCategories(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT *, (SELECT COUNT(*) FROM recipes WHERE category_id = categories.category_id) AS recipe_count FROM categories ORDER BY category_name");
    return $stmt->fetchAll();
}

/**
 * Save / unsave recipe
 */
function toggleSaveRecipe(int $userId, int $recipeId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id FROM saved_recipes WHERE user_id = ? AND recipe_id = ?");
    $stmt->execute([$userId, $recipeId]);
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM saved_recipes WHERE user_id = ? AND recipe_id = ?")->execute([$userId, $recipeId]);
        return ['saved' => false, 'message' => 'Recipe removed from saved.'];
    } else {
        $pdo->prepare("INSERT INTO saved_recipes (user_id, recipe_id) VALUES (?,?)")->execute([$userId, $recipeId]);
        return ['saved' => true, 'message' => 'Recipe saved!'];
    }
}

/**
 * Submit or update review
 */
function submitReview(int $userId, int $recipeId, int $rating, string $text): array {
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Rating must be between 1 and 5.'];
    }
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND recipe_id = ?");
    $stmt->execute([$userId, $recipeId]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE reviews SET rating = ?, review_text = ? WHERE user_id = ? AND recipe_id = ?")
            ->execute([$rating, sanitize($text), $userId, $recipeId]);
    } else {
        $pdo->prepare("INSERT INTO reviews (recipe_id, user_id, rating, review_text) VALUES (?,?,?,?)")
            ->execute([$recipeId, $userId, $rating, sanitize($text)]);
    }
    $pdo->prepare("UPDATE recipes SET rating_avg = (SELECT AVG(rating) FROM reviews WHERE recipe_id = ?) WHERE recipe_id = ?")
        ->execute([$recipeId, $recipeId]);
    return ['success' => true, 'message' => 'Review submitted!'];
}

/**
 * Get nutrition summary per serving
 */
function getNutritionPerServing(array $recipe): array {
    $s = max(1, (int)$recipe['servings']);
    return [
        'calories' => round($recipe['total_calories'] / $s, 1),
        'protein'  => round($recipe['total_protein']  / $s, 1),
        'carbs'    => round($recipe['total_carbs']    / $s, 1),
        'fat'      => round($recipe['total_fat']      / $s, 1),
        'fiber'    => round($recipe['total_fiber']    / $s, 1),
    ];
}

/**
 * Search recipes
 */
function searchRecipes(string $query, ?int $userId = null): array {
    $pdo = getDB();
    if ($userId) {
        $pdo->prepare("INSERT INTO search_history (user_id, search_query) VALUES (?,?)")->execute([$userId, sanitize($query)]);
    }
    return getRecipes(['search' => sanitize($query)]);
}

/**
 * Add or update recipe (FIXED WHITELIST)
 */
function saveRecipe(array $data, ?int $recipeId = null): array {
    $pdo = getDB();
    // ADDED 'image_url' TO THIS ARRAY SO MYSQL ACTUALLY RECEIVES THE PICTURE NAME:
    $fields = ['title','description','category_id','prep_time_min','cook_time_min','servings',
                'difficulty','dietary_type','image_url','instructions','total_calories','total_protein',
                'total_carbs','total_fat','total_fiber'];
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = $data[$f] ?? null;
    }
    $values['user_id'] = $data['user_id'] ?? null;
    $values['is_featured'] = isset($data['is_featured']) ? 1 : 0;

    if ($recipeId) {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($values)));
        $stmt = $pdo->prepare("UPDATE recipes SET $set WHERE recipe_id = ?");
        $stmt->execute([...array_values($values), $recipeId]);
        return ['success' => true, 'recipe_id' => $recipeId];
    } else {
        $cols = implode(', ', array_keys($values));
        $ph   = implode(', ', array_fill(0, count($values), '?'));
        $stmt = $pdo->prepare("INSERT INTO recipes ($cols) VALUES ($ph)");
        $stmt->execute(array_values($values));
        return ['success' => true, 'recipe_id' => $pdo->lastInsertId()];
    }
}

/**
 * Get meal plan
 */
function getMealPlan(int $userId, string $startDate, string $endDate): array {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        "SELECT mp.*, r.title, r.total_calories, r.total_protein, r.total_carbs, r.total_fat, r.image_url
         FROM meal_plans mp
         JOIN recipes r ON mp.recipe_id = r.recipe_id
         WHERE mp.user_id = ? AND mp.plan_date BETWEEN ? AND ?
         ORDER BY mp.plan_date, FIELD(mp.meal_type,'Breakfast','Lunch','Dinner','Snack')"
    );
    $stmt->execute([$userId, $startDate, $endDate]);
    $rows = $stmt->fetchAll();
    $plan = [];
    foreach ($rows as $row) {
        $plan[$row['plan_date']][$row['meal_type']][] = $row;
    }
    return $plan;
}

if (!function_exists('sanitize')) {
    function sanitize(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}