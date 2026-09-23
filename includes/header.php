<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ============================================================
// Reusable HTML Header / Navbar
// ============================================================
require_once __DIR__ . '/auth.php';
startSession();
$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
$initials = $currentUser ? strtoupper(substr($currentUser['full_name'] ?? $currentUser['username'] ?? 'U', 0, 1)) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="NutriChef – Smart Recipe Recommendations & Nutrition Analysis" />
  <title><?= $pageTitle ?? 'NutriChef' ?> | NutriChef</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css" />

  <?php if (isset($extraCSS)) echo $extraCSS; ?>
  
<style>
    /* 1. Expand the container and FORCE it to stretch full height */
    .navbar .container {
        max-width: 1500px !important; 
        width: 100% !important;
        height: 100% !important; 
        padding: 0 1.5rem !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }
    
    /* 2. Slide menu to the left and lock it to one line */
    .nav-links {
        margin-left: 2rem !important;
        margin-right: auto !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 0.5rem !important; /* Slightly tighter gap to allow for larger text */
        list-style: none !important;
    }
    
    /* 3. Original Text Size restored */
    .nav-links a {
        white-space: nowrap !important;
        font-size: 0.95rem !important; /* RESTORED ALMOST ORIGINAL SIZE */
        font-weight: 500 !important;   /* Makes the text crisp and readable */
        padding: 0.4rem 0.5rem !important; 
        display: flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
    }
    
    /* Mobile hamburger handling */
    @media (max-width: 992px) {
        .nav-links {
            display: none !important;
        }
        .nav-links.open {
            display: flex !important;
            flex-direction: column !important;
            position: absolute;
            top: 70px; left: 0; right: 0;
            background: var(--primary-dark);
            margin: 0 !important; padding: 1rem !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .hamburger { display: block !important; }
    }
  </style>
</head>
<body>

<header class="navbar" style="position: sticky; top: 0; z-index: 1000; background: var(--primary-dark); box-shadow: var(--shadow); height: 70px;">
  <div class="container">
    
    <a href="<?= SITE_URL ?>/index.php" style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none; font-size: 1.25rem; font-weight: 800; color: #fff; flex-shrink: 0;">
      <i class="fas fa-leaf" style="color: var(--secondary-light);"></i> NutriChef
    </a>

    <ul class="nav-links">
      <li><a href="<?= SITE_URL ?>/index.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="<?= SITE_URL ?>/pages/recipes.php"><i class="fas fa-utensils"></i> Recipes</a></li>
      <li><a href="<?= SITE_URL ?>/pages/categories.php"><i class="fas fa-th-large"></i> Categories</a></li>
      <?php if ($isLoggedIn): ?>
        <li><a href="<?= SITE_URL ?>/pages/meal_plan.php"><i class="fas fa-calendar-alt"></i> Meal Plan</a></li>
        <li><a href="<?= SITE_URL ?>/pages/dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
      <?php endif; ?>
      <li><a href="<?= SITE_URL ?>/pages/nutrition_calculator.php"><i class="fas fa-calculator"></i> Nutrition</a></li>
      <li><a href="<?= SITE_URL ?>/pages/ingredients.php"><i class="fas fa-database"></i> Ingredients</a></li>
      <li><a href="<?= SITE_URL ?>/pages/about.php"><i class="fas fa-info-circle"></i> About</a></li>
    </ul>

    <div style="display: flex; align-items: center; gap: 0.6rem; flex-shrink: 0;">
      <?php if ($isLoggedIn): ?>
        <?php if (($currentUser['username'] ?? '') === 'admin' || ($currentUser['user_id'] ?? 0) === 1): ?>
          <a href="<?= SITE_URL ?>/pages/admin.php" class="btn btn-sm" style="border-radius:50px; background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.35); white-space:nowrap;">
            <i class="fas fa-shield-alt"></i> Admin
          </a>
        <?php endif; ?>
        
        <a href="<?= SITE_URL ?>/pages/add_recipe.php" class="btn btn-secondary btn-sm" style="border-radius:50px; white-space:nowrap;">
          <i class="fas fa-plus"></i> Add Recipe
        </a>
        
        <a href="<?= SITE_URL ?>/pages/profile.php" class="nav-avatar" title="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>" style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#2E7D32,#FF6F00); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:.9rem; text-decoration:none; flex-shrink:0;">
          <?= $initials ?>
        </a>
        
        <a href="<?= SITE_URL ?>/php/logout.php" class="btn btn-outline btn-sm" style="color:#fff; border-color:rgba(255,255,255,.5); flex-shrink:0; padding: 0.4rem 0.6rem;" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/pages/login.php" style="color:#fff; font-weight:600; text-decoration:none; margin-right:.5rem; font-size:.9rem; white-space:nowrap;">Log In</a>
        <a href="<?= SITE_URL ?>/pages/register.php" class="btn btn-primary btn-sm" style="border-radius:50px; white-space:nowrap;">Sign Up Free</a>
      <?php endif; ?>
      
      <button class="hamburger" style="display:none; background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer; margin-left:0.5rem; flex-shrink: 0;">
        <i class="fas fa-bars"></i>
      </button>
    </div>

  </div>
</header>