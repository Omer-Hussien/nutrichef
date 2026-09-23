<?php
// ============================================================
// pages/edit_recipe.php — Edit or Delete an Existing Recipe
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$user = getCurrentUser();
$pdo  = getDB();

$recipeId = (int)($_GET['id'] ?? 0);
if (!$recipeId) { header('Location: recipes.php'); exit; }

$recipe = getRecipeById($recipeId);
if (!$recipe || ($recipe['user_id'] != $user['user_id'] && $user['username'] !== 'admin')) {
    header('Location: recipes.php?error=unauthorized');
    exit;
}

$pageTitle   = 'Edit Recipe — ' . $recipe['title'];
$categories  = getCategories();
$ingredients = $pdo->query("SELECT * FROM ingredients ORDER BY ingredient_name")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- 1. HANDLE RECIPE DELETION & IMAGE CLEANUP ---
    if ($action === 'delete_recipe') {
        if (!empty($recipe['image_url']) && $recipe['image_url'] !== 'default_recipe.jpg') {
            $imgPath = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . $recipe['image_url'];
            if (file_exists($imgPath)) {
                @unlink($imgPath);
            }
        }
        $pdo->prepare("DELETE FROM recipes WHERE recipe_id = ?")->execute([$recipeId]);
        header('Location: recipes.php?msg=deleted');
        exit;
    }

    // --- 2. HANDLE RECIPE UPDATES ---
    if ($action === 'update_recipe') {
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
                $factor      = $qty / 100;
                $totalCal   += $ing['calories_per_100g'] * $factor;
                $totalProt  += $ing['protein_per_100g']  * $factor;
                $totalCarbs += $ing['carbs_per_100g']    * $factor;
                $totalFat   += $ing['fat_per_100g']      * $factor;
                $totalFiber += $ing['fiber_per_100g']    * $factor;
                $selectedIngredients[] = ['id'=>$ingId,'qty'=>$qty,'unit'=>$_POST['ing_unit'][$idx]??'grams'];
            }
        }

        $imageUrl = $recipe['image_url'] ?? 'default_recipe.jpg';
        if (!empty($_FILES['recipe_image']['name']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['recipe_image']['tmp_name'];
            $ext     = strtolower(pathinfo($_FILES['recipe_image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0777, true);

                $newFileName = 'recipe_' . time() . '_' . substr(md5(uniqid()), 0, 4) . '.' . $ext;
                $destination = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . $newFileName;
                
                if (move_uploaded_file($tmpName, $destination)) {
                    // GARBAGE COLLECTION: DELETE OLD IMAGE BEFORE REPLACING
                    if (!empty($recipe['image_url']) && $recipe['image_url'] !== 'default_recipe.jpg') {
                        $oldPath = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . $recipe['image_url'];
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $imageUrl = $newFileName;
                }
            }
        }

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
            'user_id'        => $recipe['user_id'],
            'is_featured'    => isset($_POST['is_featured']) ? 1 : 0,
        ];

        if (empty($data['title']) || empty($data['instructions'])) {
            $error = 'Title and instructions are required.';
        } else {
            $result = saveRecipe($data, $recipeId);
            if ($result['success']) {
                $pdo->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?")->execute([$recipeId]);
                foreach ($selectedIngredients as $si) {
                    $pdo->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit) VALUES (?,?,?,?)")
                        ->execute([$recipeId, $si['id'], $si['qty'], $si['unit']]);
                }
                $success = 'Recipe updated successfully!';
                $recipe  = getRecipeById($recipeId);
            } else {
                $error = 'Could not update recipe.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>">Home</a><span>/</span>
            <a href="recipes.php">Recipes</a><span>/</span>
            <a href="recipe_detail.php?id=<?= $recipeId ?>"><?= htmlspecialchars($recipe['title']) ?></a>
            <span>/</span><span>Edit</span>
        </nav>
        <h1><i class="fas fa-edit"></i> Edit Recipe</h1>
        <p>Update details for: <strong><?= htmlspecialchars($recipe['title']) ?></strong></p>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">

    <?php if ($error):   ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?> <a href="recipe_detail.php?id=<?= $recipeId ?>">View Recipe</a></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:2rem;align-items:start">
        
        <div>
            <form method="POST" enctype="multipart/form-data" novalidate id="updateForm">
                <input type="hidden" name="action" value="update_recipe">

                <div class="card" style="margin-bottom:1.25rem">
                    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
                        <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-info-circle"></i> Basic Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Recipe Title *</label>
                            <input class="form-control" type="text" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required maxlength="150">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" maxlength="500"><?= htmlspecialchars($recipe['description']??'') ?></textarea>
                            <span class="form-hint"><?= strlen($recipe['description']??'') ?> / 500 characters</span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select class="form-control" name="category_id">
                                    <option value="">— Select —</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>" <?= $recipe['category_id']==$cat['category_id']?'selected':'' ?>>
                                            <?= htmlspecialchars($cat['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Dietary Type</label>
                                <select class="form-control" name="dietary_type">
                                    <?php foreach (['None','Vegetarian','Vegan','Gluten-Free','Keto','Paleo'] as $d): ?>
                                        <option value="<?=$d?>" <?= $recipe['dietary_type']===$d?'selected':'' ?>><?=$d?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Prep Time (min)</label>
                                <input class="form-control" type="number" name="prep_time_min" min="0" max="480" value="<?= (int)$recipe['prep_time_min'] ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cook Time (min)</label>
                                <input class="form-control" type="number" name="cook_time_min" min="0" max="480" value="<?= (int)$recipe['cook_time_min'] ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Servings</label>
                                <input class="form-control" type="number" name="servings" min="1" max="50" value="<?= (int)$recipe['servings'] ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Difficulty</label>
                                <select class="form-control" name="difficulty">
                                    <?php foreach (['Easy','Medium','Hard'] as $d): ?>
                                        <option value="<?=$d?>" <?= $recipe['difficulty']===$d?'selected':'' ?>><?=$d?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:1rem;">
                            <label class="form-label"><i class="fas fa-camera" style="color:var(--primary)"></i> Update Recipe Photo</label>
                            <input class="form-control" type="file" name="recipe_image" accept="image/jpeg, image/png, image/webp" style="padding:0.45rem;">
                            <span class="form-hint">Current file: <strong><?= htmlspecialchars($recipe['image_url'] ?? 'None') ?></strong>. Leave blank to keep existing photo.</span>
                        </div>

                        <div style="display:flex;align-items:center;gap:.6rem;margin-top:.75rem">
                            <input type="checkbox" name="is_featured" id="isFeatured" value="1" <?= !empty($recipe['is_featured'])?'checked':'' ?>>
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
                            <?php if (!empty($recipe['ingredients'])): ?>
                                <?php foreach ($recipe['ingredients'] as $ing): ?>
                                <div class="ingredient-row-form" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:.75rem;align-items:end;margin-bottom:.75rem">
                                    <div class="form-group" style="margin:0">
                                        <select class="form-control" name="ingredient_id[]" style="font-size:.85rem">
                                            <option value="">— Select —</option>
                                            <?php foreach ($ingredients as $i_): ?>
                                                <option value="<?= $i_['ingredient_id'] ?>" <?= $i_['ingredient_id']==$ing['ingredient_id']?'selected':'' ?>>
                                                    <?= htmlspecialchars($i_['ingredient_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0">
                                        <input class="form-control" type="number" name="quantity[]" min="0" step="0.1" value="<?= (float)$ing['quantity'] ?>" style="font-size:.85rem">
                                    </div>
                                    <div class="form-group" style="margin:0">
                                        <select class="form-control" name="ing_unit[]" style="font-size:.85rem">
                                            <?php foreach (['grams','ml','cups','tbsp','tsp','pieces'] as $u): ?>
                                                <option value="<?=$u?>" <?= $ing['unit']===$u?'selected':'' ?>><?=$u?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="ingredient-row-form" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:.75rem;align-items:end;margin-bottom:.75rem">
                                    <div class="form-group" style="margin:0">
                                        <select class="form-control" name="ingredient_id[]" style="font-size:.85rem">
                                            <option value="">— Select —</option>
                                            <?php foreach ($ingredients as $i_): ?>
                                                <option value="<?= $i_['ingredient_id'] ?>"><?= htmlspecialchars($i_['ingredient_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0">
                                        <input class="form-control" type="number" name="quantity[]" min="0" step="0.1" placeholder="100" style="font-size:.85rem">
                                    </div>
                                    <div class="form-group" style="margin:0">
                                        <select class="form-control" name="ing_unit[]" style="font-size:.85rem">
                                            <option value="grams">grams</option><option value="ml">ml</option><option value="cups">cups</option><option value="tbsp">tbsp</option><option value="tsp">tsp</option><option value="pieces">pieces</option>
                                        </select>
                                    </div>
                                    <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom:1.25rem">
                    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
                        <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-tasks"></i> Step-by-Step Instructions</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control" name="instructions" rows="10" required maxlength="5000"><?= htmlspecialchars($recipe['instructions']) ?></textarea>
                            <span class="form-hint"><?= strlen($recipe['instructions']) ?> / 5000 characters</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:1rem;align-items:center;margin-bottom:1.5rem;">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Changes</button>
                    <a href="recipe_detail.php?id=<?= $recipeId ?>" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>

            <div style="border-top:2px dashed var(--border); padding-top:1.5rem;">
                <form method="POST" action="" onsubmit="return confirm('Permanently delete this recipe? This cannot be undone.');">
                    <input type="hidden" name="action" value="delete_recipe">
                    <button type="submit" class="btn btn-danger btn-lg"><i class="fas fa-trash"></i> Delete Recipe Completely</button>
                </form>
            </div>
        </div>

        <div style="position:sticky;top:80px">
            <div class="card" style="margin-bottom:1rem">
                <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;padding:1.1rem 1.5rem;border-radius:var(--radius) var(--radius) 0 0">
                    <h4 style="font-size:.9rem;font-weight:700;margin:0"><i class="fas fa-chart-pie"></i> Current Nutrition (Total)</h4>
                </div>
                <div class="card-body">
                    <?php
                    $nFields = [
                        ['label'=>'Calories','val'=>$recipe['total_calories'],'unit'=>'kcal','color'=>'var(--secondary)'],
                        ['label'=>'Protein', 'val'=>$recipe['total_protein'], 'unit'=>'g','color'=>'#1565C0'],
                        ['label'=>'Carbs',   'val'=>$recipe['total_carbs'],   'unit'=>'g','color'=>'#E65100'],
                        ['label'=>'Fat',     'val'=>$recipe['total_fat'],     'unit'=>'g','color'=>'#AD1457'],
                        ['label'=>'Fiber',   'val'=>$recipe['total_fiber'],   'unit'=>'g','color'=>'#2E7D32'],
                    ];
                    foreach ($nFields as $nf): ?>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.875rem">
                        <span style="color:var(--text-muted)"><?= $nf['label'] ?></span>
                        <span style="font-weight:700;color:<?= $nf['color'] ?>"><?= round((float)$nf['val'],1) ?> <?= $nf['unit'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
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
        <div class="form-group" style="margin:0"><select class="form-control" name="ingredient_id[]" style="font-size:.85rem">${ingredientOptions}</select></div>
        <div class="form-group" style="margin:0"><input class="form-control" type="number" name="quantity[]" min="0" step="0.1" placeholder="100" style="font-size:.85rem"></div>
        <div class="form-group" style="margin:0"><select class="form-control" name="ing_unit[]" style="font-size:.85rem"><option value="grams">grams</option><option value="ml">ml</option><option value="cups">cups</option><option value="tbsp">tbsp</option><option value="tsp">tsp</option><option value="pieces">pieces</option></select></div>
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