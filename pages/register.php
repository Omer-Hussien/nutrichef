<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
startSession();
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser($_POST);
    if ($result['success']) { $success = $result['message']; }
    else { $error = $result['message']; }
}
$pageTitle = 'Sign Up';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign Up | NutriChef</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
.auth-card { max-width: 560px; }
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width:480px){ .two-col { grid-template-columns:1fr; } }
.step-progress { display:flex; gap:.5rem; margin-bottom:1.5rem; }
.step-dot { flex:1; height:4px; border-radius:2px; background:var(--border); transition:background .3s; }
.step-dot.done { background:var(--primary); }
</style>
</head>
<body>
<div class="auth-page" style="align-items:flex-start;padding:2rem 1rem;">
  <div class="auth-card" style="margin:auto;">
    <div class="auth-card-header">
      <a href="<?= SITE_URL ?>/index.php" style="text-decoration:none; color:inherit; display:block;">
        <div class="logo" style="margin-bottom:0.25rem;">🥗</div>
        <h2 style="font-weight:800; letter-spacing:-0.5px;">Nutri<span style="color:#FFA726;">Chef</span></h2>
      </a>
      <p style="margin-top:0.5rem;">Join NutriChef for personalised recipe recommendations</p>
    </div>
    <div class="auth-card-body">

      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i><?= htmlspecialchars($success) ?> <a href="login.php">Log in now</a></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-times-circle"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="step-progress">
        <div class="step-dot done" id="dot1"></div>
        <div class="step-dot" id="dot2"></div>
        <div class="step-dot" id="dot3"></div>
      </div>

      <form method="POST" id="regForm" novalidate>

        <!-- Step 1: Account Info -->
        <div id="step1">
          <h4 style="font-size:.9rem;font-weight:700;color:var(--primary-dark);margin-bottom:1rem">
            <i class="fas fa-user-circle"></i> Account Information
          </h4>
          <div class="two-col">
            <div class="form-group">
              <label class="form-label">Username *</label>
              <input class="form-control" type="text" name="username" value="<?= htmlspecialchars($_POST['username']??'') ?>" placeholder="e.g. healthychef" required>
            </div>
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input class="form-control" type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name']??'') ?>" placeholder="Your full name" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>" placeholder="your@email.com" required>
          </div>
          <div class="two-col">
            <div class="form-group">
              <label class="form-label">Password *</label>
              <input class="form-control" type="password" name="password" placeholder="Min 8 chars, 1 uppercase, 1 number" required>
            </div>
            <div class="form-group">
              <label class="form-label">Confirm Password *</label>
              <input class="form-control" type="password" id="confPwd" placeholder="Repeat password" required>
            </div>
          </div>
          <button type="button" onclick="nextStep(1)" class="btn btn-primary btn-block">Next <i class="fas fa-arrow-right"></i></button>
        </div>

        <!-- Step 2: Physical Stats (Upgraded with Asterisks) -->
        <div id="step2" style="display:none">
          <h4 style="font-size:.9rem;font-weight:700;color:var(--primary-dark);margin-bottom:1rem">
            <i class="fas fa-heartbeat"></i> Physical Information
          </h4>
          <div class="two-col">
            <div class="form-group">
              <label class="form-label">Age *</label>
              <input class="form-control" type="number" name="age" min="12" max="120" value="<?= htmlspecialchars($_POST['age']??'') ?>" placeholder="e.g. 25" required>
            </div>
            <div class="form-group">
              <label class="form-label">Gender *</label>
              <select class="form-control" name="gender">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Prefer not to say</option>
              </select>
            </div>
          </div>
          <div class="two-col">
            <div class="form-group">
              <label class="form-label">Weight (kg) *</label>
              <input class="form-control" type="number" name="weight" step="0.1" min="20" max="300" value="<?= htmlspecialchars($_POST['weight']??'') ?>" placeholder="e.g. 70" required>
            </div>
            <div class="form-group">
              <label class="form-label">Height (cm) *</label>
              <input class="form-control" type="number" name="height" step="0.1" min="50" max="250" value="<?= htmlspecialchars($_POST['height']??'') ?>" placeholder="e.g. 170" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Activity Level</label>
            <select class="form-control" name="activity_level">
              <option value="Sedentary">Sedentary (desk job, little exercise)</option>
              <option value="Lightly Active">Lightly Active (1–3 days/week)</option>
              <option value="Moderately Active" selected>Moderately Active (3–5 days/week)</option>
              <option value="Very Active">Very Active (6–7 days/week)</option>
              <option value="Extra Active">Extra Active (athlete/physical job)</option>
            </select>
          </div>
          <div style="display:flex;gap:.75rem">
            <button type="button" onclick="prevStep(2)" class="btn btn-outline" style="flex:1"><i class="fas fa-arrow-left"></i> Back</button>
            <button type="button" onclick="nextStep(2)" class="btn btn-primary" style="flex:2">Next <i class="fas fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 3: Goals & Preferences -->
        <div id="step3" style="display:none">
          <h4 style="font-size:.9rem;font-weight:700;color:var(--primary-dark);margin-bottom:1rem">
            <i class="fas fa-bullseye"></i> Goals & Dietary Preferences
          </h4>
          <div class="form-group">
            <label class="form-label">Health Goal</label>
            <select class="form-control" name="health_goal">
              <option value="Lose Weight">Lose Weight</option>
              <option value="Maintain Weight" selected>Maintain Weight</option>
              <option value="Gain Weight">Gain Weight</option>
              <option value="Build Muscle">Build Muscle</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Dietary Preference</label>
            <select class="form-control" name="dietary_preference">
              <option value="None" selected>No Preference</option>
              <option value="Vegetarian">Vegetarian</option>
              <option value="Vegan">Vegan</option>
              <option value="Gluten-Free">Gluten-Free</option>
              <option value="Keto">Keto</option>
              <option value="Paleo">Paleo</option>
            </select>
          </div>
          <div style="display:flex;gap:.75rem;margin-top:1.25rem">
            <button type="button" onclick="prevStep(3)" class="btn btn-outline" style="flex:1"><i class="fas fa-arrow-left"></i> Back</button>
            <button type="submit" class="btn btn-primary" style="flex:2"><i class="fas fa-check-circle"></i> Create Account</button>
          </div>
        </div>

      </form>

      <div style="text-align:center;margin-top:1.25rem;font-size:.875rem;color:var(--text-muted)">
        Already have an account? <a href="login.php" style="font-weight:600">Log In</a>
      </div>
    </div>
  </div>
</div>

<script>
let currentStep = 1;

function nextStep(step) {
  const pwd  = document.querySelector('input[name="password"]');
  const conf = document.getElementById('confPwd');
  
  // --- STRICT STEP 1 VALIDATION ---
  if (step === 1) {
    const uname = document.querySelector('input[name="username"]').value.trim();
    const fname = document.querySelector('input[name="full_name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    
    if (!uname) return alert('Username is required.');
    if (!fname) return alert('Full Name is required.');
    if (!email) return alert('Email address is required.');
    if (pwd.value.length < 8) return alert('Password must be at least 8 characters.');
    if (pwd.value !== conf.value) return alert('Passwords do not match.');
  }

  // --- STRICT STEP 2 VALIDATION ---
  if (step === 2) {
    const age    = parseFloat(document.querySelector('input[name="age"]').value);
    const weight = parseFloat(document.querySelector('input[name="weight"]').value);
    const height = parseFloat(document.querySelector('input[name="height"]').value);
    
    if (!age || age < 12 || age > 120) return alert('Please enter a valid Age (between 12 and 120).');
    if (!weight || weight < 20 || weight > 300) return alert('Please enter a valid Weight in kg.');
    if (!height || height < 50 || height > 250) return alert('Please enter a valid Height in cm.');
  }

  document.getElementById('step'+step).style.display = 'none';
  currentStep = step + 1;
  document.getElementById('step'+currentStep).style.display = 'block';
  for (let i=1; i<=3; i++) {
    document.getElementById('dot'+i).classList.toggle('done', i < currentStep);
  }
}

function prevStep(step) {
  document.getElementById('step'+step).style.display = 'none';
  currentStep = step - 1;
  document.getElementById('step'+currentStep).style.display = 'block';
  for (let i=1; i<=3; i++) {
    document.getElementById('dot'+i).classList.toggle('done', i < currentStep);
  }
}
</script>
</body></html>