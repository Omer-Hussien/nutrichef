<?php
// ============================================================
// pages/login.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

startSession();
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    }
    $error = $result['message'];
}
$pageTitle = 'Log In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Log In | NutriChef</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-card-header">
      <a href="<?= SITE_URL ?>/index.php" style="text-decoration:none; color:inherit; display:block;">
      <div class="logo"><i class="fas fa-leaf" style="color:var(--secondary-light)"></i></div>
      </a>
      <h2>Welcome Back!</h2>
      <p>Log in to your NutriChef account</p>
    </div>
    <div class="auth-card-body">

      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-times-circle"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="form-group">
          <label class="form-label" for="username"><i class="fas fa-user"></i> Username or Email</label>
          <input class="form-control" type="text" id="username" name="username" required
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Enter your username or email" autocomplete="username">
        </div>
        <div class="form-group">
          <label class="form-label" for="password"><i class="fas fa-lock"></i> Password</label>
          <div style="position:relative">
            <input class="form-control" type="password" id="password" name="password" required
                   placeholder="Enter your password" autocomplete="current-password" style="padding-right:2.8rem">
            <button type="button" onclick="togglePwd()" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                    background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.95rem" id="eyeBtn">
              <i class="far fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-bottom:1.25rem">
          <a href="#" style="font-size:.825rem;color:var(--primary)">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="height:46px">
          <i class="fas fa-sign-in-alt"></i> Log In
        </button>
      </form>

      <div style="text-align:center;margin-top:1.5rem;font-size:.875rem;color:var(--text-muted)">
        Don't have an account? <a href="register.php" style="font-weight:600">Sign Up Free</a>
      </div>

      <!-- Demo Credentials -->
      <div style="margin-top:1.25rem;padding:1rem;background:var(--bg);border-radius:var(--radius-sm);font-size:.8rem;color:var(--text-muted)">
        <strong style="color:var(--primary-dark)"><i class="fas fa-info-circle"></i> Demo Account</strong><br>
        Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>password</code>
      </div>
    </div>
  </div>
</div>
<script src="<?= SITE_URL ?>/js/main.js"></script>
<script>
function togglePwd() {
  const pwd = document.getElementById('password');
  const ico = document.getElementById('eyeIcon');
  if (pwd.type === 'password') { pwd.type = 'text'; ico.className = 'far fa-eye-slash'; }
  else { pwd.type = 'password'; ico.className = 'far fa-eye'; }
}
</script>
</body></html>
