<?php
// ============================================================
// pages/dashboard.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();
$user = getCurrentUser();
$pdo  = getDB();

$pageTitle = 'My Dashboard';

// Stats
$saved  = $pdo->prepare("SELECT COUNT(*) FROM saved_recipes WHERE user_id=?"); $saved->execute([$user['user_id']]); $savedCount = $saved->fetchColumn();
$reviews= $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id=?"); $reviews->execute([$user['user_id']]); $reviewCount = $reviews->fetchColumn();
$plans  = $pdo->prepare("SELECT COUNT(*) FROM meal_plans WHERE user_id=? AND plan_date >= CURDATE()"); $plans->execute([$user['user_id']]); $planCount = $plans->fetchColumn();

// My saved recipes
$savedRecipes = $pdo->prepare(
  "SELECT r.*, c.category_name, c.icon, c.color_hex FROM saved_recipes sr 
   JOIN recipes r ON sr.recipe_id=r.recipe_id 
   LEFT JOIN categories c ON r.category_id=c.category_id
   WHERE sr.user_id=? ORDER BY sr.saved_at DESC LIMIT 6"
);
$savedRecipes->execute([$user['user_id']]);
$savedRecipes = $savedRecipes->fetchAll();

// Today's meal plan
$todayPlan = $pdo->prepare(
  "SELECT mp.*, r.title, r.total_calories, r.total_protein, r.total_carbs, r.total_fat
   FROM meal_plans mp JOIN recipes r ON mp.recipe_id=r.recipe_id
   WHERE mp.user_id=? AND mp.plan_date=CURDATE()
   ORDER BY FIELD(mp.meal_type,'Breakfast','Lunch','Dinner','Snack')"
);
$todayPlan->execute([$user['user_id']]);
$todayMeals = $todayPlan->fetchAll();

$todayCalories = array_sum(array_column($todayMeals, 'total_calories'));
$dailyCal = calculateDailyCalories($user);
$calPct   = $dailyCal > 0 ? min(100, round($todayCalories / $dailyCal * 100)) : 0;

// BMI
$bmiData = ($user['weight_kg'] && $user['height_cm']) ? calculateBMI((float)$user['weight_kg'], (float)$user['height_cm']) : null;

// Recommendations
$recs = getRecommendations($user, 3);

// Weekly calorie data for chart (last 7 days)
$weeklyChartData = [];
$dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
for ($d = 6; $d >= 0; $d--) {
    $date = date('Y-m-d', strtotime("-$d days"));
    $dow  = (int)date('N', strtotime($date)) - 1; // 0=Mon
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(r.total_calories),0) as total
         FROM meal_plans mp JOIN recipes r ON mp.recipe_id=r.recipe_id
         WHERE mp.user_id=? AND mp.plan_date=?"
    );
    $stmt->execute([$user['user_id'], $date]);
    $cal = (int)$stmt->fetchColumn();
    $weeklyChartData[] = [
        'label'    => $dayNames[$dow],
        'calories' => $cal,
        'target'   => round($dailyCal / 3 * 3), // full day target
        'today'    => ($d === 0),
    ];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>Welcome back, <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>! 👋</h1>
    <p>Here's your personalised health dashboard for today, <?= date('l, F j') ?></p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">
  <div class="dashboard-grid">

    <!-- SIDEBAR -->
    <div class="sidebar">
      <div class="sidebar-avatar">
        <div class="avatar"><?= strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)) ?></div>
        <h3 style="font-size:1rem;font-weight:700"><?= htmlspecialchars($user['full_name']) ?></h3>
        <p class="text-muted" style="font-size:.8rem">@<?= htmlspecialchars($user['username']) ?></p>
        <span class="badge badge-green" style="margin-top:.35rem"><?= htmlspecialchars($user['health_goal']) ?></span>
      </div>

      <!-- Daily Calories -->
      <div style="background:var(--bg);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1.25rem;text-align:center">
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.3rem">Daily Calorie Target</div>
        <div style="font-size:1.6rem;font-weight:800;color:var(--primary-dark)"><?= number_format($dailyCal) ?></div>
        <div style="font-size:.75rem;color:var(--text-muted)">kcal/day</div>
      </div>

      <nav>
        <ul class="sidebar-nav">
          <li><a href="dashboard.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
          <li><a href="profile.php"><i class="fas fa-user-cog"></i> My Profile</a></li>
          <li><a href="saved_recipes.php"><i class="fas fa-heart"></i> Saved Recipes <span class="badge badge-green" style="float:right"><?= $savedCount ?></span></a></li>
          <li><a href="meal_plan.php"><i class="fas fa-calendar-alt"></i> Meal Planner</a></li>
          <li><a href="add_recipe.php"><i class="fas fa-plus-circle"></i> Add Recipe</a></li>
          <li><a href="nutrition_calculator.php"><i class="fas fa-calculator"></i> Nutrition Calc</a></li>
          <li><a href="../php/logout.php" style="color:var(--danger)"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
      </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div>

      <!-- Stats Cards -->
      <div class="stats-grid" style="margin-bottom:1.5rem">
        <div class="stat-card">
          <div class="stat-icon" style="background:#E8F5E9"><i class="fas fa-heart" style="color:#E91E63"></i></div>
          <div class="stat-value"><?= $savedCount ?></div>
          <div class="stat-label">Saved Recipes</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#FFF3E0"><i class="fas fa-calendar" style="color:var(--secondary)"></i></div>
          <div class="stat-value"><?= $planCount ?></div>
          <div class="stat-label">Upcoming Meals</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#E0F7FA"><i class="fas fa-star" style="color:var(--warning)"></i></div>
          <div class="stat-value"><?= $reviewCount ?></div>
          <div class="stat-label">Reviews Written</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#F3E5F5"><i class="fas fa-fire-alt" style="color:#9C27B0"></i></div>
          <div class="stat-value"><?= round($todayCalories) ?></div>
          <div class="stat-label">Today's kcal</div>
        </div>
      </div>

      <!-- Today's Calorie Progress -->
      <div class="card" style="margin-bottom:1.5rem">
        <div class="card-body">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
            <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-fire-alt" style="color:var(--secondary)"></i> Today's Calorie Progress</h3>
            <span style="font-size:.85rem;color:var(--text-muted)"><?= round($todayCalories) ?> / <?= number_format($dailyCal) ?> kcal</span>
          </div>
          <div class="nutrition-bar-track" style="height:14px;border-radius:50px">
            <div class="nutrition-bar-fill fill-protein" style="width:<?= $calPct ?>%;height:100%;background:linear-gradient(90deg,var(--primary),var(--secondary))"></div>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.75rem;color:var(--text-muted);margin-top:.4rem">
            <span><?= $calPct ?>% of daily target</span>
            <span><?= max(0, round($dailyCal - $todayCalories)) ?> kcal remaining</span>
          </div>
        </div>
      </div>

      <!-- Weekly Calorie Chart -->
      <div class="card" style="margin-bottom:1.5rem">
        <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
          <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)">
            <i class="fas fa-chart-bar" style="color:var(--accent)"></i> Weekly Calorie Overview
          </h3>
          <a href="meal_plan.php" style="font-size:.8rem;color:var(--primary)">View Full Plan <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body">
          <canvas id="weeklyCalChart" width="600" height="180"
                  data-chart="weekly"
                  data-days='<?= json_encode($weeklyChartData) ?>'
                  style="width:100%;height:180px"></canvas>
        </div>
      </div>

      <!-- TABS -->
      <div class="tabs-wrapper">
        <div class="tabs">
          <button class="tab-btn active" data-target="tab-today">Today's Plan</button>
          <button class="tab-btn" data-target="tab-recs">Recommendations</button>
          <button class="tab-btn" data-target="tab-profile">Quick Stats</button>
        </div>

        <!-- TODAY's MEALS -->
        <div class="tab-panel active" id="tab-today">
          <?php if (empty($todayMeals)): ?>
            <div style="text-align:center;padding:2rem">
              <i class="fas fa-calendar-plus" style="font-size:2.5rem;color:var(--border);margin-bottom:.75rem;display:block"></i>
              <p class="text-muted">No meals planned for today.</p>
              <a href="meal_plan.php" class="btn btn-primary btn-sm mt-2"><i class="fas fa-plus"></i> Plan Today's Meals</a>
            </div>
          <?php else: ?>
            <?php
              $mealTypes = ['Breakfast'=>'☀️','Lunch'=>'🌤️','Dinner'=>'🌙','Snack'=>'🍎'];
              $grouped = [];
              foreach ($todayMeals as $m) $grouped[$m['meal_type']][] = $m;
            ?>
            <?php foreach ($mealTypes as $type => $emoji): ?>
              <?php if (!empty($grouped[$type])): ?>
                <div style="margin-bottom:1.25rem">
                  <h4 style="font-size:.875rem;font-weight:700;color:var(--text-muted);margin-bottom:.6rem;text-transform:uppercase;letter-spacing:.5px">
                    <?= $emoji ?> <?= $type ?>
                  </h4>
                  <?php foreach ($grouped[$type] as $meal): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;background:var(--bg);border-radius:var(--radius-sm);padding:.75rem 1rem;margin-bottom:.4rem">
                      <span style="font-size:.9rem;font-weight:600"><?= htmlspecialchars($meal['title']) ?></span>
                      <div style="display:flex;gap:1rem;font-size:.8rem;color:var(--text-muted)">
                        <span style="color:var(--secondary);font-weight:600"><?= round($meal['total_calories']) ?> kcal</span>
                        <span><?= round($meal['total_protein']) ?>g P</span>
                        <span><?= round($meal['total_carbs']) ?>g C</span>
                        <span><?= round($meal['total_fat']) ?>g F</span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
            <div style="padding:.75rem;background:linear-gradient(90deg,#E8F5E9,#F1F8E9);border-radius:var(--radius-sm);text-align:center">
              <strong style="color:var(--primary-dark)"><?= round($todayCalories) ?> kcal</strong>
              <span style="color:var(--text-muted);font-size:.85rem"> total planned for today</span>
            </div>
          <?php endif; ?>
        </div>

        <!-- RECOMMENDATIONS -->
        <div class="tab-panel" id="tab-recs">
          <?php if (empty($recs)): ?>
            <p class="text-muted">No personalised recommendations yet. <a href="profile.php">Complete your profile</a> for better suggestions.</p>
          <?php else: ?>
            <div class="recipe-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
              <?php foreach ($recs as $r):
                $nR = getNutritionPerServing($r); ?>
                <div class="card recipe-card">
                  <?php if (!empty($r['image_url']) && $r['image_url'] !== 'default_recipe.jpg'): ?>
                  <img src="<?= SITE_URL ?>/images/<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" class="recipe-card-img" style="object-fit:cover; width:100%; height:180px;">
                  <?php else: ?>
                  <div class="recipe-card-img placeholder" style="background:linear-gradient(135deg,<?= htmlspecialchars($r['color_hex']??'#4CAF50') ?>44,<?= htmlspecialchars($r['color_hex']??'#4CAF50') ?>88)">
                  <i class="fas <?= htmlspecialchars($r['icon']??'fa-utensils') ?>" style="color:<?= htmlspecialchars($r['color_hex']??'#4CAF50') ?>;font-size:3rem;opacity:.7"></i>
                  </div>
                  <?php endif; ?>
                  <div class="recipe-card-body" style="padding:.85rem">
                    <h4 class="recipe-title" style="font-size:.875rem"><?= htmlspecialchars($r['title']) ?></h4>
                    <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.35rem"><?= round($nR['calories']) ?> kcal · <?= $nR['protein'] ?>g protein</div>
                    <a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>" class="btn btn-primary btn-sm" style="font-size:.75rem">View</a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- QUICK STATS -->
        <div class="tab-panel" id="tab-profile">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <?php
              $profileStats = [
                ['label'=>'Height','value'=>($user['height_cm']?$user['height_cm'].' cm':'—'),'icon'=>'fa-ruler-vertical','color'=>'#2196F3'],
                ['label'=>'Weight','value'=>($user['weight_kg']?$user['weight_kg'].' kg':'—'),'icon'=>'fa-weight','color'=>'#9C27B0'],
                ['label'=>'Activity Level','value'=>$user['activity_level']??'—','icon'=>'fa-running','color'=>'#4CAF50'],
                ['label'=>'Dietary Pref.','value'=>$user['dietary_preference']??'None','icon'=>'fa-seedling','color'=>'#FF9800'],
                ['label'=>'Daily Calories','value'=>number_format($dailyCal).' kcal','icon'=>'fa-fire-alt','color'=>'#F44336'],
                ['label'=>'BMI','value'=>$bmiData?$bmiData['bmi'].' ('.$bmiData['category'].')':'—','icon'=>'fa-heartbeat','color'=>'#E91E63'],
              ];
              foreach ($profileStats as $s):
            ?>
              <div style="display:flex;align-items:center;gap:.85rem;background:var(--bg);padding:.85rem;border-radius:var(--radius-sm)">
                <div style="width:40px;height:40px;border-radius:50%;background:<?= $s['color'] ?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <i class="fas <?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;font-size:.9rem"></i>
                </div>
                <div>
                  <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px"><?= $s['label'] ?></div>
                  <div style="font-weight:700;font-size:.9rem;color:var(--text)"><?= htmlspecialchars($s['value']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="text-center mt-3">
            <a href="profile.php" class="btn btn-primary btn-sm"><i class="fas fa-user-edit"></i> Edit Profile</a>
          </div>
        </div>

      </div><!-- end tabs-wrapper -->

    </div><!-- end main -->
  </div><!-- end dashboard-grid -->
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
