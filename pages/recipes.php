<?php
// ============================================================
// pages/recipes.php — All Recipes with Filters
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
$isLoggedIn = isLoggedIn();
$pageTitle  = 'All Recipes';

// Build filters from GET
$filters = [];
if (!empty($_GET['search']))      $filters['search']       = $_GET['search'];
if (!empty($_GET['category']))    $filters['category_id']  = (int)$_GET['category'];
if (!empty($_GET['diet']))        $filters['dietary_type'] = $_GET['diet'];
if (!empty($_GET['difficulty']))  $filters['difficulty']   = $_GET['difficulty'];
if (!empty($_GET['max_cal']))     $filters['max_calories'] = (int)$_GET['max_cal'];
if (!empty($_GET['sort']))        $filters['sort']         = $_GET['sort'];
if (!empty($_GET['featured']))    $filters['featured']     = true;

$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$recipes    = getRecipes($filters, $perPage, $offset);
$categories = getCategories();

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Recipes</span>
    </nav>
    <h1><i class="fas fa-utensils"></i> <?= !empty($filters['search']) ? 'Results for "'.htmlspecialchars($filters['search']).'"' : 'All Recipes' ?></h1>
    <p>Discover nutritious meals tailored to your goals</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">

  <!-- Filter Bar -->
  <form id="filterForm" method="GET" action="">
    <div class="filter-bar">
      <!-- Search -->
      <div class="search-bar" style="flex:1;max-width:320px">
        <input type="text" name="search" placeholder="Search recipes…" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
      </div>

      <!-- Category -->
      <select name="category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['category_id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Dietary -->
      <select name="diet">
        <option value="">All Diets</option>
        <?php foreach (['Vegetarian','Vegan','Gluten-Free','Keto','Paleo'] as $d): ?>
          <option value="<?= $d ?>" <?= (($_GET['diet'] ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
        <?php endforeach; ?>
      </select>

      <!-- Difficulty -->
      <select name="difficulty">
        <option value="">Any Difficulty</option>
        <?php foreach (['Easy','Medium','Hard'] as $d): ?>
          <option value="<?= $d ?>" <?= (($_GET['difficulty'] ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
        <?php endforeach; ?>
      </select>

      <!-- Sort -->
      <select name="sort">
        <option value="newest"  <?= (($_GET['sort'] ?? '') === 'newest')       ? 'selected' : '' ?>>Newest First</option>
        <option value="popular" <?= (($_GET['sort'] ?? '') === 'popular')      ? 'selected' : '' ?>>Most Popular</option>
        <option value="rating"  <?= (($_GET['sort'] ?? '') === 'rating')       ? 'selected' : '' ?>>Top Rated</option>
        <option value="calories_asc"  <?= (($_GET['sort'] ?? '') === 'calories_asc')  ? 'selected' : '' ?>>Lowest Calories</option>
        <option value="calories_desc" <?= (($_GET['sort'] ?? '') === 'calories_desc') ? 'selected' : '' ?>>Highest Calories</option>
      </select>

      <!-- Max Calories -->
      <input type="number" name="max_cal" placeholder="Max kcal" min="0" max="5000" value="<?= htmlspecialchars($_GET['max_cal'] ?? '') ?>" style="width:110px">

      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
      <a href="recipes.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
    </div>
  </form>

  <!-- Results Count -->
  <p class="text-muted mb-2">
    <i class="fas fa-list"></i> Showing <?= count($recipes) ?> recipe<?= count($recipes) != 1 ? 's' : '' ?>
    <?= !empty($filters['search']) ? 'for "<strong>'.htmlspecialchars($filters['search']).'</strong>"' : '' ?>
  </p>

  <!-- Recipe Grid -->
  <?php if (empty($recipes)): ?>
    <div class="card" style="padding:3rem;text-align:center">
      <i class="fas fa-search" style="font-size:3rem;color:var(--border);margin-bottom:1rem;display:block"></i>
      <h3 style="color:var(--text-muted);margin-bottom:.5rem">No recipes found</h3>
      <p class="text-muted">Try different filters or <a href="add_recipe.php">add a new recipe</a>.</p>
    </div>
  <?php else: ?>
    <div class="recipe-grid">
      <?php foreach ($recipes as $r):
        $nutr  = getNutritionPerServing($r);
        $stars = round((float)$r['rating_avg']);
        $difficultyColor = ['Easy'=>'badge-green','Medium'=>'badge-amber','Hard'=>'badge-red'][$r['difficulty']] ?? 'badge-green';
      ?>
        <div class="card recipe-card">
          <?php if (!empty($r['image_url']) && $r['image_url'] !== 'default_recipe.jpg'): ?>
            <img src="<?= SITE_URL ?>/images/<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" class="recipe-card-img" style="object-fit:cover; width:100%; height:200px;">
            <?php else: ?>
            <div class="recipe-card-img placeholder" style="background:linear-gradient(135deg,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>44,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>88)">
            <i class="fas <?= htmlspecialchars($r['icon'] ?? 'fa-utensils') ?>" style="color:<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>;font-size:3.5rem;opacity:.7"></i>
            </div>
          <?php endif; ?>
          <div class="recipe-card-badge">
            <span class="badge <?= $difficultyColor ?>"><?= htmlspecialchars($r['difficulty']) ?></span>
          </div>
          <?php if ($isLoggedIn): ?>
          <button class="recipe-save-btn" data-recipe-id="<?= $r['recipe_id'] ?>"><i class="far fa-heart"></i></button>
          <?php endif; ?>
          <div class="recipe-card-body">
            <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem;color:<?= htmlspecialchars($r['color_hex']??'#4CAF50') ?>">
              <i class="fas <?= htmlspecialchars($r['icon']??'fa-utensils') ?>"></i> <?= htmlspecialchars($r['category_name']??'General') ?>
            </div>
            <h3 class="recipe-title">
              <a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>" style="color:inherit"><?= htmlspecialchars($r['title']) ?></a>
            </h3>
            <div class="recipe-meta">
              <span><i class="fas fa-clock"></i> <?= (int)$r['prep_time_min']+(int)$r['cook_time_min'] ?> min</span>
              <span><i class="fas fa-users"></i> <?= (int)$r['servings'] ?></span>
              <?php if ($r['dietary_type'] !== 'None'): ?>
                <span><i class="fas fa-seedling" style="color:var(--primary)"></i> <?= htmlspecialchars($r['dietary_type']) ?></span>
              <?php endif; ?>
            </div>
            <div class="recipe-nutrition">
              <div class="nutr-pill"><strong><?= round($nutr['calories']) ?></strong>kcal</div>
              <div class="nutr-pill"><strong><?= $nutr['protein'] ?>g</strong>Protein</div>
              <div class="nutr-pill"><strong><?= $nutr['carbs'] ?>g</strong>Carbs</div>
              <div class="nutr-pill"><strong><?= $nutr['fat'] ?>g</strong>Fat</div>
            </div>
          </div>
          <div class="card-footer recipe-card-footer">
            <div class="stars" title="<?= number_format((float)$r['rating_avg'],1) ?>/5">
              <?php for ($i=1;$i<=5;$i++) echo '<i class="'.($i<=$stars?'fas':'far').' fa-star"></i>'; ?>
            </div>
            <a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>" class="btn btn-primary btn-sm">View <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if (count($recipes) === $perPage || $page > 1): ?>
      <div style="display:flex;justify-content:center;gap:.5rem;margin-top:2rem;">
        <?php if ($page > 1): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-outline btn-sm"><i class="fas fa-chevron-left"></i> Prev</a>
        <?php endif; ?>
        <span class="btn btn-primary btn-sm" style="cursor:default">Page <?= $page ?></span>
        <?php if (count($recipes) === $perPage): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-outline btn-sm">Next <i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
