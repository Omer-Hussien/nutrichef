<?php
// ============================================================
// pages/admin.php — Admin Panel
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$user = getCurrentUser();
// Only admin (user_id = 1 or username = 'admin') can access
if ($user['username'] !== 'admin' && $user['user_id'] !== 1) {
    header('Location: dashboard.php');
    exit;
}

$pdo       = getDB();
$pageTitle = 'Admin Panel';

// Handle actions
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_recipe') {
        $rid = (int)$_POST['recipe_id'];
        
        // 1. Fetch old image filename before wiping the database row
        $stmt = $pdo->prepare("SELECT image_url FROM recipes WHERE recipe_id = ?");
        $stmt->execute([$rid]);
        $oldImg = $stmt->fetchColumn();
        
        if ($oldImg && $oldImg !== 'default_recipe.jpg') {
            $imgPath = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . $oldImg;
            if (file_exists($imgPath)) {
                @unlink($imgPath); // Wipes file off disk
            }
        }

        // 2. Wipe database record
        $pdo->prepare("DELETE FROM recipes WHERE recipe_id = ?")->execute([$rid]);
        $msg = 'Recipe and associated image file deleted successfully.';
    }
    if ($action === 'toggle_featured') {
        $rid = (int)$_POST['recipe_id'];
        $pdo->prepare("UPDATE recipes SET is_featured = NOT is_featured WHERE recipe_id = ?")->execute([$rid]);
        $msg = 'Featured status updated.';
    }
    if ($action === 'delete_user') {
        $uid = (int)$_POST['user_id'];
        if ($uid !== 1) {
            $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$uid]);
            $msg = 'User deleted.';
        } else {
            $msg = 'Cannot delete the admin account.';
        }
    }
    if ($action === 'add_category') {
        $name  = sanitize($_POST['cat_name'] ?? '');
        $icon  = sanitize($_POST['cat_icon'] ?? 'fa-utensils');
        $color = sanitize($_POST['cat_color'] ?? '#4CAF50');
        if ($name) {
            $pdo->prepare("INSERT IGNORE INTO categories (category_name, icon, color_hex) VALUES (?,?,?)")->execute([$name, $icon, $color]);
            $msg = "Category '$name' added.";
        }
    }
    if ($action === 'delete_category') {
        $cid = (int)$_POST['category_id'];
        $pdo->prepare("DELETE FROM categories WHERE category_id = ?")->execute([$cid]);
        $msg = 'Category deleted.';
    }
}

// Stats
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalRecipes  = $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$totalReviews  = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$totalSaves    = $pdo->query("SELECT COUNT(*) FROM saved_recipes")->fetchColumn();
$totalPlans    = $pdo->query("SELECT COUNT(*) FROM meal_plans")->fetchColumn();

// Active tab
$tab = $_GET['tab'] ?? 'overview';

// Data per tab
$allRecipes = $pdo->query(
    "SELECT r.*, u.username, c.category_name
     FROM recipes r
     LEFT JOIN users u ON r.user_id = u.user_id
     LEFT JOIN categories c ON r.category_id = c.category_id
     ORDER BY r.created_at DESC LIMIT 100"
)->fetchAll();

$allUsers = $pdo->query(
    "SELECT u.*, COUNT(DISTINCT r.recipe_id) as recipe_count, COUNT(DISTINCT rv.review_id) as review_count
     FROM users u
     LEFT JOIN recipes r ON r.user_id = u.user_id
     LEFT JOIN reviews rv ON rv.user_id = u.user_id
     GROUP BY u.user_id ORDER BY u.created_at DESC"
)->fetchAll();

$allCategories = getCategories();

$recentReviews = $pdo->query(
    "SELECT rv.*, u.username, r.title
     FROM reviews rv
     JOIN users u ON rv.user_id = u.user_id
     JOIN recipes r ON rv.recipe_id = r.recipe_id
     ORDER BY rv.created_at DESC LIMIT 20"
)->fetchAll();

// Top recipes by views
$topRecipes = $pdo->query(
    "SELECT r.title, r.view_count, r.rating_avg, u.username
     FROM recipes r LEFT JOIN users u ON r.user_id = u.user_id
     ORDER BY r.view_count DESC LIMIT 10"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<style>
.admin-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:1.5rem; flex-wrap:wrap; }
.admin-tab-btn {
    padding:.6rem 1.1rem; border:none; background:none; font-family:var(--font);
    font-size:.875rem; font-weight:600; color:var(--text-muted); cursor:pointer;
    border-bottom:3px solid transparent; margin-bottom:-2px; transition:all var(--transition);
    display:flex; align-items:center; gap:.4rem; white-space:nowrap;
}
.admin-tab-btn:hover { color:var(--primary); }
.admin-tab-btn.active { color:var(--primary-dark); border-bottom-color:var(--primary); }
.admin-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.admin-table th { background:var(--primary); color:#fff; padding:.65rem .85rem; text-align:left; font-weight:600; }
.admin-table td { padding:.6rem .85rem; border-bottom:1px solid var(--border); vertical-align:middle; }
.admin-table tr:hover td { background:#F1F8E9; }
.admin-table tr:nth-child(even) td { background:#FAFAFA; }
.admin-table tr:nth-child(even):hover td { background:#F1F8E9; }
</style>

<!-- Admin Header -->
<div style="background:linear-gradient(135deg,#1B5E20,#2E7D32,#00838F);color:#fff;padding:2rem 0;margin-bottom:0">
    <div class="container">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
            <div>
                <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1px;opacity:.75;margin-bottom:.3rem">
                    <i class="fas fa-shield-alt"></i> Administrator Access
                </div>
                <h1 style="font-size:1.8rem;font-weight:800;margin:0">Admin Control Panel</h1>
                <p style="opacity:.8;margin:.25rem 0 0;font-size:.9rem">NutriChef System Management</p>
            </div>
            <div style="display:flex;gap:.75rem">
                <a href="<?= SITE_URL ?>" class="btn btn-white btn-sm"><i class="fas fa-home"></i> View Site</a>
                <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="fas fa-chart-pie"></i> Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding-top:2rem;padding-bottom:3rem">

    <?php if ($msg): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem"><i class="fas fa-check-circle"></i><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="stats-grid" style="margin-bottom:2rem">
        <?php
        $stats = [
            ['icon'=>'fa-users','label'=>'Total Users','value'=>$totalUsers,'color'=>'#2196F3','bg'=>'#E3F2FD'],
            ['icon'=>'fa-utensils','label'=>'Recipes','value'=>$totalRecipes,'color'=>'#4CAF50','bg'=>'#E8F5E9'],
            ['icon'=>'fa-star','label'=>'Reviews','value'=>$totalReviews,'color'=>'#FF9800','bg'=>'#FFF3E0'],
            ['icon'=>'fa-heart','label'=>'Saves','value'=>$totalSaves,'color'=>'#E91E63','bg'=>'#FCE4EC'],
            ['icon'=>'fa-calendar','label'=>'Meal Plans','value'=>$totalPlans,'color'=>'#9C27B0','bg'=>'#F3E5F5'],
        ];
        foreach ($stats as $s): ?>
        <div class="stat-card">
            <div class="stat-icon" style="background:<?= $s['bg'] ?>">
                <i class="fas <?= $s['icon'] ?>" style="color:<?= $s['color'] ?>"></i>
            </div>
            <div class="stat-value" style="font-size:1.6rem"><?= number_format($s['value']) ?></div>
            <div class="stat-label"><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
        <?php
        $tabs = [
            'overview'   => ['icon'=>'fa-chart-bar','label'=>'Overview'],
            'recipes'    => ['icon'=>'fa-utensils','label'=>'Recipes ('.$totalRecipes.')'],
            'users'      => ['icon'=>'fa-users','label'=>'Users ('.$totalUsers.')'],
            'categories' => ['icon'=>'fa-th-large','label'=>'Categories'],
            'reviews'    => ['icon'=>'fa-star','label'=>'Reviews'],
        ];
        foreach ($tabs as $key => $t): ?>
        <a href="?tab=<?= $key ?>" class="admin-tab-btn <?= $tab===$key?'active':'' ?>">
            <i class="fas <?= $t['icon'] ?>"></i> <?= $t['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ── OVERVIEW ── -->
    <?php if ($tab === 'overview'): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

        <!-- Top Recipes -->
        <div class="card">
            <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border)">
                <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-fire-alt" style="color:var(--secondary)"></i> Top Recipes by Views</h3>
            </div>
            <div class="card-body" style="padding:0">
                <table class="admin-table">
                    <thead><tr><th>Recipe</th><th>Author</th><th style="text-align:center">Views</th><th style="text-align:center">Rating</th></tr></thead>
                    <tbody>
                    <?php foreach ($topRecipes as $r): ?>
                        <tr>
                            <td style="font-weight:600"><?= htmlspecialchars($r['title']) ?></td>
                            <td style="color:var(--text-muted)">@<?= htmlspecialchars($r['username']??'—') ?></td>
                            <td style="text-align:center"><span class="badge badge-green"><?= number_format($r['view_count']) ?></span></td>
                            <td style="text-align:center">
                                <span style="color:var(--warning);font-weight:700"><?= number_format((float)$r['rating_avg'],1) ?></span>
                                <i class="fas fa-star" style="color:var(--warning);font-size:.8rem"></i>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Signups -->
        <div class="card">
            <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border)">
                <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-user-plus" style="color:var(--accent)"></i> Recent Members</h3>
            </div>
            <div class="card-body" style="padding:0">
                <table class="admin-table">
                    <thead><tr><th>User</th><th>Goal</th><th>Joined</th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($allUsers, 0, 10) as $u): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600"><?= htmlspecialchars($u['full_name']) ?></div>
                                <div style="font-size:.75rem;color:var(--text-muted)">@<?= htmlspecialchars($u['username']) ?></div>
                            </td>
                            <td><span class="badge badge-teal" style="font-size:.65rem"><?= htmlspecialchars($u['health_goal']??'—') ?></span></td>
                            <td style="color:var(--text-muted);font-size:.8rem"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="grid-column:span 2">
            <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border)">
                <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-bolt" style="color:var(--warning)"></i> Quick Actions</h3>
            </div>
            <div class="card-body" style="display:flex;gap:1rem;flex-wrap:wrap">
                <a href="add_recipe.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Recipe</a>
                <a href="?tab=categories" class="btn btn-secondary"><i class="fas fa-plus"></i> Add Category</a>
                <a href="?tab=recipes" class="btn btn-outline"><i class="fas fa-utensils"></i> Manage Recipes</a>
                <a href="?tab=users" class="btn btn-outline"><i class="fas fa-users"></i> Manage Users</a>
                <a href="?tab=reviews" class="btn btn-outline"><i class="fas fa-star"></i> View Reviews</a>
            </div>
        </div>
    </div>

    <!-- ── RECIPES ── -->
    <?php elseif ($tab === 'recipes'): ?>
    <div class="card">
        <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-utensils"></i> All Recipes</h3>
            <a href="add_recipe.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Recipe</a>
        </div>
        <div style="overflow-x:auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th><th>Title</th><th>Category</th><th>Author</th>
                    <th style="text-align:center">Featured</th><th style="text-align:center">Calories</th>
                    <th style="text-align:center">Views</th><th style="text-align:center">Rating</th>
                    <th style="text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($allRecipes as $r): ?>
                <tr>
                    <td style="color:var(--text-muted)">#<?= $r['recipe_id'] ?></td>
                    <td style="font-weight:600;max-width:200px">
                        <a href="recipe_detail.php?id=<?= $r['recipe_id'] ?>" style="color:var(--primary-dark)"><?= htmlspecialchars($r['title']) ?></a>
                    </td>
                    <td><span class="badge badge-green" style="font-size:.65rem"><?= htmlspecialchars($r['category_name']??'—') ?></span></td>
                    <td style="color:var(--text-muted)">@<?= htmlspecialchars($r['username']??'—') ?></td>
                    <td style="text-align:center">
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="toggle_featured">
                            <input type="hidden" name="recipe_id" value="<?= $r['recipe_id'] ?>">
                            <button type="submit" style="background:none;border:none;cursor:pointer;font-size:1.1rem" title="Toggle featured">
                                <?= $r['is_featured'] ? '⭐' : '☆' ?>
                            </button>
                        </form>
                    </td>
                    <td style="text-align:center;font-weight:600;color:var(--secondary)"><?= round((float)$r['total_calories']) ?></td>
                    <td style="text-align:center"><?= number_format($r['view_count']) ?></td>
                    <td style="text-align:center">
                        <span style="color:var(--warning)"><?= number_format((float)$r['rating_avg'],1) ?>★</span>
                    </td>
                    <td style="text-align:center">
                        <div style="display:flex;gap:.4rem;justify-content:center">
                            <a href="edit_recipe.php?id=<?= $r['recipe_id'] ?>" class="btn btn-outline btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this recipe permanently?')">
                                <input type="hidden" name="action" value="delete_recipe">
                                <input type="hidden" name="recipe_id" value="<?= $r['recipe_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- ── USERS ── -->
    <?php elseif ($tab === 'users'): ?>
    <div class="card">
        <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border)">
            <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-users"></i> All Users</h3>
        </div>
        <div style="overflow-x:auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Goal</th><th>Diet</th>
                    <th style="text-align:center">Recipes</th><th style="text-align:center">Reviews</th>
                    <th>Joined</th><th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($allUsers as $u): ?>
                <tr>
                    <td style="color:var(--text-muted)">#<?= $u['user_id'] ?></td>
                    <td>
                        <div style="font-weight:600"><?= htmlspecialchars($u['full_name']) ?></div>
                        <div style="font-size:.75rem;color:var(--text-muted)">@<?= htmlspecialchars($u['username']) ?></div>
                    </td>
                    <td style="font-size:.82rem"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-green" style="font-size:.62rem"><?= htmlspecialchars($u['health_goal']??'—') ?></span></td>
                    <td style="font-size:.8rem;color:var(--text-muted)"><?= htmlspecialchars($u['dietary_preference']??'None') ?></td>
                    <td style="text-align:center"><?= $u['recipe_count'] ?></td>
                    <td style="text-align:center"><?= $u['review_count'] ?></td>
                    <td style="color:var(--text-muted);font-size:.8rem"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    <td style="text-align:center">
                        <?php if ($u['user_id'] != 1): ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete user @<?= htmlspecialchars($u['username']) ?>? This cannot be undone.')">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php else: ?>
                            <span style="font-size:.75rem;color:var(--primary-dark);font-weight:700">Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- ── CATEGORIES ── -->
    <?php elseif ($tab === 'categories'): ?>
    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start">
        <div class="card">
            <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border)">
                <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-th-large"></i> All Categories</h3>
            </div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Icon</th><th>Name</th><th>Color</th><th style="text-align:center">Recipes</th><th style="text-align:center">Action</th></tr></thead>
                <tbody>
                <?php foreach ($allCategories as $cat): ?>
                    <tr>
                        <td style="color:var(--text-muted)">#<?= $cat['category_id'] ?></td>
                        <td><i class="fas <?= htmlspecialchars($cat['icon']) ?>" style="color:<?= htmlspecialchars($cat['color_hex']) ?>;font-size:1.2rem"></i></td>
                        <td style="font-weight:600"><?= htmlspecialchars($cat['category_name']) ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <span style="width:18px;height:18px;border-radius:50%;background:<?= htmlspecialchars($cat['color_hex']) ?>;display:inline-block;border:1px solid var(--border)"></span>
                                <code style="font-size:.8rem"><?= htmlspecialchars($cat['color_hex']) ?></code>
                            </div>
                        </td>
                        <td style="text-align:center"><span class="badge badge-green"><?= $cat['recipe_count'] ?></span></td>
                        <td style="text-align:center">
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete category?')">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Category Form -->
        <div class="card">
            <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border)">
                <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-plus-circle" style="color:var(--primary)"></i> Add New Category</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_category">
                    <div class="form-group">
                        <label class="form-label">Category Name *</label>
                        <input class="form-control" type="text" name="cat_name" placeholder="e.g. Smoothie Bowls" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Font Awesome Icon Class</label>
                        <input class="form-control" type="text" name="cat_icon" placeholder="e.g. fa-blender" value="fa-utensils">
                        <span class="form-hint">Use any FA 6 icon name from fontawesome.com</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category Color</label>
                        <input class="form-control" type="color" name="cat_color" value="#4CAF50" style="height:42px;padding:.25rem">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> Add Category</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── REVIEWS ── -->
    <?php elseif ($tab === 'reviews'): ?>
    <div class="card">
        <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border)">
            <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-star"></i> Recent Reviews</h3>
        </div>
        <table class="admin-table">
            <thead><tr><th>User</th><th>Recipe</th><th style="text-align:center">Rating</th><th>Review</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($recentReviews as $rv): ?>
                <tr>
                    <td><strong>@<?= htmlspecialchars($rv['username']) ?></strong></td>
                    <td>
                        <a href="recipe_detail.php?id=<?= $rv['recipe_id'] ?>" style="color:var(--primary-dark);font-weight:600">
                            <?= htmlspecialchars($rv['title']) ?>
                        </a>
                    </td>
                    <td style="text-align:center">
                        <?php for ($i=1;$i<=5;$i++) echo '<i class="'.($i<=(int)$rv['rating']?'fas':'far').' fa-star" style="color:var(--warning);font-size:.85rem"></i>'; ?>
                    </td>
                    <td style="max-width:300px;font-size:.82rem;color:var(--text)">
                        <?= htmlspecialchars(mb_strimwidth($rv['review_text']??'(No text)', 0, 100, '…')) ?>
                    </td>
                    <td style="color:var(--text-muted);font-size:.8rem"><?= date('M j, Y', strtotime($rv['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
