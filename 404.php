<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
startSession();
$pageTitle = '404 — Page Not Found';
http_response_code(404);
include __DIR__ . '/includes/header.php';
?>

<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:3rem 1rem">
    <div style="text-align:center;max-width:520px">
        <!-- Animated Illustration -->
        <div style="position:relative;margin-bottom:2rem">
            <div style="font-size:8rem;line-height:1;margin-bottom:1rem;animation:float 3s ease-in-out infinite">🍽️</div>
            <div style="position:absolute;top:-10px;right:50%;transform:translateX(80px);font-size:2.5rem;animation:float 3s ease-in-out infinite .5s">❓</div>
        </div>

        <h1 style="font-size:5rem;font-weight:900;color:var(--primary-dark);line-height:1;margin-bottom:.5rem">404</h1>
        <h2 style="font-size:1.5rem;font-weight:700;color:var(--text);margin-bottom:1rem">Oops! Recipe Not Found</h2>
        <p style="color:var(--text-muted);font-size:1rem;line-height:1.7;margin-bottom:2rem">
            The page you're looking for seems to have wandered off the menu. 
            It may have been removed, renamed, or never existed in the first place.
        </p>

        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-bottom:2.5rem">
            <a href="<?= SITE_URL ?>" class="btn btn-primary btn-lg"><i class="fas fa-home"></i> Back to Home</a>
            <a href="<?= SITE_URL ?>/pages/recipes.php" class="btn btn-outline btn-lg"><i class="fas fa-search"></i> Browse Recipes</a>
        </div>

        <!-- Quick Links -->
        <div style="background:var(--bg);border-radius:var(--radius);padding:1.5rem">
            <p style="font-weight:700;color:var(--primary-dark);margin-bottom:1rem;font-size:.9rem">
                <i class="fas fa-lightbulb" style="color:var(--warning)"></i> You might be looking for:
            </p>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;justify-content:center">
                <a href="<?= SITE_URL ?>/pages/recipes.php" class="category-chip"><i class="fas fa-utensils"></i> All Recipes</a>
                <a href="<?= SITE_URL ?>/pages/categories.php" class="category-chip"><i class="fas fa-th-large"></i> Categories</a>
                <a href="<?= SITE_URL ?>/pages/nutrition_calculator.php" class="category-chip"><i class="fas fa-calculator"></i> Nutrition Calc</a>
                <a href="<?= SITE_URL ?>/pages/dashboard.php" class="category-chip"><i class="fas fa-chart-pie"></i> Dashboard</a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-12px); }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
