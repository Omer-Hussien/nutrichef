<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ============================================================
// index.php — NutriChef Homepage
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

startSession();
$pageTitle  = 'Home – Smart Recipes & Nutrition';
$isLoggedIn = isLoggedIn();
$user       = $isLoggedIn ? getCurrentUser() : null;

$featuredRecipes    = getRecipes(['featured' => true], 6);
$latestRecipes      = getRecipes([], 8);
$categories         = getCategories();
$recommendations    = $user ? getRecommendations($user, 4) : [];

include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <h1>Eat Smart.<br>Live <span>Healthy</span>.</h1>
      <p>Discover personalised recipes tailored to your nutrition goals, dietary needs, and taste preferences. Every meal, perfectly planned.</p>
      <div class="hero-btns">
        <a href="pages/recipes.php" class="btn btn-white btn-lg"><i class="fas fa-search"></i> Explore Recipes</a>
        <?php if (!$isLoggedIn): ?>
          <a href="pages/register.php" class="btn btn-secondary btn-lg"><i class="fas fa-user-plus"></i> Get Started Free</a>
        <?php else: ?>
          <a href="pages/meal_plan.php" class="btn btn-secondary btn-lg"><i class="fas fa-calendar-alt"></i> Plan My Meals</a>
        <?php endif; ?>
      </div>

      <!-- Quick Search -->
      <form action="pages/recipes.php" method="GET" style="margin-top:2rem;">
        <div class="search-bar" style="max-width:500px;background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);">
          <input type="text" name="search" placeholder="Search recipes, ingredients..." 
                 style="background:transparent;color:#fff;" id="liveSearch"
                 autocomplete="off" />
          <button type="submit" style="background:var(--secondary)"><i class="fas fa-search"></i></button>
        </div>
        <div id="searchResults" style="background:#fff;border-radius:0 0 12px 12px;box-shadow:var(--shadow);max-width:500px;overflow:hidden;"></div>
      </form>
    </div>
  </div>
</section>

<!-- ===== STATS STRIP ===== -->
<section style="background:#fff;padding:2rem 0;border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background:#E8F5E9"><i class="fas fa-utensils" style="color:var(--primary)"></i></div>
        <div class="stat-value" data-count="<?= count($latestRecipes) + 50 ?>">0</div>
        <div class="stat-label">Recipes</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#FFF3E0"><i class="fas fa-users" style="color:var(--secondary)"></i></div>
        <div class="stat-value" data-count="1240">0</div>
        <div class="stat-label">Members</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#E0F7FA"><i class="fas fa-leaf" style="color:var(--accent)"></i></div>
        <div class="stat-value" data-count="<?= count($categories) ?>">0</div>
        <div class="stat-label">Categories</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#FCE4EC"><i class="fas fa-star" style="color:#E91E63"></i></div>
        <div class="stat-value" data-count="4800">0</div>
        <div class="stat-label">Reviews</div>
      </div>
    </div>
  </div>
</section>

<!-- ===== CATEGORIES ===== -->
<section style="padding:3rem 0 1.5rem;">
  <div class="container">
    <div class="section-heading">
      <h2><i class="fas fa-th-large" style="font-size:1.4rem;margin-right:.4rem;color:var(--secondary)"></i>Browse Categories</h2>
      <p>Explore recipes by meal type or dietary preference</p>
    </div>
    <div class="category-chips">
      <a href="pages/recipes.php" class="category-chip"><i class="fas fa-fire-alt"></i> All</a>
      <?php foreach ($categories as $cat): ?>
        <a href="pages/recipes.php?category=<?= $cat['category_id'] ?>" class="category-chip">
          <i class="fas <?= htmlspecialchars($cat['icon']) ?>" style="color:<?= htmlspecialchars($cat['color_hex']) ?>"></i>
          <?= htmlspecialchars($cat['category_name']) ?>
          <span class="badge badge-green" style="font-size:.65rem"><?= $cat['recipe_count'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== FEATURED RECIPES ===== -->
<section style="padding:2.5rem 0;">
  <div class="container">
    <div class="section-heading flex justify-between items-center" style="display:flex;justify-content:space-between;align-items:flex-end">
      <div>
        <h2><i class="fas fa-star" style="font-size:1.3rem;margin-right:.4rem;color:var(--warning)"></i>Featured Recipes</h2>
        <p>Hand-picked nutritious meals loved by our community</p>
      </div>
      <a href="pages/recipes.php?featured=1" class="btn btn-outline btn-sm">View All <i class="fas fa-arrow-right"></i></a>
    </div>

    <?php if (empty($featuredRecipes)): ?>
      <div class="alert alert-info"><i class="fas fa-info-circle"></i> No featured recipes yet. <a href="pages/add_recipe.php">Add one!</a></div>
    <?php else: ?>
      <div class="recipe-grid">
        <?php foreach ($featuredRecipes as $r):
          $nutr = getNutritionPerServing($r);
          $stars = round((float)$r['rating_avg']);
        ?>
          <div class="card recipe-card">
            <!-- Image -->
            <?php if (!empty($r['image_url']) && $r['image_url'] !== 'default_recipe.jpg'): ?>
            <img src="<?= SITE_URL ?>/images/<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" class="recipe-card-img" style="object-fit:cover; width:100%; height:200px;">
            <?php else: ?>
            <div class="recipe-card-img placeholder" style="background:linear-gradient(135deg,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>44,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>88)">
            <i class="fas <?= htmlspecialchars($r['icon'] ?? 'fa-utensils') ?>" style="color:<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>;font-size:3.5rem;opacity:.7"></i>
            </div>
            <?php endif; ?>

            <!-- Badges -->
            <div class="recipe-card-badge">
              <span class="badge badge-green"><?= htmlspecialchars($r['dietary_type'] ?? 'Any') ?></span>
            </div>

            <?php if ($isLoggedIn): ?>
            <button class="recipe-save-btn" data-recipe-id="<?= $r['recipe_id'] ?>" title="Save recipe">
              <i class="far fa-heart"></i>
            </button>
            <?php endif; ?>

            <!-- Body -->
            <div class="recipe-card-body">
              <h3 class="recipe-title">
                <a href="pages/recipe_detail.php?id=<?= $r['recipe_id'] ?>" style="color:inherit">
                  <?= htmlspecialchars($r['title']) ?>
                </a>
              </h3>
              <div class="recipe-meta">
                <span><i class="fas fa-clock"></i> <?= (int)$r['prep_time_min'] + (int)$r['cook_time_min'] ?> min</span>
                <span><i class="fas fa-signal"></i> <?= htmlspecialchars($r['difficulty']) ?></span>
                <span><i class="fas fa-users"></i> <?= (int)$r['servings'] ?> serv.</span>
              </div>
              <div class="recipe-nutrition">
                <div class="nutr-pill"><strong><?= round($nutr['calories']) ?></strong>kcal</div>
                <div class="nutr-pill"><strong><?= $nutr['protein'] ?>g</strong>Protein</div>
                <div class="nutr-pill"><strong><?= $nutr['carbs'] ?>g</strong>Carbs</div>
                <div class="nutr-pill"><strong><?= $nutr['fat'] ?>g</strong>Fat</div>
              </div>
            </div>

            <div class="card-footer recipe-card-footer">
              <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="<?= $i <= $stars ? 'fas' : 'far' ?> fa-star"></i>
                <?php endfor; ?>
              </div>
              <a href="pages/recipe_detail.php?id=<?= $r['recipe_id'] ?>" class="btn btn-primary btn-sm">
                View <i class="fas fa-arrow-right"></i>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== PERSONALISED RECOMMENDATIONS ===== -->
<?php if ($isLoggedIn && !empty($recommendations)): ?>
<section style="padding:2.5rem 0;background:#fff;">
  <div class="container">
    <div class="section-heading">
      <h2><i class="fas fa-magic" style="font-size:1.3rem;margin-right:.4rem;color:var(--accent)"></i>Recommended for You</h2>
      <p>Tailored to your <?= htmlspecialchars($user['health_goal']) ?> goal &amp; <?= htmlspecialchars($user['dietary_preference']) ?> preference</p>
    </div>
    <div class="recipe-grid">
      <?php foreach ($recommendations as $r):
        $nutr  = getNutritionPerServing($r);
        $stars = round((float)$r['rating_avg']);
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
            <span class="badge badge-teal"><?= htmlspecialchars($r['difficulty']) ?></span>
          </div>
          <div class="recipe-card-body">
            <h3 class="recipe-title">
              <a href="pages/recipe_detail.php?id=<?= $r['recipe_id'] ?>" style="color:inherit"><?= htmlspecialchars($r['title']) ?></a>
            </h3>
            <div class="recipe-meta">
              <span><i class="fas fa-fire-alt" style="color:var(--secondary)"></i> <?= round($nutr['calories']) ?> kcal</span>
              <span><i class="fas fa-dumbbell" style="color:#1565C0"></i> <?= $nutr['protein'] ?>g protein</span>
            </div>
            <div class="recipe-nutrition">
              <div class="nutr-pill"><strong><?= round($nutr['calories']) ?></strong>kcal</div>
              <div class="nutr-pill"><strong><?= $nutr['protein'] ?>g</strong>Protein</div>
              <div class="nutr-pill"><strong><?= $nutr['carbs'] ?>g</strong>Carbs</div>
              <div class="nutr-pill"><strong><?= $nutr['fat'] ?>g</strong>Fat</div>
            </div>
          </div>
          <div class="card-footer recipe-card-footer">
            <div class="stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="<?= $i <= $stars ? 'fas' : 'far' ?> fa-star"></i>
              <?php endfor; ?>
            </div>
            <a href="pages/recipe_detail.php?id=<?= $r['recipe_id'] ?>" class="btn btn-primary btn-sm">View</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== HOW IT WORKS ===== -->
<section style="padding:3.5rem 0;background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;">
  <div class="container">
    <div class="section-heading center text-center" style="margin-bottom:2.5rem">
      <h2 style="color:#fff">How NutriChef Works</h2>
      <h2 style="display:none"></h2>
      <p style="color:rgba(255,255,255,.8)">Three simple steps to smarter, healthier eating</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:2rem">
      <?php
        $steps = [
          ['icon'=>'fa-user-cog','title'=>'1. Build Your Profile','desc'=>'Enter your health goals, dietary preferences, allergies, and physical stats.'],
          ['icon'=>'fa-magic','title'=>'2. Get Recommendations','desc'=>'Our algorithm suggests personalised recipes matched to your nutrition targets.'],
          ['icon'=>'fa-chart-bar','title'=>'3. Track Nutrition','desc'=>'View detailed macro breakdowns and daily calorie summaries for every meal.'],
          ['icon'=>'fa-calendar-alt','title'=>'4. Plan Your Week','desc'=>'Organise breakfast, lunch, dinner and snacks with the visual meal planner.'],
        ];
        foreach ($steps as $s):
      ?>
        <div style="text-align:center;padding:1.5rem 1rem">
          <div style="width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.15);
                      display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.8rem">
            <i class="fas <?= $s['icon'] ?>" style="color:var(--secondary-light)"></i>
          </div>
          <h3 style="font-size:1rem;font-weight:700;margin-bottom:.5rem"><?= $s['title'] ?></h3>
          <p style="font-size:.875rem;opacity:.85;line-height:1.6"><?= $s['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (!$isLoggedIn): ?>
      <div class="text-center" style="margin-top:2rem">
        <a href="pages/register.php" class="btn btn-secondary btn-lg"><i class="fas fa-rocket"></i> Start Your Journey</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== LATEST RECIPES ===== -->
<section style="padding:3rem 0;">
  <div class="container">
    <div class="section-heading flex justify-between items-center" style="display:flex;justify-content:space-between;align-items:flex-end">
      <div>
        <h2><i class="fas fa-clock" style="font-size:1.3rem;margin-right:.4rem;color:var(--primary)"></i>Latest Additions</h2>
        <p>Fresh recipes added by our community</p>
      </div>
      <a href="pages/recipes.php" class="btn btn-outline btn-sm">See All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="recipe-grid">
      <?php foreach (array_slice($latestRecipes, 0, 4) as $r):
        $nutr  = getNutritionPerServing($r);
        $stars = round((float)$r['rating_avg']);
      ?>
        <div class="card recipe-card">
          <?php if (!empty($r['image_url']) && $r['image_url'] !== 'default_recipe.jpg'): ?>
            <img src="<?= SITE_URL ?>/images/<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" class="recipe-card-img" style="object-fit:cover; width:100%; height:200px;">
            <?php else: ?>
            <div class="recipe-card-img placeholder" style="background:linear-gradient(135deg,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>44,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>88)">
            <i class="fas <?= htmlspecialchars($r['icon'] ?? 'fa-utensils') ?>" style="color:<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>;font-size:3.5rem;opacity:.7"></i>
            </div>
            <?php endif; ?>
          <?php if ($isLoggedIn): ?>
          <button class="recipe-save-btn" data-recipe-id="<?= $r['recipe_id'] ?>"><i class="far fa-heart"></i></button>
          <?php endif; ?>
          <div class="recipe-card-body">
            <h3 class="recipe-title">
              <a href="pages/recipe_detail.php?id=<?= $r['recipe_id'] ?>" style="color:inherit"><?= htmlspecialchars($r['title']) ?></a>
            </h3>
            <div class="recipe-meta">
              <span><i class="fas fa-clock"></i> <?= (int)$r['prep_time_min'] + (int)$r['cook_time_min'] ?> min</span>
              <span><i class="fas fa-fire-alt" style="color:var(--secondary)"></i> <?= round($nutr['calories']) ?> kcal</span>
            </div>
            <div class="recipe-nutrition">
              <div class="nutr-pill"><strong><?= round($nutr['calories']) ?></strong>kcal</div>
              <div class="nutr-pill"><strong><?= $nutr['protein'] ?>g</strong>Protein</div>
              <div class="nutr-pill"><strong><?= $nutr['carbs'] ?>g</strong>Carbs</div>
              <div class="nutr-pill"><strong><?= $nutr['fat'] ?>g</strong>Fat</div>
            </div>
          </div>
          <div class="card-footer recipe-card-footer">
            <div class="stars"><?php for ($i=1;$i<=5;$i++) echo '<i class="'.($i<=$stars?'fas':'far').' fa-star"></i>'; ?></div>
            <a href="pages/recipe_detail.php?id=<?= $r['recipe_id'] ?>" class="btn btn-primary btn-sm">View</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
