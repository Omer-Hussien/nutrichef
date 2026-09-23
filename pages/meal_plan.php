<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();
$user      = getCurrentUser();
$pageTitle = 'Weekly Meal Planner';

// Fetch all available recipes for the Quick Add dropdown
$pdo = getDB();
$allRecipes = $pdo->query("SELECT recipe_id, title, total_calories FROM recipes ORDER BY title ASC")->fetchAll();

// Week navigation
$weekStart = isset($_GET['date']) ? date('Y-m-d', strtotime('monday this week', strtotime($_GET['date']))) : date('Y-m-d', strtotime('monday this week'));
$weekEnd   = date('Y-m-d', strtotime($weekStart . ' +6 days'));
$plan      = getMealPlan($user['user_id'], $weekStart, $weekEnd);

// Remove meal plan entry (Fixed column name to plan_id)
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $pdo->prepare("DELETE FROM meal_plans WHERE plan_id=? AND user_id=?")->execute([(int)$_GET['remove'], $user['user_id']]);
    header("Location: meal_plan.php?date={$weekStart}");
    exit;
}

// Compute weekly nutrition totals
$weeklyTotals = ['calories'=>0,'protein'=>0,'carbs'=>0,'fat'=>0,'count'=>0];
foreach ($plan as $dayMeals) {
    foreach ($dayMeals as $meals) {
        foreach ($meals as $m) {
            $weeklyTotals['calories'] += (float)$m['total_calories'];
            $weeklyTotals['protein']  += (float)$m['total_protein'];
            $weeklyTotals['carbs']    += (float)$m['total_carbs'];
            $weeklyTotals['fat']      += (float)$m['total_fat'];
            $weeklyTotals['count']++;
        }
    }
}

$prevWeek = date('Y-m-d', strtotime($weekStart . ' -7 days'));
$nextWeek = date('Y-m-d', strtotime($weekStart . ' +7 days'));
$dailyCal = calculateDailyCalories($user);
$mealTypes = ['Breakfast','Lunch','Dinner','Snack'];
$mealEmoji = ['Breakfast'=>'☀️','Lunch'=>'🌤️','Dinner'=>'🌙','Snack'=>'🍎'];
$mealColors = ['Breakfast'=>'#FF9800','Lunch'=>'#4CAF50','Dinner'=>'#3F51B5','Snack'=>'#E91E63'];

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Meal Planner</span></nav>
    <h1><i class="fas fa-calendar-alt"></i> Weekly Meal Planner</h1>
    <p>Plan and track your meals for the entire week</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">

  <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;padding:1rem 1.5rem;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:1.5rem">
    <a href="meal_plan.php?date=<?= $prevWeek ?>" class="btn btn-outline btn-sm"><i class="fas fa-chevron-left"></i> Previous</a>
    <div style="text-align:center">
      <h2 style="font-size:1.1rem;font-weight:700;color:var(--primary-dark)">
        <?= date('M j', strtotime($weekStart)) ?> – <?= date('M j, Y', strtotime($weekEnd)) ?>
      </h2>
      <a href="meal_plan.php" style="font-size:.8rem;color:var(--primary)">Jump to this week</a>
    </div>
    <a href="meal_plan.php?date=<?= $nextWeek ?>" class="btn btn-outline btn-sm">Next <i class="fas fa-chevron-right"></i></a>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1.5rem">
    <?php
      $summaryItems = [
        ['label'=>'Total Meals','value'=>$weeklyTotals['count'],'icon'=>'fa-utensils','color'=>'#4CAF50','unit'=>''],
        ['label'=>'Total Calories','value'=>round($weeklyTotals['calories']),'icon'=>'fa-fire-alt','color'=>'#FF5722','unit'=>'kcal'],
        ['label'=>'Avg Daily Cal','value'=>$weeklyTotals['count']?round($weeklyTotals['calories']/7):0,'icon'=>'fa-chart-line','color'=>'#2196F3','unit'=>'kcal'],
        ['label'=>'Total Protein','value'=>round($weeklyTotals['protein']),'icon'=>'fa-dumbbell','color'=>'#9C27B0','unit'=>'g'],
      ];
      foreach ($summaryItems as $s):
    ?>
      <div class="stat-card">
        <div class="stat-icon" style="background:<?= $s['color'] ?>22"><i class="fas <?= $s['icon'] ?>" style="color:<?= $s['color'] ?>"></i></div>
        <div class="stat-value" style="font-size:1.3rem"><?= number_format($s['value']) ?><span style="font-size:.65rem;font-weight:400;color:var(--text-muted)"><?= $s['unit'] ?></span></div>
        <div class="stat-label"><?= $s['label'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:.75rem;margin-bottom:2rem">
    <?php
      $days = [];
      $d = new DateTime($weekStart);
      for ($i=0;$i<7;$i++) { $days[] = $d->format('Y-m-d'); $d->modify('+1 day'); }
    ?>
    <?php foreach ($days as $day):
      $dayName  = date('D', strtotime($day));
      $dayNum   = date('j', strtotime($day));
      $isToday  = ($day === date('Y-m-d'));
      $dayMeals = $plan[$day] ?? [];
      $dayTotal = 0;
      foreach ($dayMeals as $meals) foreach ($meals as $m) $dayTotal += (float)$m['total_calories'];
      $dayPct = $dailyCal > 0 ? min(100, round($dayTotal / $dailyCal * 100)) : 0;
    ?>
      <div style="background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:.85rem;
                  border-top:3px solid <?= $isToday ? 'var(--primary)' : 'var(--border)' ?>">
        <div style="text-align:center;margin-bottom:.75rem">
          <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:var(--text-muted)"><?= $dayName ?></div>
          <div style="font-size:1.2rem;font-weight:800;color:<?= $isToday ? 'var(--primary-dark)' : 'var(--text)' ?>"><?= $dayNum ?></div>
          <?php if ($isToday): ?>
            <span style="font-size:.6rem;background:var(--primary);color:#fff;padding:.1rem .4rem;border-radius:50px;font-weight:700">TODAY</span>
          <?php endif; ?>
        </div>

        <?php foreach ($mealTypes as $type):
          $meals = $dayMeals[$type] ?? [];
        ?>
          <div style="margin-bottom:.5rem">
            <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
                        color:<?= $mealColors[$type] ?>;margin-bottom:.2rem"><?= $mealEmoji[$type]?> <?= $type ?></div>
            <?php if (empty($meals)): ?>
              <a href="recipes.php" style="display:block;border:1.5px dashed var(--border);border-radius:4px;padding:.3rem;text-align:center;font-size:.6rem;color:var(--text-muted);text-decoration:none;transition:all .2s;cursor:pointer;">
                + Add
              </a>
            <?php else: ?>
              <?php foreach ($meals as $m): ?>
                <div style="background:<?= $mealColors[$type] ?>11;border-radius:4px;padding:.3rem .4rem;
                            font-size:.65rem;margin-bottom:.15rem;display:flex;justify-content:space-between;align-items:center">
                  <span style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%"><?= htmlspecialchars($m['title']) ?></span>
                  <a href="?date=<?= $weekStart ?>&remove=<?= $m['plan_id'] ?? '' ?>" style="color:var(--danger);font-size:.6rem" title="Remove"
                     onclick="return confirm('Remove this meal?')">✕</a>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php if ($dayTotal > 0): ?>
          <div style="margin-top:.5rem">
            <div style="font-size:.6rem;text-align:center;color:var(--text-muted);margin-bottom:.2rem"><?= round($dayTotal) ?> kcal</div>
            <div class="nutrition-bar-track" style="height:5px">
              <div style="width:<?= $dayPct ?>%;height:5px;border-radius:50px;
                           background:<?= $dayPct>90?'var(--danger)':($dayPct>75?'var(--warning)':'var(--primary)') ?>"></div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="card-body">
      <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark);margin-bottom:1rem">
        <i class="fas fa-plus-circle" style="color:var(--primary)"></i> Quick Add Meal
      </h3>
      <form method="POST" action="../php/add_to_meal_plan.php" style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr) minmax(0,1fr) minmax(0,1fr) auto;gap:1rem;align-items:end;width:100%;">
        <div class="form-group" style="margin:0">
          <label class="form-label">Choose Recipe</label>
          <select class="form-control" name="recipe_id" required style="font-size:0.85rem;">
            <option value="" disabled selected>-- Select recipe --</option>
            <?php foreach ($allRecipes as $rec): ?>
              <option value="<?= $rec['recipe_id'] ?>">
                <?= htmlspecialchars($rec['title']) ?> (<?= round($rec['total_calories']) ?> kcal)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Date</label>
          <input class="form-control" type="date" name="plan_date" value="<?= date('Y-m-d') ?>" min="<?= $weekStart ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Meal Type</label>
          <select class="form-control" name="meal_type">
            <option>Breakfast</option><option>Lunch</option><option>Dinner</option><option>Snack</option>
          </select>
        </div>
        <div>
          <a href="recipes.php" class="btn btn-outline btn-block btn-sm"><i class="fas fa-search"></i> Browse Recipes</a>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add</button>
      </form>
      <p class="text-muted" style="font-size:.78rem;margin-top:.75rem">Select a recipe directly from your collection above, choose the target day, and click Add.</p>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>