<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
$pageTitle  = 'Recipe Categories';
$categories = getCategories();
$pdo        = getDB();
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Categories</span></nav>
    <h1><i class="fas fa-th-large"></i> Recipe Categories</h1>
    <p>Find recipes by category — from quick breakfasts to hearty dinners</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.5rem">
    <?php foreach ($categories as $cat):
      // Get top 3 recipes for preview
      $stmt = $pdo->prepare("SELECT recipe_id, title, total_calories, servings FROM recipes WHERE category_id=? LIMIT 3");
      $stmt->execute([$cat['category_id']]);
      $previewRecipes = $stmt->fetchAll();
    ?>
      <div class="card" style="overflow:hidden;transition:transform var(--transition),box-shadow var(--transition)">
        <!-- Category Header -->
        <div style="background:linear-gradient(135deg,<?= htmlspecialchars($cat['color_hex']) ?>cc,<?= htmlspecialchars($cat['color_hex']) ?>);
                    padding:1.75rem 1.5rem;color:#fff;position:relative;overflow:hidden">
          <div style="position:absolute;right:-10px;bottom:-10px;font-size:5rem;opacity:.15">
            <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i>
          </div>
          <div style="position:relative;z-index:1">
            <div style="width:50px;height:50px;background:rgba(255,255,255,.2);border-radius:12px;
                        display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:.75rem">
              <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i>
            </div>
            <h3 style="font-size:1.15rem;font-weight:700;margin-bottom:.2rem"><?= htmlspecialchars($cat['category_name']) ?></h3>
            <span style="font-size:.8rem;opacity:.85"><?= (int)$cat['recipe_count'] ?> recipe<?= $cat['recipe_count']!=1?'s':'' ?></span>
          </div>
        </div>

        <!-- Preview Recipes -->
        <div style="padding:1rem 1.25rem">
          <?php if (empty($previewRecipes)): ?>
            <p class="text-muted" style="font-size:.82rem;text-align:center;padding:.5rem 0">No recipes yet in this category.</p>
          <?php else: ?>
            <?php foreach ($previewRecipes as $r): ?>
              <a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>"
                 style="display:flex;justify-content:space-between;align-items:center;padding:.45rem 0;
                        border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);
                        font-size:.82rem;transition:color var(--transition)">
                <span style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:65%"><?= htmlspecialchars($r['title']) ?></span>
                <span style="color:var(--secondary);font-weight:600;font-size:.78rem;flex-shrink:0"><?= round((float)$r['total_calories'] / max(1,(int)$r['servings'])) ?> kcal</span>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
          <div style="margin-top:.85rem">
            <a href="recipes.php?category=<?= $cat['category_id'] ?>" class="btn cat-card-btn btn-sm btn-block"
               style="--cat-color: <?= htmlspecialchars($cat['color_hex']) ?>;">
              <i class="fas fa-arrow-right"></i> View All <?= htmlspecialchars($cat['category_name']) ?>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Dietary Filter Section -->
  <div style="margin-top:3rem">
    <div class="section-heading"><h2>Browse by Dietary Preference</h2><p>Filter recipes to match your lifestyle</p></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem">
      <?php
        $diets = [
          ['label'=>'Vegetarian','icon'=>'fa-leaf','color'=>'#4CAF50','bg'=>'#E8F5E9'],
          ['label'=>'Vegan','icon'=>'fa-seedling','color'=>'#2E7D32','bg'=>'#F1F8E9'],
          ['label'=>'Gluten-Free','icon'=>'fa-wheat-alt','color'=>'#FF9800','bg'=>'#FFF3E0'],
          ['label'=>'Keto','icon'=>'fa-bolt','color'=>'#9C27B0','bg'=>'#F3E5F5'],
          ['label'=>'Paleo','icon'=>'fa-drumstick-bite','color'=>'#795548','bg'=>'#EFEBE9'],
        ];
        foreach ($diets as $d):
          $cnt = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE dietary_type=?");
          $cnt->execute([$d['label']]);
          $count = $cnt->fetchColumn();
      ?>
        <a href="recipes.php?diet=<?= urlencode($d['label']) ?>" class="card"
           style="padding:1.25rem;text-align:center;background:<?= $d['bg'] ?>;border-top:3px solid <?= $d['color'] ?>;text-decoration:none">
          <i class="fas <?= $d['icon'] ?>" style="font-size:1.8rem;color:<?= $d['color'] ?>;margin-bottom:.5rem;display:block"></i>
          <div style="font-weight:700;color:<?= $d['color'] ?>;font-size:.9rem"><?= $d['label'] ?></div>
          <div style="font-size:.75rem;color:var(--text-muted);margin-top:.25rem"><?= $count ?> recipes</div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
