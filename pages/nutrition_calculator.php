<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
$isLoggedIn = isLoggedIn();
$user = $isLoggedIn ? getCurrentUser() : null;
$pageTitle = 'Nutrition Calculator';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Nutrition Calculator</span></nav>
    <h1><i class="fas fa-calculator"></i> Nutrition & Calorie Calculator</h1>
    <p>Calculate your daily calorie needs and track your macronutrient targets</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;align-items:start">

    <!-- Calculator Form -->
    <div class="card">
      <div class="card-body">
        <h2 style="font-size:1.15rem;font-weight:700;color:var(--primary-dark);margin-bottom:1.5rem">
          <i class="fas fa-fire-alt" style="color:var(--secondary)"></i> Calorie Needs Calculator
        </h2>

        <form id="calcForm">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
              <label class="form-label">Age (years)</label>
              <input class="form-control" type="number" id="calcAge" min="12" max="120" placeholder="e.g. 25" value="<?= $user['age'] ?? '' ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Gender</label>
              <select class="form-control" id="calcGender">
                <option value="Male" <?= ($user['gender']??'')=='Male'?'selected':'' ?>>Male</option>
                <option value="Female" <?= ($user['gender']??'')=='Female'?'selected':'' ?>>Female</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Weight (kg)</label>
              <input class="form-control" type="number" id="calcWeight" step="0.1" min="20" max="300" placeholder="e.g. 70" value="<?= $user['weight_kg'] ?? '' ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Height (cm)</label>
              <input class="form-control" type="number" id="calcHeight" step="0.1" min="50" max="250" placeholder="e.g. 170" value="<?= $user['height_cm'] ?? '' ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Activity Level</label>
            <select class="form-control" id="calcActivity">
              <option value="1.2">Sedentary (desk job, little/no exercise)</option>
              <option value="1.375">Lightly Active (light exercise 1–3 days/week)</option>
              <option value="1.55" selected>Moderately Active (moderate exercise 3–5 days/week)</option>
              <option value="1.725">Very Active (hard exercise 6–7 days/week)</option>
              <option value="1.9">Extra Active (very hard exercise + physical job)</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Goal</label>
            <select class="form-control" id="calcGoal">
              <option value="lose">Lose Weight (–500 kcal deficit)</option>
              <option value="maintain" selected>Maintain Weight</option>
              <option value="gain">Gain Weight (+500 kcal surplus)</option>
              <option value="muscle">Build Muscle (+300 kcal + high protein)</option>
            </select>
          </div>

          <button type="button" onclick="calculate()" class="btn btn-primary btn-block">
            <i class="fas fa-calculator"></i> Calculate My Targets
          </button>
        </form>
      </div>
    </div>

    <!-- Results -->
    <div id="resultsArea">
      <div class="card" style="margin-bottom:1rem">
        <div class="card-body" style="text-align:center;padding:2rem">
          <i class="fas fa-chart-pie" style="font-size:3rem;color:var(--border);margin-bottom:1rem;display:block"></i>
          <p class="text-muted">Fill in the form and click <strong>Calculate</strong> to see your personalised targets.</p>
        </div>
      </div>
    </div>

  </div>

  <!-- BMI Calculator -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:2rem">
    <div class="card">
      <div class="card-body">
        <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark);margin-bottom:1.25rem">
          <i class="fas fa-weight" style="color:var(--accent)"></i> BMI Calculator
        </h3>
        <form id="bmiCalcForm">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
              <label class="form-label">Weight (kg)</label>
              <input class="form-control" type="number" id="bmiWeight" step="0.1" placeholder="e.g. 70" value="<?= $user['weight_kg'] ?? '' ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Height (cm)</label>
              <input class="form-control" type="number" id="bmiHeight" step="0.1" placeholder="e.g. 170" value="<?= $user['height_cm'] ?? '' ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-calculator"></i> Calculate BMI</button>
        </form>
        <div id="bmiResult" style="margin-top:1.25rem;text-align:center"></div>
      </div>
    </div>

    <!-- BMI Scale -->
    <div class="card">
      <div class="card-body">
        <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark);margin-bottom:1rem">
          <i class="fas fa-info-circle" style="color:var(--accent)"></i> BMI Reference Scale
        </h3>
        <?php
          $bmiRanges = [
            ['label'=>'Underweight','range'=>'< 18.5','color'=>'#2196F3','bg'=>'#E3F2FD'],
            ['label'=>'Normal Weight','range'=>'18.5 – 24.9','color'=>'#4CAF50','bg'=>'#E8F5E9'],
            ['label'=>'Overweight','range'=>'25.0 – 29.9','color'=>'#FF9800','bg'=>'#FFF3E0'],
            ['label'=>'Obese','range'=>'≥ 30.0','color'=>'#F44336','bg'=>'#FFEBEE'],
          ];
          foreach ($bmiRanges as $r):
        ?>
          <div style="display:flex;align-items:center;justify-content:space-between;
                      padding:.6rem .85rem;border-radius:var(--radius-sm);margin-bottom:.4rem;background:<?= $r['bg'] ?>">
            <span style="font-weight:600;color:<?= $r['color'] ?>"><?= $r['label'] ?></span>
            <span style="font-size:.875rem;color:<?= $r['color'] ?>;font-weight:700"><?= $r['range'] ?></span>
          </div>
        <?php endforeach; ?>
        <p class="text-muted" style="font-size:.78rem;margin-top:.75rem">BMI is an indicator, not a definitive health measure. Consult a healthcare professional for personalised advice.</p>
      </div>
    </div>
  </div>

  <!-- Macronutrient Reference -->
  <div class="card" style="margin-top:2rem">
    <div class="card-body">
      <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark);margin-bottom:1.25rem">
        <i class="fas fa-leaf" style="color:var(--primary)"></i> Macronutrient Reference Guide
      </h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
        <?php
          $macros = [
            ['name'=>'Protein','icon'=>'fa-dumbbell','color'=>'#1565C0','kcal'=>'4 kcal/g',
             'tips'=>'Essential for muscle repair and immune function. Target: 0.8–2.0g per kg bodyweight.'],
            ['name'=>'Carbohydrates','icon'=>'fa-bolt','color'=>'#E65100','kcal'=>'4 kcal/g',
             'tips'=>'Primary energy source. Choose complex carbs (whole grains, vegetables) over refined sugars.'],
            ['name'=>'Fats','icon'=>'fa-tint','color'=>'#AD1457','kcal'=>'9 kcal/g',
             'tips'=>'Essential for hormones and fat-soluble vitamins. Focus on unsaturated fats from nuts, avocado, olive oil.'],
            ['name'=>'Dietary Fiber','icon'=>'fa-seedling','color'=>'#2E7D32','kcal'=>'~2 kcal/g',
             'tips'=>'Supports digestive health and blood sugar control. Target: 25–38g per day for adults.'],
          ];
          foreach ($macros as $m):
        ?>
          <div style="background:var(--bg);border-radius:var(--radius-sm);padding:1rem;border-top:4px solid <?= $m['color'] ?>">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem">
              <i class="fas <?= $m['icon'] ?>" style="color:<?= $m['color'] ?>;font-size:1.1rem"></i>
              <strong style="color:<?= $m['color'] ?>"><?= $m['name'] ?></strong>
            </div>
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:.4rem"><?= $m['kcal'] ?></div>
            <p style="font-size:.8rem;color:var(--text);line-height:1.6"><?= $m['tips'] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<script>
function calculate() {
  const age    = parseFloat(document.getElementById('calcAge').value);
  const gender = document.getElementById('calcGender').value;
  const weight = parseFloat(document.getElementById('calcWeight').value);
  const height = parseFloat(document.getElementById('calcHeight').value);
  const act    = parseFloat(document.getElementById('calcActivity').value);
  const goal   = document.getElementById('calcGoal').value;

  if (!age || !weight || !height) return alert('Please fill in all fields.');

  // Mifflin-St Jeor BMR
  let bmr = gender === 'Male'
    ? 10 * weight + 6.25 * height - 5 * age + 5
    : 10 * weight + 6.25 * height - 5 * age - 161;

  let tdee = Math.round(bmr * act);
  let target = tdee;
  if (goal === 'lose')   target -= 500;
  if (goal === 'gain')   target += 500;
  if (goal === 'muscle') target += 300;

  // Macro splits
  let protG, carbG, fatG;
  if (goal === 'lose') {
    protG = Math.round(weight * 1.8);
    fatG  = Math.round(weight * 1.0);
    carbG = Math.round((target - protG*4 - fatG*9) / 4);
  } else if (goal === 'muscle') {
    protG = Math.round(weight * 2.0);
    fatG  = Math.round(weight * 1.0);
    carbG = Math.round((target - protG*4 - fatG*9) / 4);
  } else {
    protG = Math.round(weight * 1.2);
    fatG  = Math.round(target * 0.30 / 9);
    carbG = Math.round((target - protG*4 - fatG*9) / 4);
  }
  carbG = Math.max(50, carbG);

  const bmi = (weight / Math.pow(height/100, 2)).toFixed(1);
  const bmiCat = bmi < 18.5 ? 'Underweight' : bmi < 25 ? 'Normal Weight' : bmi < 30 ? 'Overweight' : 'Obese';
  const bmiColor = bmi < 18.5 ? '#2196F3' : bmi < 25 ? '#4CAF50' : bmi < 30 ? '#FF9800' : '#F44336';

  const goalLabel = { lose:'Lose Weight', maintain:'Maintain Weight', gain:'Gain Weight', muscle:'Build Muscle' }[goal];

  document.getElementById('resultsArea').innerHTML = `
    <div class="card" style="margin-bottom:1rem;border-top:4px solid var(--primary)">
      <div class="card-body">
        <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark);margin-bottom:1.25rem">
          <i class="fas fa-chart-bar" style="color:var(--primary)"></i> Your Results — ${goalLabel}
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
          <div style="text-align:center;background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;padding:1.25rem;border-radius:var(--radius-sm)">
            <div style="font-size:2rem;font-weight:800">${target.toLocaleString()}</div>
            <div style="font-size:.75rem;opacity:.85;text-transform:uppercase;letter-spacing:.5px">Daily Calorie Target</div>
          </div>
          <div style="text-align:center;background:var(--bg);padding:1.25rem;border-radius:var(--radius-sm)">
            <div style="font-size:2rem;font-weight:800;color:${bmiColor}">${bmi}</div>
            <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">BMI — ${bmiCat}</div>
          </div>
        </div>
        <h4 style="font-size:.875rem;font-weight:700;margin-bottom:.85rem;color:var(--text)">Daily Macronutrient Targets</h4>
        <div style="margin-bottom:.65rem">
          <div style="display:flex;justify-content:space-between;font-size:.82rem;font-weight:600;margin-bottom:.25rem">
            <span style="color:#1565C0"><i class="fas fa-dumbbell"></i> Protein</span>
            <span>${protG}g &nbsp;(${Math.round(protG*4)} kcal)</span>
          </div>
          <div class="nutrition-bar-track"><div style="width:${Math.round(protG*4/target*100)}%;height:8px;border-radius:50px;background:linear-gradient(90deg,#1565C0,#42A5F5)"></div></div>
        </div>
        <div style="margin-bottom:.65rem">
          <div style="display:flex;justify-content:space-between;font-size:.82rem;font-weight:600;margin-bottom:.25rem">
            <span style="color:#E65100"><i class="fas fa-bolt"></i> Carbohydrates</span>
            <span>${carbG}g &nbsp;(${Math.round(carbG*4)} kcal)</span>
          </div>
          <div class="nutrition-bar-track"><div style="width:${Math.round(carbG*4/target*100)}%;height:8px;border-radius:50px;background:linear-gradient(90deg,#E65100,#FFA726)"></div></div>
        </div>
        <div>
          <div style="display:flex;justify-content:space-between;font-size:.82rem;font-weight:600;margin-bottom:.25rem">
            <span style="color:#AD1457"><i class="fas fa-tint"></i> Fat</span>
            <span>${fatG}g &nbsp;(${Math.round(fatG*9)} kcal)</span>
          </div>
          <div class="nutrition-bar-track"><div style="width:${Math.round(fatG*9/target*100)}%;height:8px;border-radius:50px;background:linear-gradient(90deg,#AD1457,#F48FB1)"></div></div>
        </div>
        <div style="margin-top:1.25rem;padding:.85rem;background:var(--bg);border-radius:var(--radius-sm);font-size:.8rem;color:var(--text-muted)">
          <i class="fas fa-lightbulb" style="color:var(--warning)"></i>
          <strong>Tip:</strong> These are estimates based on the Mifflin-St Jeor equation. Individual needs may vary. 
          <a href="recipes.php">Browse recipes</a> matching your targets.
        </div>
      </div>
    </div>
  `;
}
// Auto-calculate if user data pre-filled
<?php if ($user && $user['weight_kg'] && $user['height_cm']): ?>
window.addEventListener('DOMContentLoaded', () => {
  document.getElementById('calcActivity').value = '1.55';
  calculate();
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
