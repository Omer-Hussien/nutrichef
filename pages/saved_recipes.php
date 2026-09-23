<?php
// ============================================================
// pages/saved_recipes.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();
$user = getCurrentUser();
$pageTitle = 'Saved Recipes';

$pdo  = getDB();
$stmt = $pdo->prepare(
    "SELECT r.*, c.category_name, c.icon, c.color_hex, sr.saved_at
     FROM saved_recipes sr
     JOIN recipes r ON sr.recipe_id = r.recipe_id
     LEFT JOIN categories c ON r.category_id = c.category_id
     WHERE sr.user_id = ?
     ORDER BY sr.saved_at DESC"
);
$stmt->execute([$user['user_id']]);
$savedRecipes = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>">Home</a><span>/</span><a href="dashboard.php">Dashboard</a><span>/</span><span>Saved Recipes</span></nav>
    <h1><i class="fas fa-heart"></i> My Saved Recipes</h1>
    <p><?= count($savedRecipes) ?> recipe<?= count($savedRecipes)!=1?'s':'' ?> saved to your collection</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">
  <?php if (empty($savedRecipes)): ?>
    <div class="card" style="padding:3.5rem;text-align:center">
      <i class="fas fa-heart-broken" style="font-size:3.5rem;color:var(--border);margin-bottom:1rem;display:block"></i>
      <h3 style="color:var(--text-muted);margin-bottom:.5rem">No saved recipes yet</h3>
      <p class="text-muted" style="margin-bottom:1.5rem">Browse recipes and click the <i class="fas fa-heart" style="color:var(--secondary)"></i> icon to save them here.</p>
      <a href="recipes.php" class="btn btn-primary"><i class="fas fa-search"></i> Explore Recipes</a>
    </div>
  <?php else: ?>
    <div class="recipe-grid">
      <?php foreach ($savedRecipes as $r):
        $nutr  = getNutritionPerServing($r);
        $stars = round((float)($r['rating_avg'] ?? 0));
      ?>
        <div class="card recipe-card">
          <?php if (!empty($r['image_url']) && trim($r['image_url']) !== '' && $r['image_url'] !== 'default_recipe.jpg'): ?>
          <img src="<?= SITE_URL ?>/images/<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" class="recipe-card-img" style="object-fit:cover; width:100%; height:200px; display:block;">
          <?php else: ?>
          <div class="recipe-card-img placeholder" style="background:linear-gradient(135deg,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>44,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>88)">
          <i class="fas <?= htmlspecialchars($r['icon'] ?? 'fa-utensils') ?>" style="color:<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>;font-size:3.5rem;opacity:.7"></i>
          </div>
          <?php endif; ?>
          <button class="recipe-save-btn saved" data-recipe-id="<?= $r['recipe_id'] ?>" title="Remove from saved">
            <i class="fas fa-heart"></i>
          </button>
          <div class="recipe-card-body">
            <div style="font-size:.7rem;color:<?= htmlspecialchars($r['color_hex']??'var(--primary)') ?>;font-weight:700;text-transform:uppercase;margin-bottom:.3rem">
              <i class="fas <?= htmlspecialchars($r['icon']??'fa-utensils') ?>"></i> <?= htmlspecialchars($r['category_name']??'General') ?>
            </div>
            <h3 class="recipe-title">
              <a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>" style="color:inherit"><?= htmlspecialchars($r['title']) ?></a>
            </h3>
            <div class="recipe-meta">
              <span><i class="fas fa-clock"></i> <?= (int)$r['prep_time_min']+(int)$r['cook_time_min'] ?> min</span>
              <span><i class="fas fa-signal"></i> <?= htmlspecialchars($r['difficulty']) ?></span>
            </div>
            <div class="recipe-nutrition">
              <div class="nutr-pill"><strong><?= round($nutr['calories']) ?></strong>kcal</div>
              <div class="nutr-pill"><strong><?= $nutr['protein'] ?>g</strong>Protein</div>
              <div class="nutr-pill"><strong><?= $nutr['carbs'] ?>g</strong>Carbs</div>
              <div class="nutr-pill"><strong><?= $nutr['fat'] ?>g</strong>Fat</div>
            </div>
            <p style="font-size:.72rem;color:var(--text-muted);margin-top:.5rem">
              <i class="fas fa-bookmark"></i> Saved <?= date('M j, Y', strtotime($r['saved_at'])) ?>
            </p>
          </div>
          <div class="card-footer recipe-card-footer">
            <div class="stars"><?php for ($i=1;$i<=5;$i++) echo '<i class="'.($i<=$stars?'fas':'far').' fa-star"></i>'; ?></div>
            <a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>" class="btn btn-primary btn-sm">View <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
