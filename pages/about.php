<?php
// ============================================================
// pages/about.php — About NutriChef
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();
$pageTitle = 'About NutriChef';
$isLoggedIn = isLoggedIn();

$pdo = getDB();
$totalRecipes = $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$totalUsers   = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalIngr    = $pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
$totalReviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero -->
<section style="background:linear-gradient(135deg,var(--primary-dark) 0%,#388E3C 55%,var(--accent) 100%);
                color:#fff;padding:5rem 0 4rem;position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E\")"></div>
  <div class="container" style="position:relative;z-index:1;text-align:center">
    <div style="font-size:4rem;margin-bottom:1rem">🍃</div>
    <h1 style="font-size:clamp(2rem,5vw,3.2rem);font-weight:800;margin-bottom:1rem">
      About <span style="color:var(--secondary-light)">NutriChef</span>
    </h1>
    <p style="font-size:1.15rem;opacity:.9;max-width:600px;margin:0 auto 2rem;line-height:1.7">
      Empowering healthier lives through smart, personalised nutrition and recipe guidance — built for everyone, tailored to you.
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
      <a href="recipes.php" class="btn btn-white btn-lg"><i class="fas fa-utensils"></i> Explore Recipes</a>
      <?php if (!$isLoggedIn): ?>
        <a href="register.php" class="btn btn-secondary btn-lg"><i class="fas fa-user-plus"></i> Join Free</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Stats Bar -->
<section style="background:#fff;padding:2.5rem 0;border-bottom:1px solid var(--border)">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;text-align:center">
      <?php
        $milestones = [
          ['val'=>$totalRecipes,'label'=>'Recipes','icon'=>'fa-utensils','color'=>'#4CAF50'],
          ['val'=>$totalUsers,  'label'=>'Members', 'icon'=>'fa-users',  'color'=>'#2196F3'],
          ['val'=>$totalIngr,   'label'=>'Ingredients','icon'=>'fa-leaf','color'=>'#FF9800'],
          ['val'=>$totalReviews,'label'=>'Reviews', 'icon'=>'fa-star',   'color'=>'#E91E63'],
        ];
        foreach ($milestones as $m):
      ?>
        <div>
          <div style="font-size:2.5rem;font-weight:900;color:<?= $m['color'] ?>" data-count="<?= $m['val'] ?>">0</div>
          <div style="color:var(--text-muted);font-size:.875rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:.25rem">
            <i class="fas <?= $m['icon'] ?>" style="color:<?= $m['color'] ?>"></i> <?= $m['label'] ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Mission -->
<section style="padding:4rem 0">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center">
      <div>
        <div class="section-heading">
          <h2>Our Mission</h2>
          <p>Why NutriChef was built</p>
        </div>
        <p style="font-size:1rem;line-height:1.8;color:var(--text);margin-bottom:1.25rem">
          Most people want to eat healthier — but don't know where to start. Generic recipe websites give no nutritional context, no personalisation, and no help planning meals around real health goals.
        </p>
        <p style="font-size:1rem;line-height:1.8;color:var(--text);margin-bottom:1.25rem">
          <strong>NutriChef</strong> was built to change that. By combining a smart recommendation engine with automated nutrition analysis and personalised meal planning, we make healthy eating accessible, informed, and enjoyable for everyone.
        </p>
        <p style="font-size:1rem;line-height:1.8;color:var(--text)">
          Whether your goal is to lose weight, build muscle, follow a vegan lifestyle, or simply understand what's in your food — NutriChef gives you the tools to succeed.
        </p>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <?php
          $values = [
            ['icon'=>'fa-bullseye','title'=>'Personalised','desc'=>'Recommendations matched to your body, goals and dietary preferences.','color'=>'#4CAF50','bg'=>'#E8F5E9'],
            ['icon'=>'fa-flask','title'=>'Science-Based','desc'=>'Nutrition calculated using validated formulas (Mifflin-St Jeor, WHO BMI).','color'=>'#2196F3','bg'=>'#E3F2FD'],
            ['icon'=>'fa-users','title'=>'Community','desc'=>'Recipes shared, rated and reviewed by a growing community of food lovers.','color'=>'#FF9800','bg'=>'#FFF3E0'],
            ['icon'=>'fa-lock','title'=>'Private & Secure','desc'=>'Your data is encrypted, never sold, and fully under your control.','color'=>'#9C27B0','bg'=>'#F3E5F5'],
          ];
          foreach ($values as $v):
        ?>
          <div style="background:<?= $v['bg'] ?>;border-radius:var(--radius);padding:1.5rem;border-top:3px solid <?= $v['color'] ?>">
            <i class="fas <?= $v['icon'] ?>" style="font-size:1.6rem;color:<?= $v['color'] ?>;margin-bottom:.75rem;display:block"></i>
            <h4 style="font-size:.95rem;font-weight:700;color:<?= $v['color'] ?>;margin-bottom:.4rem"><?= $v['title'] ?></h4>
            <p style="font-size:.82rem;color:var(--text);line-height:1.6;margin:0"><?= $v['desc'] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- How It Works -->
<section style="background:var(--bg);padding:4rem 0">
  <div class="container">
    <div class="section-heading center text-center" style="margin-bottom:3rem">
      <h2 style="text-align:center">How NutriChef Works</h2>
      <p>Four intelligent steps from profile to plate</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem">
      <?php
        $steps = [
          ['num'=>'01','icon'=>'fa-user-cog','title'=>'Build Your Profile','desc'=>'Enter your age, weight, height, activity level, health goal and dietary preferences in our 3-step onboarding wizard.','color'=>'var(--primary)'],
          ['num'=>'02','icon'=>'fa-magic','title'=>'Get Smart Picks','desc'=>'Our algorithm filters and ranks recipes based on your exact TDEE, macro targets, and dietary restrictions — no guesswork.','color'=>'var(--secondary)'],
          ['num'=>'03','icon'=>'fa-chart-pie','title'=>'Analyse Nutrition','desc'=>'Every recipe shows a full macro breakdown — calories, protein, carbs, fat and fibre — calculated from real ingredient data.','color'=>'var(--accent)'],
          ['num'=>'04','icon'=>'fa-calendar-alt','title'=>'Plan Your Week','desc'=>'Drag recipes onto your 7-day visual meal planner to build balanced breakfasts, lunches, dinners and snacks.','color'=>'#9C27B0'],
        ];
        foreach ($steps as $i => $s):
      ?>
        <div style="text-align:center;position:relative">
          <?php if ($i < 3): ?>
            <div style="position:absolute;top:35px;left:calc(50% + 35px);right:-50%;height:2px;
                        background:linear-gradient(90deg,<?= $s['color'] ?>,transparent);z-index:0"></div>
          <?php endif; ?>
          <div style="width:70px;height:70px;border-radius:50%;background:<?= $s['color'] ?>;
                      display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;
                      font-size:1.5rem;color:#fff;position:relative;z-index:1;
                      box-shadow:0 4px 15px <?= $s['color'] ?>55">
            <i class="fas <?= $s['icon'] ?>"></i>
          </div>
          <div style="font-size:.65rem;font-weight:800;letter-spacing:2px;color:<?= $s['color'] ?>;margin-bottom:.3rem">STEP <?= $s['num'] ?></div>
          <h3 style="font-size:1rem;font-weight:700;margin-bottom:.5rem;color:var(--text)"><?= $s['title'] ?></h3>
          <p style="font-size:.82rem;color:var(--text-muted);line-height:1.6"><?= $s['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Technology -->
<section style="padding:4rem 0">
  <div class="container">
    <div class="section-heading center text-center" style="margin-bottom:2.5rem">
      <h2 style="text-align:center">Technology Stack</h2>
      <p>Built with modern, proven web technologies</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem">
      <?php
        $tech = [
          ['name'=>'PHP 8.0+',    'icon'=>'fa-code',       'desc'=>'Server-side logic & auth',      'color'=>'#7B3F9E','bg'=>'#F3E5F5'],
          ['name'=>'MySQL 8.0',   'icon'=>'fa-database',   'desc'=>'Relational data storage',       'color'=>'#0277BD','bg'=>'#E1F5FE'],
          ['name'=>'PDO',         'icon'=>'fa-shield-alt', 'desc'=>'Secure parameterised queries',  'color'=>'#2E7D32','bg'=>'#E8F5E9'],
          ['name'=>'HTML5',       'icon'=>'fa-html5',      'desc'=>'Semantic markup & canvas',      'color'=>'#E44D26','bg'=>'#FBE9E7'],
          ['name'=>'CSS3',        'icon'=>'fa-css3-alt',   'desc'=>'Custom design system',          'color'=>'#1565C0','bg'=>'#E3F2FD'],
          ['name'=>'JavaScript',  'icon'=>'fa-js',         'desc'=>'AJAX, charts & interactivity',  'color'=>'#F57F17','bg'=>'#FFF8E1'],
          ['name'=>'Font Awesome','icon'=>'fa-icons',      'desc'=>'6.5 icon library via CDN',      'color'=>'#512DA8','bg'=>'#EDE7F6'],
          ['name'=>'Poppins Font','icon'=>'fa-font',       'desc'=>'Google Fonts typography',       'color'=>'#00838F','bg'=>'#E0F7FA'],
        ];
        foreach ($tech as $t):
      ?>
        <div style="background:<?= $t['bg'] ?>;border-radius:var(--radius-sm);padding:1.1rem;text-align:center;border-bottom:3px solid <?= $t['color'] ?>">
          <i class="fab <?= strpos($t['icon'],'fa-')===0 ? str_replace('fa-','fab fa-',$t['icon']) : $t['icon'] ?>, fas <?= $t['icon'] ?>"
             style="font-size:1.6rem;color:<?= $t['color'] ?>;margin-bottom:.5rem;display:block"></i>
          <div style="font-weight:700;font-size:.875rem;color:<?= $t['color'] ?>"><?= $t['name'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem"><?= $t['desc'] ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Nutrition Science -->
<section style="background:linear-gradient(135deg,var(--primary-dark),#2E7D32);color:#fff;padding:4rem 0">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center">
      <div>
        <h2 style="font-size:2rem;font-weight:800;margin-bottom:1rem">Grounded in Nutrition Science</h2>
        <p style="opacity:.9;line-height:1.8;margin-bottom:1.25rem">
          NutriChef's nutrition engine uses the <strong>Mifflin-St Jeor Equation</strong> — the most accurate method for estimating Basal Metabolic Rate (BMR) for healthy adults, validated in peer-reviewed research since 1990.
        </p>
        <p style="opacity:.9;line-height:1.8;margin-bottom:1.5rem">
          BMI classification follows <strong>WHO guidelines</strong>. All ingredient nutrition data is sourced from the <strong>USDA FoodData Central</strong> database for maximum accuracy.
        </p>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
          <span style="background:rgba(255,255,255,.15);padding:.4rem 1rem;border-radius:50px;font-size:.82rem;font-weight:600">
            <i class="fas fa-check-circle" style="color:var(--secondary-light)"></i> Mifflin-St Jeor (1990)
          </span>
          <span style="background:rgba(255,255,255,.15);padding:.4rem 1rem;border-radius:50px;font-size:.82rem;font-weight:600">
            <i class="fas fa-check-circle" style="color:var(--secondary-light)"></i> WHO BMI Standards
          </span>
          <span style="background:rgba(255,255,255,.15);padding:.4rem 1rem;border-radius:50px;font-size:.82rem;font-weight:600">
            <i class="fas fa-check-circle" style="color:var(--secondary-light)"></i> USDA FoodData
          </span>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <?php
          $formulas = [
            ['title'=>'BMR (Male)', 'formula'=>'(10×W) + (6.25×H) − (5×A) + 5','color'=>'rgba(255,255,255,.12)'],
            ['title'=>'BMR (Female)','formula'=>'(10×W) + (6.25×H) − (5×A) − 161','color'=>'rgba(255,255,255,.12)'],
            ['title'=>'TDEE',       'formula'=>'BMR × Activity Multiplier (1.2–1.9)','color'=>'rgba(255,255,255,.12)'],
            ['title'=>'BMI',        'formula'=>'Weight(kg) ÷ Height(m)²','color'=>'rgba(255,255,255,.12)'],
          ];
          foreach ($formulas as $f):
        ?>
          <div style="background:<?= $f['color'] ?>;border-radius:var(--radius-sm);padding:1rem;border:1px solid rgba(255,255,255,.2)">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;opacity:.75;margin-bottom:.35rem"><?= $f['title'] ?></div>
            <code style="font-size:.78rem;color:var(--secondary-light);line-height:1.5"><?= $f['formula'] ?></code>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<?php if (!$isLoggedIn): ?>
<section style="padding:4rem 0;text-align:center">
  <div class="container">
    <div style="max-width:560px;margin:0 auto">
      <div style="font-size:3rem;margin-bottom:1rem">🥗</div>
      <h2 style="font-size:1.8rem;font-weight:800;color:var(--primary-dark);margin-bottom:1rem">Ready to Eat Smarter?</h2>
      <p style="color:var(--text-muted);margin-bottom:2rem;font-size:1rem;line-height:1.7">
        Join NutriChef for free and get personalised recipe recommendations, macro tracking, and a full meal planner — all in one place.
      </p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="register.php" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Create Free Account</a>
        <a href="recipes.php"  class="btn btn-outline btn-lg"><i class="fas fa-search"></i> Browse Recipes</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
