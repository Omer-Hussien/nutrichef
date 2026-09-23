<?php
// ============================================================
// pages/ingredients.php — Nutrition Database Browser
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
startSession();
$pageTitle = 'Nutrition Database';

$pdo    = getDB();
$search = sanitize($_GET['search'] ?? '');
$sortBy = in_array($_GET['sort'] ?? '', ['calories_per_100g','protein_per_100g','carbs_per_100g','fat_per_100g','fiber_per_100g']) ? $_GET['sort'] : 'ingredient_name';
$dir    = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$where = $search ? "WHERE ingredient_name LIKE :q" : "";
$stmt  = $pdo->prepare("SELECT * FROM ingredients $where ORDER BY $sortBy $dir");
if ($search) $stmt->bindValue(':q', "%$search%");
$stmt->execute();
$ingredients = $stmt->fetchAll();

$totalIngr = $pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();


function sortLink(string $col, string $label, string $current, string $dir): string {
    $newDir = ($current === $col && $dir === 'ASC') ? 'DESC' : 'ASC';
    $icon   = $current === $col ? ($dir === 'ASC' ? '▲' : '▼') : '⇅';
    $q      = http_build_query(array_merge($_GET, ['sort'=>$col,'dir'=>$newDir]));
    return "<a href=\"?$q\" style=\"color:#fff;text-decoration:none\">$label <span style=\"font-size:.7rem\">$icon</span></a>";
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Nutrition Database</span></nav>
    <h1><i class="fas fa-database"></i> Ingredient Nutrition Database</h1>
    <p>Browse <?= $totalIngr ?> ingredients with full macro data per 100g</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">

  <!-- Search & Filter Bar -->
  <div class="card" style="margin-bottom:1.5rem;padding:1.25rem 1.5rem">
    <form method="GET" style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
      <div class="search-bar" style="flex:1;min-width:250px">
        <input type="text" name="search" placeholder="Search ingredients…"
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
      </div>
      <div style="display:flex;align-items:center;gap:.5rem;color:var(--text-muted);font-size:.875rem">
        <i class="fas fa-filter" style="color:var(--primary)"></i>
        <span>Sort by:</span>
        <?php
          $sortOptions = [
            'ingredient_name'  => 'Name',
            'calories_per_100g'=> 'Calories',
            'protein_per_100g' => 'Protein',
            'carbs_per_100g'   => 'Carbs',
            'fat_per_100g'     => 'Fat',
          ];
          foreach ($sortOptions as $col => $lbl):
            $active = $sortBy === $col;
        ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['sort'=>$col,'dir'=>($active&&$dir==='ASC'?'DESC':'ASC')])) ?>"
             class="btn btn-sm <?= $active ? 'btn-primary' : 'btn-outline' ?>" style="padding:.3rem .8rem;font-size:.78rem">
            <?= $lbl ?> <?= $active ? ($dir==='ASC'?'▲':'▼') : '' ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php if ($search): ?>
        <a href="ingredients.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Results Count -->
  <p class="text-muted mb-2" style="font-size:.875rem">
    <i class="fas fa-list"></i>
    Showing <strong><?= count($ingredients) ?></strong> of <?= $totalIngr ?> ingredients
    <?= $search ? ' matching "<strong>'.htmlspecialchars($search).'</strong>"' : '' ?>
  </p>

  <!-- Ingredients Table -->
  <div class="card" style="overflow:hidden">
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
          <tr style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff">
            <th style="padding:.85rem 1rem;text-align:left;white-space:nowrap"><?= sortLink('ingredient_name','Ingredient',$sortBy,$dir) ?></th>
            <th style="padding:.85rem .75rem;text-align:center;white-space:nowrap"><?= sortLink('calories_per_100g','🔥 Kcal',$sortBy,$dir) ?></th>
            <th style="padding:.85rem .75rem;text-align:center;white-space:nowrap"><?= sortLink('protein_per_100g','💪 Protein',$sortBy,$dir) ?></th>
            <th style="padding:.85rem .75rem;text-align:center;white-space:nowrap"><?= sortLink('carbs_per_100g','⚡ Carbs',$sortBy,$dir) ?></th>
            <th style="padding:.85rem .75rem;text-align:center;white-space:nowrap"><?= sortLink('fat_per_100g','💧 Fat',$sortBy,$dir) ?></th>
            <th style="padding:.85rem .75rem;text-align:center;white-space:nowrap"><?= sortLink('fiber_per_100g','🌿 Fiber',$sortBy,$dir) ?></th>
            <th style="padding:.85rem .75rem;text-align:center">Sugar</th>
            <th style="padding:.85rem .75rem;text-align:center">Sodium</th>
            <th style="padding:.85rem .75rem;text-align:center">Macro Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($ingredients)): ?>
            <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--text-muted)">
              <i class="fas fa-search" style="font-size:2rem;display:block;margin-bottom:.75rem;color:var(--border)"></i>
              No ingredients found for "<?= htmlspecialchars($search) ?>".
            </td></tr>
          <?php else: ?>
            <?php foreach ($ingredients as $i => $ing):
              $totalMacro = ($ing['protein_per_100g']*4) + ($ing['carbs_per_100g']*4) + ($ing['fat_per_100g']*9);
              $protPct = $totalMacro > 0 ? round($ing['protein_per_100g']*4/$totalMacro*100) : 0;
              $carbPct = $totalMacro > 0 ? round($ing['carbs_per_100g']*4/$totalMacro*100) : 0;
              $fatPct  = $totalMacro > 0 ? round($ing['fat_per_100g']*9/$totalMacro*100) : 0;

              // Calorie density colour
              $cal = (float)$ing['calories_per_100g'];
              $calColor = $cal < 50 ? '#4CAF50' : ($cal < 150 ? '#FF9800' : ($cal < 300 ? '#F44336' : '#B71C1C'));
            ?>
              <tr style="border-bottom:1px solid var(--border);background:<?= $i%2===0?'#fff':'#FAFAFA' ?>">
                <td style="padding:.7rem 1rem;font-weight:600;color:var(--text)">
                  <i class="fas fa-circle" style="color:var(--primary);font-size:.4rem;margin-right:.5rem;vertical-align:middle"></i>
                  <?= htmlspecialchars($ing['ingredient_name']) ?>
                </td>
                <td style="text-align:center;font-weight:800;color:<?= $calColor ?>"><?= round($cal) ?></td>
                <td style="text-align:center">
                  <span style="background:#E3F2FD;color:#1565C0;padding:.15rem .5rem;border-radius:50px;font-size:.75rem;font-weight:700">
                    <?= number_format((float)$ing['protein_per_100g'],1) ?>g
                  </span>
                </td>
                <td style="text-align:center">
                  <span style="background:#FFF3E0;color:#E65100;padding:.15rem .5rem;border-radius:50px;font-size:.75rem;font-weight:700">
                    <?= number_format((float)$ing['carbs_per_100g'],1) ?>g
                  </span>
                </td>
                <td style="text-align:center">
                  <span style="background:#FCE4EC;color:#AD1457;padding:.15rem .5rem;border-radius:50px;font-size:.75rem;font-weight:700">
                    <?= number_format((float)$ing['fat_per_100g'],1) ?>g
                  </span>
                </td>
                <td style="text-align:center">
                  <span style="background:#E8F5E9;color:#2E7D32;padding:.15rem .5rem;border-radius:50px;font-size:.75rem;font-weight:700">
                    <?= number_format((float)$ing['fiber_per_100g'],1) ?>g
                  </span>
                </td>
                <td style="text-align:center;color:var(--text-muted);font-size:.8rem"><?= number_format((float)$ing['sugar_per_100g'],1) ?>g</td>
                <td style="text-align:center;color:var(--text-muted);font-size:.8rem"><?= number_format((float)$ing['sodium_per_100g'],0) ?>mg</td>
                <td style="padding:.7rem .75rem;min-width:120px">
                  <!-- Stacked macro bar -->
                  <div style="height:8px;border-radius:50px;overflow:hidden;display:flex;background:#f0f0f0" title="P:<?=$protPct?>% C:<?=$carbPct?>% F:<?=$fatPct?>%">
                    <div style="width:<?=$protPct?>%;background:#1565C0;transition:width .3s"></div>
                    <div style="width:<?=$carbPct?>%;background:#E65100;transition:width .3s"></div>
                    <div style="width:<?=$fatPct?>%;background:#AD1457;transition:width .3s"></div>
                  </div>
                  <div style="display:flex;justify-content:space-between;font-size:.62rem;color:var(--text-muted);margin-top:.2rem">
                    <span style="color:#1565C0"><?=$protPct?>%P</span>
                    <span style="color:#E65100"><?=$carbPct?>%C</span>
                    <span style="color:#AD1457"><?=$fatPct?>%F</span>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Legend -->
    <div style="padding:1rem 1.5rem;background:var(--bg);border-top:1px solid var(--border);font-size:.78rem;color:var(--text-muted)">
      <div style="display:flex;gap:1.5rem;flex-wrap:wrap;align-items:center">
        <span><strong>All values per 100g</strong></span>
        <span style="display:flex;align-items:center;gap:.4rem"><span style="width:12px;height:12px;background:#4CAF50;border-radius:2px;display:inline-block"></span> &lt;50 kcal (Low)</span>
        <span style="display:flex;align-items:center;gap:.4rem"><span style="width:12px;height:12px;background:#FF9800;border-radius:2px;display:inline-block"></span> 50–150 kcal (Medium)</span>
        <span style="display:flex;align-items:center;gap:.4rem"><span style="width:12px;height:12px;background:#F44336;border-radius:2px;display:inline-block"></span> 150–300 kcal (High)</span>
        <span style="display:flex;align-items:center;gap:.4rem"><span style="width:12px;height:12px;background:#B71C1C;border-radius:2px;display:inline-block"></span> &gt;300 kcal (Very High)</span>
        <span style="margin-left:auto">Macro bar: <span style="color:#1565C0">■</span> Protein &nbsp;<span style="color:#E65100">■</span> Carbs &nbsp;<span style="color:#AD1457">■</span> Fat</span>
      </div>
    </div>
  </div>

  <!-- Nutrition Tips -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-top:2rem">
    <?php
      $tips = [
        ['icon'=>'fa-dumbbell','title'=>'High Protein Foods','color'=>'#1565C0','bg'=>'#E3F2FD',
         'items'=>['Chicken Breast (31g)','Salmon (20g)','Greek Yogurt (10g)','Eggs (13g)','Canned Tuna (26g)']],
        ['icon'=>'fa-seedling','title'=>'High Fiber Foods','color'=>'#2E7D32','bg'=>'#E8F5E9',
         'items'=>['Chia Seeds (34g)','Flaxseeds (27g)','Oats (11g)','Black Beans (9g)','Quinoa (5g)']],
        ['icon'=>'fa-bolt','title'=>'Complex Carb Sources','color'=>'#E65100','bg'=>'#FFF3E0',
         'items'=>['Brown Rice (45g)','Oats (66g)','Sweet Potato (20g)','Quinoa (39g)','Whole Wheat Bread (41g)']],
        ['icon'=>'fa-heart','title'=>'Healthy Fat Sources','color'=>'#AD1457','bg'=>'#FCE4EC',
         'items'=>['Avocado (15g)','Almonds (50g)','Peanut Butter (50g)','Salmon (13g)','Chia Seeds (31g)']],
      ];
      foreach ($tips as $t):
    ?>
      <div class="card" style="border-top:3px solid <?= $t['color'] ?>">
        <div class="card-body" style="padding:1rem">
          <h4 style="font-size:.875rem;font-weight:700;color:<?= $t['color'] ?>;margin-bottom:.75rem">
            <i class="fas <?= $t['icon'] ?>"></i> <?= $t['title'] ?>
          </h4>
          <?php foreach ($t['items'] as $item): ?>
            <div style="font-size:.8rem;padding:.3rem 0;border-bottom:1px solid var(--border);color:var(--text)">
              <i class="fas fa-circle" style="font-size:.35rem;color:<?= $t['color'] ?>;margin-right:.4rem;vertical-align:middle"></i>
              <?= $item ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
