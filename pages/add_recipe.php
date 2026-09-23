<?php
// ============================================================
// pages/add_recipe.php — Automated Recipe & Photo Publisher
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();
$user = getCurrentUser();
$pageTitle = 'Add New Recipe';

$categories   = getCategories();
$pdo          = getDB();
$ingredients  = $pdo->query("SELECT * FROM ingredients ORDER BY ingredient_name")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalCal = $totalProt = $totalCarbs = $totalFat = $totalFiber = 0;
    $selectedIngredients = [];

    if (!empty($_POST['ingredient_id'])) {
        foreach ($_POST['ingredient_id'] as $idx => $ingId) {
            $ingId = (int)$ingId;
            $qty   = (float)($_POST['quantity'][$idx] ?? 0);
            if (!$ingId || $qty <= 0) continue;
            $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_id=?");
            $stmt->execute([$ingId]);
            $ing = $stmt->fetch();
            if (!$ing) continue;
            $factor = $qty / 100;
            $totalCal   += $ing['calories_per_100g'] * $factor;
            $totalProt  += $ing['protein_per_100g']  * $factor;
            $totalCarbs += $ing['carbs_per_100g']    * $factor;
            $totalFat   += $ing['fat_per_100g']      * $factor;
            $totalFiber += $ing['fiber_per_100g']    * $factor;
            $selectedIngredients[] = ['id'=>$ingId,'qty'=>$qty,'unit'=>$_POST['ing_unit'][$idx]??'grams'];
        }
    }

    $imageUrl = 'default_recipe.jpg';
    if (!empty($_FILES['recipe_image']['name'])) {
        if ($_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['recipe_image']['tmp_name'];
            $ext     = strtolower(pathinfo($_FILES['recipe_image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                if (!is_dir(UPLOAD_PATH)) {
                    mkdir(UPLOAD_PATH, 0777, true);
                }

                $newFileName = 'recipe_' . time() . '_' . substr(md5(uniqid()), 0, 4) . '.' . $ext;
                $destination = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . $newFileName;
                
                if (move_uploaded_file($tmpName, $destination)) {
                    $imageUrl = $newFileName;
                } else {
                    $error = "Server Error: Could not move picture into images folder.";
                }
            } else {
                $error = "Invalid picture format. Only JPG, PNG, and WEBP files allowed.";
            }
        } else {
            $err = $_FILES['recipe_image']['error'];
            if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                $error = "Picture is too large! Max upload size is 10MB.";
            } else {
                $error = "File upload failed with error code: " . $err;
            }
        }
    }

    if (empty($error)) {
        $data = [
            'title'          => sanitize($_POST['title'] ?? ''),
            'description'    => sanitize($_POST['description'] ?? ''),
            'category_id'    => max(1, (int)($_POST['category_id'] ?? 1)),
            'prep_time_min'  => (int)($_POST['prep_time_min'] ?? 0),
            'cook_time_min'  => (int)($_POST['cook_time_min'] ?? 0),
            'servings'       => max(1, (int)($_POST['servings'] ?? 1)),
            'difficulty'     => $_POST['difficulty'] ?? 'Medium',
            'dietary_type'   => $_POST['dietary_type'] ?? 'None',
            'image_url'      => $imageUrl,
            'instructions'   => sanitize($_POST['instructions'] ?? ''),
            'total_calories' => round($totalCal, 2),
            'total_protein'  => round($totalProt, 2),
            'total_carbs'    => round($totalCarbs, 2),
            'total_fat'      => round($totalFat, 2),
            'total_fiber'    => round($totalFiber, 2),
            'user_id'        => $user['user_id'],
            'is_featured'    => isset($_POST['is_featured']) ? 1 : 0,
        ];

        if (empty($data['title']) || empty($data['instructions'])) {
            $error = 'Title and instructions are required.';
        } else {
            $result = saveRecipe($data);
            if ($result['success']) {
                $newId = $result['recipe_id'];
                foreach ($selectedIngredients as $si) {
                    $pdo->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit) VALUES (?,?,?,?)")
                        ->execute([$newId, $si['id'], $si['qty'], $si['unit']]);
                }
                header("Location: recipe_detail.php?id={$newId}&msg=created");
                exit;
            }
            $error = 'Could not save recipe to database.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>">Home</a><span>/</span><a href="recipes.php">Recipes</a><span>/</span><span>Add Recipe</span></nav>
    <h1><i class="fas fa-plus-circle"></i> Add New Recipe</h1>
    <p>Share your favourite healthy recipe with the NutriChef community</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">

  <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data" novalidate>
    <div style="display:grid;grid-template-columns:1fr 380px;gap:2rem;align-items:start">

      <div>
        <div class="card" style="margin-bottom:1.25rem">
          <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
            <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-info-circle"></i> Basic Information</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Recipe Title *</label>
              <input class="form-control" type="text" name="title" value="<?= htmlspecialchars($_POST['title']??'') ?>"
                     placeholder="e.g. Grilled Salmon with Quinoa" required maxlength="150">
            </div>
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3" maxlength="500"
                        placeholder="A brief description of this recipe and its health benefits…"><?= htmlspecialchars($_POST['description']??'') ?></textarea>
              <span class="form-hint">0 / 500 characters</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
              <div class="form-group">
                <label class="form-label">Category</label>
                <select class="form-control" name="category_id">
                  <option value="">— Select Category —</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= ($_POST['category_id']??'')==$cat['category_id']?'selected':'' ?>>
                      <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Dietary Type</label>
                <select class="form-control" name="dietary_type">
                  <?php foreach (['None','Vegetarian','Vegan','Gluten-Free','Keto','Paleo'] as $d): ?>
                    <option value="<?=$d?>" <?= ($_POST['dietary_type']??'')===$d?'selected':'' ?>><?=$d?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Prep Time (min)</label>
                <input class="form-control" type="number" name="prep_time_min" min="0" max="480" value="<?= htmlspecialchars($_POST['prep_time_min']??'0') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Cook Time (min)</label>
                <input class="form-control" type="number" name="cook_time_min" min="0" max="480" value="<?= htmlspecialchars($_POST['cook_time_min']??'0') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Servings</label>
                <input class="form-control" type="number" name="servings" min="1" max="50" value="<?= htmlspecialchars($_POST['servings']??'2') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Difficulty</label>
                <select class="form-control" name="difficulty">
                  <?php foreach (['Easy','Medium','Hard'] as $d): ?>
                    <option value="<?=$d?>" <?= ($_POST['difficulty']??'Medium')===$d?'selected':'' ?>><?=$d?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            
            <div class="form-group" style="margin-top:1rem;">
              <label class="form-label"><i class="fas fa-camera" style="color:var(--primary)"></i> Recipe Photo</label>
              <input class="form-control" type="file" name="recipe_image" accept="image/jpeg, image/png, image/webp" style="padding:0.45rem;">
              <span class="form-hint">Accepted formats: JPG, PNG, WEBP (Max 10MB)</span>
            </div>

            <div style="display:flex;align-items:center;gap:.6rem;margin-top:.75rem">
              <input type="checkbox" name="is_featured" id="isFeatured" value="1" <?= isset($_POST['is_featured'])?'checked':'' ?>>
              <label for="isFeatured" style="font-size:.875rem;cursor:pointer">⭐ Mark as Featured Recipe</label>
            </div>
          </div>
        </div>

        <div class="card" style="margin-bottom:1.25rem">
          <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-list-ul"></i> Ingredients</h3>
            <button type="button" onclick="addIngredientRow()" class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> Add Row</button>
          </div>
          <div class="card-body">
            <div id="ingredientsContainer">
              <div class="ingredient-row-form" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:.75rem;align-items:end;margin-bottom:.75rem">
                <div class="form-group" style="margin:0">
                  <label class="form-label" style="font-size:.78rem">Ingredient</label>
                  <select class="form-control" name="ingredient_id[]" style="font-size:.85rem">
                    <option value="">— Select —</option>
                    <?php foreach ($ingredients as $ing): ?>
                      <option value="<?= $ing['ingredient_id'] ?>"><?= htmlspecialchars($ing['ingredient_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group" style="margin:0">
                  <label class="form-label" style="font-size:.78rem">Quantity</label>
                  <input class="form-control" type="number" name="quantity[]" min="0" step="0.1" placeholder="e.g. 100" style="font-size:.85rem">
                </div>
                <div class="form-group" style="margin:0">
                  <label class="form-label" style="font-size:.78rem">Unit</label>
                  <select class="form-control" name="ing_unit[]" style="font-size:.85rem">
                    <option value="grams">grams</option><option value="ml">ml</option>
                    <option value="cups">cups</option><option value="tbsp">tbsp</option>
                    <option value="tsp">tsp</option><option value="pieces">pieces</option>
                  </select>
                </div>
                <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" style="margin-bottom:0"><i class="fas fa-trash"></i></button>
              </div>
            </div>
            <p class="text-muted" style="font-size:.78rem;margin-top:.5rem">
              <i class="fas fa-info-circle"></i> Nutrition will be auto-calculated from ingredient quantities (per 100g basis).
            </p>
          </div>
        </div>

        <div class="card" style="margin-bottom:1.25rem">
          <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
            <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-tasks"></i> Step-by-Step Instructions</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Instructions *</label>
              <textarea class="form-control" name="instructions" rows="10" required maxlength="5000"
                        placeholder="1. Preheat oven to 200°C.&#10;2. Season the chicken breast with salt and pepper.&#10;3. ..."><?= htmlspecialchars($_POST['instructions']??'') ?></textarea>
              <span class="form-hint">Enter each step on a new line. Start each step with a number (1. 2. 3.) for best display. 0 / 5000 characters</span>
            </div>
          </div>
        </div>

        <div style="display:flex;gap:1rem">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-check-circle"></i> Publish Recipe</button>
          <a href="recipes.php" class="btn btn-outline btn-lg">Cancel</a>
        </div>
      </div>

      <div style="position:sticky;top:80px">
        <div class="card" style="margin-bottom:1rem;border-left:4px solid var(--primary)">
          <div class="card-body">
            <h4 style="font-size:.9rem;font-weight:700;color:var(--primary-dark);margin-bottom:.85rem"><i class="fas fa-lightbulb" style="color:var(--warning)"></i> Recipe Tips</h4>
            <?php
              $tips = [
                ['icon'=>'fa-heading','text'=>'Use a clear, descriptive title that includes the main ingredient.'],
                ['icon'=>'fa-align-left','text'=>'Write a short description highlighting the health benefits.'],
                ['icon'=>'fa-list-ol','text'=>'Number each instruction step clearly for easy following.'],
                ['icon'=>'fa-weight','text'=>'Enter ingredient quantities in grams/ml for accurate nutrition calculation.'],
                ['icon'=>'fa-clock','text'=>'Include accurate prep and cook times to help users plan ahead.'],
              ];
              foreach ($tips as $t):
            ?>
              <div style="display:flex;gap:.6rem;margin-bottom:.6rem;font-size:.82rem;color:var(--text)">
                <i class="fas <?= $t['icon'] ?>" style="color:var(--primary);width:16px;flex-shrink:0;margin-top:.15rem"></i>
                <span><?= $t['text'] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card" style="border-left:4px solid var(--accent)">
          <div class="card-body">
            <h4 style="font-size:.9rem;font-weight:700;color:var(--accent);margin-bottom:.85rem"><i class="fas fa-chart-pie"></i> Nutrition Auto-Calc</h4>
            <p style="font-size:.82rem;color:var(--text-muted);line-height:1.7">
              Nutrition totals are automatically calculated from the ingredients you add. 
              Make sure to select each ingredient and enter its quantity in grams or ml for the most accurate results.
            </p>
            <div style="margin-top:.75rem;background:var(--bg);border-radius:var(--radius-sm);padding:.75rem;font-size:.8rem">
              <div style="font-weight:600;margin-bottom:.35rem;color:var(--primary-dark)">Calculated after submit:</div>
              <div>🔥 Total Calories</div>
              <div>💪 Protein (g)</div>
              <div>⚡ Carbohydrates (g)</div>
              <div>💧 Fat (g)</div>
              <div>🌿 Dietary Fiber (g)</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </form>
</div>

<script>
const ingredientOptions = `<?php
  $opts = '<option value="">— Select —</option>';
  foreach ($ingredients as $ing) {
    $opts .= '<option value="'.$ing['ingredient_id'].'">'.htmlspecialchars($ing['ingredient_name']).'</option>';
  }
  echo $opts;
?>`;

function addIngredientRow() {
  const container = document.getElementById('ingredientsContainer');
  const div = document.createElement('div');
  div.className = 'ingredient-row-form';
  div.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:.75rem;align-items:end;margin-bottom:.75rem';
  div.innerHTML = `
    <div class="form-group" style="margin:0">
      <select class="form-control" name="ingredient_id[]" style="font-size:.85rem">${ingredientOptions}</select>
    </div>
    <div class="form-group" style="margin:0">
      <input class="form-control" type="number" name="quantity[]" min="0" step="0.1" placeholder="e.g. 100" style="font-size:.85rem">
    </div>
    <div class="form-group" style="margin:0">
      <select class="form-control" name="ing_unit[]" style="font-size:.85rem">
        <option value="grams">grams</option><option value="ml">ml</option>
        <option value="cups">cups</option><option value="tbsp">tbsp</option>
        <option value="tsp">tsp</option><option value="pieces">pieces</option>
      </select>
    </div>
    <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
  `;
  container.appendChild(div);
}

function removeRow(btn) {
  const rows = document.querySelectorAll('.ingredient-row-form');
  if (rows.length > 1) btn.closest('.ingredient-row-form').remove();
  else alert('At least one ingredient row is required.');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>