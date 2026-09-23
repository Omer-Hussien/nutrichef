<?php
// ============================================================
// pages/recipe_detail.php — Full Recipe View (No Heart Button)
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
$isLoggedIn = isLoggedIn();
$user       = $isLoggedIn ? getCurrentUser() : null;

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: recipes.php'); exit; }

$recipe = getRecipeById($id);
if (!$recipe) { header('Location: recipes.php'); exit; }

$pageTitle = $recipe['title'];
$nutrServ  = getNutritionPerServing($recipe);
$totalCal  = (float)$recipe['total_calories'];
$dailyCal  = $user ? calculateDailyCalories($user) : 2000;
$calPct    = min(100, round(($nutrServ['calories'] / ($dailyCal / 3)) * 100));

// Macro percentages for donut
$totalMacro = $nutrServ['protein'] * 4 + $nutrServ['carbs'] * 4 + $nutrServ['fat'] * 9;
$protPct = $totalMacro ? round($nutrServ['protein'] * 4 / $totalMacro * 100) : 0;
$carbPct = $totalMacro ? round($nutrServ['carbs']   * 4 / $totalMacro * 100) : 0;
$fatPct  = $totalMacro ? round($nutrServ['fat']     * 9 / $totalMacro * 100) : 0;

// Related recipes
$related = getRecipes(['category_id' => $recipe['category_id']], 4);
$related = array_filter($related, fn($r) => $r['recipe_id'] != $id);

$stars = round((float)($recipe['rating_avg'] ?? 0));

include __DIR__ . '/../includes/header.php';
?>

<style>
.print-only { display: none; }
@media print {
  .navbar, footer, .no-print, .toast-container { display: none !important; }
  .print-only { display: block; }
}
.ingredient-row { display: flex; justify-content: space-between; align-items: center;
  padding: .55rem 0; border-bottom: 1px solid var(--border); font-size: .9rem; }
.ingredient-row:last-child { border-bottom: none; }
.instruction-step { display: flex; gap: 1rem; margin-bottom: 1.25rem; }
.step-num { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff;
  font-weight: 700; font-size: .85rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.step-text { flex: 1; line-height: 1.7; font-size: .9rem; color: var(--text); }
</style>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= SITE_URL ?>">Home</a><span>/</span>
      <a href="recipes.php">Recipes</a><span>/</span>
      <span><?= htmlspecialchars($recipe['title']) ?></span>
    </nav>
    <h1><?= htmlspecialchars($recipe['title']) ?></h1>
    <div style="display:flex;align-items:center;gap:1rem;margin-top:.5rem;flex-wrap:wrap">
      <div class="stars"><?php for ($i=1;$i<=5;$i++) echo '<i class="'.($i<=$stars?'fas':'far').' fa-star"></i>'; ?></div>
      <span style="opacity:.85;font-size:.875rem"><?= count($recipe['reviews']) ?> review<?= count($recipe['reviews'])!=1?'s':'' ?></span>
      <span class="badge badge-green"><?= htmlspecialchars($recipe['dietary_type']) ?></span>
      <span class="badge badge-teal"><?= htmlspecialchars($recipe['difficulty']) ?></span>
      <span style="opacity:.7;font-size:.8rem"><i class="fas fa-eye"></i> <?= (int)$recipe['view_count'] ?> views</span>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">
  <div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start">

    <div>

      <div style="position:relative; width:100%; height:360px; border-radius:var(--radius); overflow:hidden; margin-bottom:1.5rem; background:#E8F5E9; box-shadow:var(--shadow);">
        
        <?php if (!empty($recipe['image_url']) && trim($recipe['image_url']) !== '' && $recipe['image_url'] !== 'default_recipe.jpg'): ?>
          <img src="<?= SITE_URL ?>/images/<?= htmlspecialchars($recipe['image_url']) ?>" 
               alt="<?= htmlspecialchars($recipe['title']) ?>" 
               style="width:100%; height:100%; object-fit:cover; display:block; margin:0; padding:0;">
        <?php else: ?>
          <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,<?= htmlspecialchars($recipe['color_hex']??'#4CAF50') ?>33,<?= htmlspecialchars($recipe['color_hex']??'#4CAF50') ?>88);">
            <i class="fas <?= htmlspecialchars($recipe['icon']??'fa-utensils') ?>" style="font-size:7rem; color:<?= htmlspecialchars($recipe['color_hex']??'#4CAF50') ?>; opacity:.5;"></i>
          </div>
        <?php endif; ?>

        <div class="no-print" style="position:absolute; top:16px; right:16px; z-index:10;">
          <button onclick="printRecipe()" class="btn btn-white btn-sm" style="box-shadow:0 4px 12px rgba(0,0,0,0.22); background:rgba(255,255,255,0.95); border:none; font-weight:600; color:#212121; padding:0.5rem 1rem; border-radius:50px; cursor:pointer; display:flex; align-items:center; gap:0.4rem;">
            <i class="fas fa-print" style="color:var(--primary);"></i> Print
          </button>
        </div>

      </div>

      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
        <?php
          $quickStats = [
            ['icon'=>'fa-clock','label'=>'Prep Time','value'=> $recipe['prep_time_min'].' min','color'=>'#FF9800'],
            ['icon'=>'fa-fire','label'=>'Cook Time', 'value'=> $recipe['cook_time_min'].' min','color'=>'#F44336'],
            ['icon'=>'fa-users','label'=>'Servings', 'value'=> $recipe['servings'],'color'=>'#2196F3'],
            ['icon'=>'fa-signal','label'=>'Difficulty','value'=>$recipe['difficulty'],'color'=>'#9C27B0'],
          ];
          foreach ($quickStats as $s):
        ?>
          <div class="card" style="padding:1rem;text-align:center">
            <i class="fas <?= $s['icon'] ?>" style="font-size:1.4rem;color:<?= $s['color'] ?>;margin-bottom:.4rem;display:block"></i>
            <div style="font-weight:700;font-size:.95rem;color:var(--text)"><?= $s['value'] ?></div>
            <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px"><?= $s['label'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($recipe['description']): ?>
        <div class="card" style="padding:1.5rem;margin-bottom:1.5rem">
          <p style="font-size:.95rem;line-height:1.8;color:var(--text)"><?= htmlspecialchars($recipe['description']) ?></p>
        </div>
      <?php endif; ?>

      <div class="card" style="margin-bottom:1.5rem">
        <div class="card-body" style="padding-bottom:0">
          <div class="tabs-wrapper">
            <div class="tabs">
              <button class="tab-btn active" data-target="tab-ingr">
                <i class="fas fa-list-ul"></i> Ingredients (<?= count($recipe['ingredients']) ?>)
              </button>
              <button class="tab-btn" data-target="tab-instr">
                <i class="fas fa-tasks"></i> Instructions
              </button>
            </div>

            <div class="tab-panel active" id="tab-ingr" style="padding:1rem 0">
              <?php if (empty($recipe['ingredients'])): ?>
                <p class="text-muted">No ingredients listed.</p>
              <?php else: ?>
                <?php foreach ($recipe['ingredients'] as $ing): ?>
                  <div class="ingredient-row">
                    <span><i class="fas fa-circle" style="font-size:.45rem;color:var(--primary);margin-right:.6rem;vertical-align:middle"></i>
                      <?= htmlspecialchars($ing['ingredient_name']) ?></span>
                    <span style="font-weight:600;color:var(--primary-dark)">
                      <?= (float)$ing['quantity'] ?> <?= htmlspecialchars($ing['unit']) ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <div class="tab-panel" id="tab-instr" style="padding:1rem 0">
              <?php
                $steps = array_filter(explode("\n", $recipe['instructions']));
                $steps = array_values($steps);
              ?>
              <?php foreach ($steps as $idx => $step): ?>
                <div class="instruction-step">
                  <div class="step-num"><?= $idx + 1 ?></div>
                  <div class="step-text"><?= htmlspecialchars(ltrim($step, '0123456789. ')) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-bottom:1.5rem">
        <div class="card-body">
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;color:var(--primary-dark)">
            <i class="fas fa-comments"></i> Reviews & Ratings
          </h3>

          <?php if ($isLoggedIn): ?>
            <form method="POST" action="../php/submit_review.php" class="no-print" style="background:var(--bg);padding:1.25rem;border-radius:var(--radius-sm);margin-bottom:1.5rem">
              <input type="hidden" name="recipe_id" value="<?= $id ?>">
              <div style="margin-bottom:.75rem">
                <label class="form-label">Your Rating</label>
                <div class="star-rating" style="flex-direction:row-reverse;justify-content:flex-end">
                  <?php for ($i=5;$i>=1;$i--): ?>
                    <input type="radio" id="star<?=$i?>" name="rating" value="<?=$i?>" required>
                    <label for="star<?=$i?>">&#9733;</label>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Your Review</label>
                <textarea name="review_text" class="form-control" rows="3" maxlength="500" placeholder="Share your experience with this recipe…"></textarea>
                <span class="form-hint">0 / 500 characters</span>
              </div>
              <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Submit Review</button>
            </form>
          <?php else: ?>
            <div class="alert alert-info no-print"><i class="fas fa-info-circle"></i> <a href="login.php">Log in</a> to leave a review.</div>
          <?php endif; ?>

          <?php if (empty($recipe['reviews'])): ?>
            <p class="text-muted">No reviews yet. Be the first to review this recipe!</p>
          <?php else: ?>
            <?php foreach ($recipe['reviews'] as $rev):
              $revStars = (int)$rev['rating'];
            ?>
              <div style="padding:1rem 0;border-bottom:1px solid var(--border)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
                  <div style="display:flex;align-items:center;gap:.6rem">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));
                                color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem">
                      <?= strtoupper(substr($rev['username'],0,1)) ?>
                    </div>
                    <strong style="font-size:.9rem"><?= htmlspecialchars($rev['username']) ?></strong>
                  </div>
                  <div>
                    <?php for ($i=1;$i<=5;$i++) echo '<i class="'.($i<=$revStars?'fas':'far').' fa-star" style="color:var(--warning);font-size:.85rem"></i>'; ?>
                    <small class="text-muted" style="margin-left:.4rem"><?= date('M j, Y', strtotime($rev['created_at'])) ?></small>
                  </div>
                </div>
                <?php if ($rev['review_text']): ?>
                  <p style="font-size:.875rem;color:var(--text);line-height:1.6;margin:0"><?= htmlspecialchars($rev['review_text']) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <div style="position:sticky;top:80px">

      <div class="card nutrition-section" style="margin-bottom:1.5rem">
        <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;padding:1.25rem 1.5rem;border-radius:var(--radius) var(--radius) 0 0">
          <h3 style="font-size:1rem;font-weight:700;margin-bottom:.2rem"><i class="fas fa-chart-pie"></i> Nutrition Facts</h3>
          <p style="font-size:.78rem;opacity:.8">Per serving (<?= $recipe['servings'] ?> total)</p>
        </div>
        <div class="card-body">

          <div style="text-align:center;margin-bottom:1.25rem;padding:.75rem;background:var(--bg);border-radius:var(--radius-sm)">
            <div style="font-size:2.2rem;font-weight:800;color:var(--primary-dark)"><?= round($nutrServ['calories']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Calories per serving</div>
            <div style="margin-top:.5rem">
              <canvas id="progressRing" width="120" height="120"
                      data-chart="ring"
                      data-percent="<?= $calPct ?>"
                      data-color="<?= $calPct > 80 ? '#F44336' : ($calPct > 60 ? '#FF9800' : '#4CAF50') ?>"
                      data-label="of meal cal"
                      style="width:120px;height:120px"></canvas>
              <p style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem">of estimated meal allowance</p>
            </div>
          </div>

          <div class="nutrition-bar">
            <div class="nutrition-bar-label"><span>Protein</span><span style="color:var(--primary)"><?= $nutrServ['protein'] ?>g</span></div>
            <div class="nutrition-bar-track"><div class="nutrition-bar-fill fill-protein" data-width="<?= $protPct ?>%" style="width:0"></div></div>
          </div>
          <div class="nutrition-bar">
            <div class="nutrition-bar-label"><span>Carbohydrates</span><span style="color:#E65100"><?= $nutrServ['carbs'] ?>g</span></div>
            <div class="nutrition-bar-track"><div class="nutrition-bar-fill fill-carbs" data-width="<?= $carbPct ?>%" style="width:0"></div></div>
          </div>
          <div class="nutrition-bar">
            <div class="nutrition-bar-label"><span>Fat</span><span style="color:#AD1457"><?= $nutrServ['fat'] ?>g</span></div>
            <div class="nutrition-bar-track"><div class="nutrition-bar-fill fill-fat" data-width="<?= $fatPct ?>%" style="width:0"></div></div>
          </div>
          <div class="nutrition-bar">
            <div class="nutrition-bar-label"><span>Dietary Fiber</span><span style="color:var(--primary-dark)"><?= $nutrServ['fiber'] ?>g</span></div>
            <div class="nutrition-bar-track"><div class="nutrition-bar-fill fill-fiber" data-width="<?= min(100, round($nutrServ['fiber']/25*100)) ?>%" style="width:0"></div></div>
          </div>

          <div style="margin-top:1.25rem">
            <canvas id="macroDonut" width="160" height="160"
                    data-chart="donut"
                    data-values='<?= json_encode([
                        ["value"=>$nutrServ["protein"]*4, "color"=>["#1565C0","#42A5F5"]],
                        ["value"=>$nutrServ["carbs"]*4,   "color"=>["#E65100","#FFA726"]],
                        ["value"=>$nutrServ["fat"]*9,     "color"=>["#AD1457","#F48FB1"]],
                    ]) ?>'
                    data-label="<?= round($nutrServ['calories']) ?> kcal"
                    style="width:160px;height:160px;display:block;margin:0 auto"></canvas>
            <div class="nutrition-legend">
              <div class="legend-item"><div class="legend-dot" style="background:#1565C0"></div>Protein <?= $protPct ?>%</div>
              <div class="legend-item"><div class="legend-dot" style="background:#E65100"></div>Carbs <?= $carbPct ?>%</div>
              <div class="legend-item"><div class="legend-dot" style="background:#AD1457"></div>Fat <?= $fatPct ?>%</div>
              <div class="legend-item"><div class="legend-dot" style="background:#2E7D32"></div>Fiber <?= round($nutrServ['fiber']/($totalMacro/9+0.001)*100) ?>%</div>
            </div>
          </div>

          <?php if ($isLoggedIn): ?>
            <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border)">
              <form method="POST" action="../php/add_to_meal_plan.php">
                <input type="hidden" name="recipe_id" value="<?= $id ?>">
                <div class="form-group">
                  <label class="form-label" style="font-size:.8rem">Add to Meal Plan</label>
                  <input type="date" name="plan_date" class="form-control" style="font-size:.85rem"
                         value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                  <select name="meal_type" class="form-control" style="font-size:.85rem">
                    <option>Breakfast</option><option>Lunch</option><option>Dinner</option><option>Snack</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-sm"><i class="fas fa-calendar-plus"></i> Add to Plan</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card" style="padding:1.25rem;margin-bottom:1.5rem">
        <h4 style="font-size:.875rem;font-weight:700;margin-bottom:.75rem;color:var(--primary-dark)">Recipe Tags</h4>
        <div style="display:flex;flex-wrap:wrap;gap:.4rem">
          <span class="badge badge-green"><?= htmlspecialchars($recipe['category_name']??'General') ?></span>
          <span class="badge badge-teal"><?= htmlspecialchars($recipe['difficulty']) ?></span>
          <?php if ($recipe['dietary_type'] !== 'None'): ?>
            <span class="badge badge-amber"><?= htmlspecialchars($recipe['dietary_type']) ?></span>
          <?php endif; ?>
          <span class="badge badge-green"><?= (int)$recipe['prep_time_min']+(int)$recipe['cook_time_min'] ?> min</span>
          <span class="badge badge-teal"><?= (int)$recipe['servings'] ?> servings</span>
        </div>
      </div>

    </div>
  </div>

  <?php if (!empty($related)): ?>
    <div style="margin-top:2rem">
      <div class="section-heading"><h2>You May Also Like</h2><p>More <?= htmlspecialchars($recipe['category_name']??'') ?> recipes</p></div>
      <div class="recipe-grid">
        <?php foreach (array_slice($related, 0, 4) as $r):
          $nR = getNutritionPerServing($r); $sR = round((float)$r['rating_avg']); ?>
          <div class="card recipe-card">
            <?php if (!empty($r['image_url']) && trim($r['image_url']) !== '' && $r['image_url'] !== 'default_recipe.jpg'): ?>
              <img src="<?= SITE_URL ?>/images/<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" class="recipe-card-img" style="object-fit:cover; width:100%; height:200px; display:block;">
            <?php else: ?>
              <div class="recipe-card-img placeholder" style="background:linear-gradient(135deg,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>44,<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>88)">
                <i class="fas <?= htmlspecialchars($r['icon'] ?? 'fa-utensils') ?>" style="color:<?= htmlspecialchars($r['color_hex'] ?? '#4CAF50') ?>;font-size:3.5rem;opacity:.7"></i>
              </div>
            <?php endif; ?>
            <div class="recipe-card-body">
              <h3 class="recipe-title"><a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>" style="color:inherit"><?= htmlspecialchars($r['title']) ?></a></h3>
              <div class="recipe-meta"><span><i class="fas fa-fire-alt" style="color:var(--secondary)"></i> <?= round($nR['calories']) ?> kcal</span></div>
            </div>
            <div class="card-footer recipe-card-footer">
              <div class="stars"><?php for ($i=1;$i<=5;$i++) echo '<i class="'.($i<=$sR?'fas':'far').' fa-star"></i>'; ?></div>
              <a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>" class="btn btn-primary btn-sm">View</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>