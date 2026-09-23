<?php
// ============================================================
// pages/profile.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();
$user = getCurrentUser();
$pageTitle = 'My Profile';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();
    // Check email unique (excluding self)
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email=? AND user_id != ?");
    $stmt->execute([$_POST['email'], $user['user_id']]);
    if ($stmt->fetch()) {
        $error = 'That email is already in use by another account.';
    } else {
        $fields = ['full_name','email','age','gender','weight_kg','height_cm','activity_level','dietary_preference','health_goal'];
        $values = [];
        foreach ($fields as $f) $values[$f] = isset($_POST[$f]) ? sanitize($_POST[$f]) : $user[$f];
        // Handle password change
        if (!empty($_POST['new_password'])) {
            if (strlen($_POST['new_password']) < 8) {
                $error = 'New password must be at least 8 characters.';
            } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
                $error = 'New passwords do not match.';
            } elseif (!password_verify($_POST['current_password'], $user['password_hash'])) {
                $error = 'Current password is incorrect.';
            } else {
                $values['password_hash'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            }
        }
        if (!$error) {
            $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($values)));
            $stmt = $pdo->prepare("UPDATE users SET $set WHERE user_id = ?");
            $stmt->execute([...array_values($values), $user['user_id']]);
            $success = 'Profile updated successfully!';
            $user = getCurrentUser(); // Refresh
        }
    }
}

$bmiData  = ($user['weight_kg'] && $user['height_cm']) ? calculateBMI((float)$user['weight_kg'], (float)$user['height_cm']) : null;
$dailyCal = calculateDailyCalories($user);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>">Home</a><span>/</span><a href="dashboard.php">Dashboard</a><span>/</span><span>My Profile</span></nav>
    <h1><i class="fas fa-user-cog"></i> My Profile</h1>
    <p>Manage your personal information, goals and preferences</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">
  <div style="display:grid;grid-template-columns:300px 1fr;gap:2rem;align-items:start">

    <!-- Profile Card Sidebar -->
    <div>
      <div class="card" style="margin-bottom:1rem">
        <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));padding:2rem;text-align:center;color:#fff">
          <div style="width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.2);
                      border:3px solid rgba(255,255,255,.5);display:flex;align-items:center;
                      justify-content:center;margin:0 auto 1rem;font-size:2.2rem;font-weight:800;color:#fff">
            <?= strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)) ?>
          </div>
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.25rem"><?= htmlspecialchars($user['full_name']) ?></h3>
          <p style="opacity:.8;font-size:.85rem">@<?= htmlspecialchars($user['username']) ?></p>
          <span class="badge" style="background:rgba(255,255,255,.2);color:#fff;margin-top:.5rem"><?= htmlspecialchars($user['health_goal']) ?></span>
        </div>
        <div class="card-body">
          <?php if ($bmiData): ?>
            <?php $bmiColor = match(true){$bmiData['bmi']<18.5=>'#2196F3',$bmiData['bmi']<25=>'#4CAF50',$bmiData['bmi']<30=>'#FF9800',default=>'#F44336'}; ?>
            <div style="text-align:center;padding:.85rem;background:var(--bg);border-radius:var(--radius-sm);margin-bottom:1rem">
              <canvas id="bmiGauge" width="200" height="120"
                      data-chart="bmi" data-bmi="<?= $bmiData['bmi'] ?>"
                      style="width:200px;height:120px;display:block;margin:0 auto .5rem"></canvas>
              <span class="badge" style="background:<?= $bmiColor ?>22;color:<?= $bmiColor ?>"><?= $bmiData['category'] ?></span>
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem">Body Mass Index</div>
            </div>
          <?php endif; ?>
          <div style="background:var(--bg);border-radius:var(--radius-sm);padding:.85rem;text-align:center;margin-bottom:1rem">
            <div style="font-size:1.5rem;font-weight:800;color:var(--primary-dark)"><?= number_format($dailyCal) ?></div>
            <div style="font-size:.72rem;color:var(--text-muted)">Recommended Daily Calories</div>
          </div>
          <?php
            $quickInfo = [
              ['icon'=>'fa-ruler-vertical','label'=>'Height','value'=>($user['height_cm']?$user['height_cm'].' cm':'—')],
              ['icon'=>'fa-weight','label'=>'Weight','value'=>($user['weight_kg']?$user['weight_kg'].' kg':'—')],
              ['icon'=>'fa-birthday-cake','label'=>'Age','value'=>($user['age']?$user['age'].' years':'—')],
              ['icon'=>'fa-seedling','label'=>'Diet','value'=>$user['dietary_preference']??'None'],
            ];
            foreach ($quickInfo as $qi):
          ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.85rem">
              <span style="color:var(--text-muted)"><i class="fas <?= $qi['icon'] ?>" style="width:16px;color:var(--primary)"></i> <?= $qi['label'] ?></span>
              <span style="font-weight:600"><?= htmlspecialchars($qi['value']) ?></span>
            </div>
          <?php endforeach; ?>
          <div style="margin-top:1rem">
            <a href="dashboard.php" class="btn btn-outline btn-block btn-sm"><i class="fas fa-chart-pie"></i> My Dashboard</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Form -->
    <div>
      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-times-circle"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <!-- Personal Info -->
        <div class="card" style="margin-bottom:1.25rem">
          <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
            <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-user"></i> Personal Information</h3>
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input class="form-control" type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Age</label>
                <input class="form-control" type="number" name="age" min="12" max="120" value="<?= htmlspecialchars($user['age']??'') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Gender</label>
                <select class="form-control" name="gender">
                  <?php foreach (['Male','Female','Other'] as $g): ?>
                    <option value="<?=$g?>" <?= $user['gender']===$g?'selected':'' ?>><?=$g?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Weight (kg)</label>
                <input class="form-control" type="number" name="weight_kg" step="0.1" min="20" max="300" value="<?= htmlspecialchars($user['weight_kg']??'') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Height (cm)</label>
                <input class="form-control" type="number" name="height_cm" step="0.1" min="50" max="250" value="<?= htmlspecialchars($user['height_cm']??'') ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- Goals & Lifestyle -->
        <div class="card" style="margin-bottom:1.25rem">
          <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
            <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-bullseye"></i> Goals & Lifestyle</h3>
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
              <div class="form-group">
                <label class="form-label">Health Goal</label>
                <select class="form-control" name="health_goal">
                  <?php foreach (['Lose Weight','Maintain Weight','Gain Weight','Build Muscle'] as $g): ?>
                    <option value="<?=$g?>" <?= $user['health_goal']===$g?'selected':'' ?>><?=$g?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Dietary Preference</label>
                <select class="form-control" name="dietary_preference">
                  <?php foreach (['None','Vegetarian','Vegan','Gluten-Free','Keto','Paleo'] as $d): ?>
                    <option value="<?=$d?>" <?= $user['dietary_preference']===$d?'selected':'' ?>><?=$d?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Activity Level</label>
                <select class="form-control" name="activity_level">
                  <?php foreach (['Sedentary','Lightly Active','Moderately Active','Very Active','Extra Active'] as $a): ?>
                    <option value="<?=$a?>" <?= $user['activity_level']===$a?'selected':'' ?>><?=$a?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Change Password -->
        <div class="card" style="margin-bottom:1.25rem">
          <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
            <h3 style="font-size:1rem;font-weight:700;color:var(--primary-dark)"><i class="fas fa-lock"></i> Change Password <span style="font-weight:400;color:var(--text-muted);font-size:.8rem">(leave blank to keep current)</span></h3>
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
              <div class="form-group">
                <label class="form-label">Current Password</label>
                <input class="form-control" type="password" name="current_password" placeholder="Current password" autocomplete="current-password">
              </div>
              <div class="form-group">
                <label class="form-label">New Password</label>
                <input class="form-control" type="password" name="new_password" placeholder="New password" autocomplete="new-password">
              </div>
              <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input class="form-control" type="password" name="confirm_password" placeholder="Repeat new password" autocomplete="new-password">
              </div>
            </div>
          </div>
        </div>

        <div style="display:flex;gap:1rem">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Changes</button>
          <a href="dashboard.php" class="btn btn-outline btn-lg">Cancel</a>
        </div>
      </form>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
