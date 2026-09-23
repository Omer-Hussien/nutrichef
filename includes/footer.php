<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
// includes/footer.php ?>

<!-- ===== FOOTER ===== -->
<footer>
  <div class="container">
    <div class="footer-grid">

      <div class="footer-brand">
        <h3><i class="fas fa-leaf"></i> <div>Nutri<span style="color:#FFA726;">Chef</span></div></h3>
        <p>Your smart companion for personalised recipe recommendations and in-depth nutrition analysis. Eat well, live better.</p>
        <div style="display:flex;gap:.75rem;margin-top:1rem;">
          <a href="#" style="color:rgba(255,255,255,.7);font-size:1.1rem"><i class="fab fa-facebook-f"></i></a>
          <a href="#" style="color:rgba(255,255,255,.7);font-size:1.1rem"><i class="fab fa-instagram"></i></a>
          <a href="#" style="color:rgba(255,255,255,.7);font-size:1.1rem"><i class="fab fa-twitter"></i></a>
          <a href="#" style="color:rgba(255,255,255,.7);font-size:1.1rem"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/pages/recipes.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>All Recipes</a></li>
          <li><a href="<?= SITE_URL ?>/pages/categories.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Categories</a></li>
          <li><a href="<?= SITE_URL ?>/pages/meal_plan.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Meal Planner</a></li>
          <li><a href="<?= SITE_URL ?>/pages/nutrition_calculator.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Nutrition Calculator</a></li>
          <li><a href="<?= SITE_URL ?>/pages/add_recipe.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Add Recipe</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Account</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/pages/login.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Log In</a></li>
          <li><a href="<?= SITE_URL ?>/pages/register.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Sign Up</a></li>
          <li><a href="<?= SITE_URL ?>/pages/profile.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>My Profile</a></li>
          <li><a href="<?= SITE_URL ?>/pages/dashboard.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Dashboard</a></li>
          <li><a href="<?= SITE_URL ?>/pages/saved_recipes.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Saved Recipes</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Info</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/pages/about.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>About NutriChef</a></li>
          <li><a href="<?= SITE_URL ?>/pages/ingredients.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Nutrition Database</a></li>
          <li><a href="<?= SITE_URL ?>/html/contact.html"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Contact & Support</a></li>
          <li><a href="<?= SITE_URL ?>/html/help.html"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Help & FAQ</a></li>
          <li><a href="<?= SITE_URL ?>/pages/about.php"><i class="fas fa-angle-right" style="margin-right:.35rem"></i>Privacy Policy</a></li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> NutriChef &mdash; Smart Recipe Recommendation &amp; Nutrition Analysis System &bull; Built with <i class="fas fa-heart" style="color:#EF5350"></i> for healthy living.</p>
    </div>
  </div>
</footer>

<!-- Toast Container -->
<div class="toast-container"></div>

<!-- Charts Engine -->
<script src="<?= SITE_URL ?>/js/charts.js"></script>
<!-- Main JS -->
<script src="<?= SITE_URL ?>/js/main.js"></script>
<?php if (isset($extraJS)) echo $extraJS; ?>

</body>
</html>
