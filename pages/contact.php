<?php
// ============================================================
// pages/contact.php — Contact & Support Page
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
startSession();
$pageTitle  = 'Contact Us';
$isLoggedIn = isLoggedIn();
$user       = $isLoggedIn ? getCurrentUser() : null;

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name']    ?? '');
    $email   = sanitize($_POST['email']   ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($message) < 20) {
        $error = 'Message must be at least 20 characters.';
    } else {
        // In a real deployment this would send email via mail() or SMTP
        // For this project we log to a contact_messages table or simply confirm
        $pdo = getDB();
        // Store in a simple log table if it exists, otherwise just confirm
        try {
            $pdo->prepare("CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100),
                email VARCHAR(100),
                subject VARCHAR(200),
                message TEXT,
                submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )")->execute();
            $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)")
                ->execute([$name, $email, $subject, $message]);
        } catch (Exception $e) { /* silent */ }
        $success = 'Thank you, ' . htmlspecialchars($name) . '! Your message has been received. We\'ll get back to you within 24 hours.';
    }
}

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Contact</span></nav>
    <h1><i class="fas fa-envelope"></i> Contact & Support</h1>
    <p>Have a question, suggestion or issue? We'd love to hear from you.</p>
  </div>
</div>

<div class="container" style="padding-bottom:3rem">
  <div style="display:grid;grid-template-columns:1fr 380px;gap:2rem;align-items:start">

    <!-- Contact Form -->
    <div class="card">
      <div style="padding:1.5rem;border-bottom:1px solid var(--border)">
        <h2 style="font-size:1.1rem;font-weight:700;color:var(--primary-dark)">
          <i class="fas fa-paper-plane" style="color:var(--primary)"></i> Send Us a Message
        </h2>
      </div>
      <div class="card-body">

        <?php if ($success): ?>
          <div class="alert alert-success" style="margin-bottom:1.25rem">
            <i class="fas fa-check-circle"></i><?= $success ?>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger" style="margin-bottom:1.25rem">
            <i class="fas fa-times-circle"></i><?= $error ?>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input class="form-control" type="text" name="name" required maxlength="100"
                     value="<?= htmlspecialchars($user['full_name'] ?? $_POST['name'] ?? '') ?>"
                     placeholder="Your full name">
            </div>
            <div class="form-group">
              <label class="form-label">Email Address *</label>
              <input class="form-control" type="email" name="email" required maxlength="100"
                     value="<?= htmlspecialchars($user['email'] ?? $_POST['email'] ?? '') ?>"
                     placeholder="your@email.com">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Subject</label>
            <select class="form-control" name="subject">
              <option value="General Enquiry">General Enquiry</option>
              <option value="Bug Report">Bug Report</option>
              <option value="Recipe Suggestion">Recipe Suggestion</option>
              <option value="Account Issue">Account Issue</option>
              <option value="Nutrition Query">Nutrition Query</option>
              <option value="Feature Request">Feature Request</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Message *</label>
            <textarea class="form-control" name="message" rows="6" required maxlength="2000"
                      placeholder="Please describe your question or issue in detail…"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            <span class="form-hint">0 / 2000 characters</span>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
            <i class="fas fa-paper-plane"></i> Send Message
          </button>
        </form>
      </div>
    </div>

    <!-- Info Sidebar -->
    <div>

      <!-- Contact Info -->
      <div class="card" style="margin-bottom:1rem">
        <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));
                    color:#fff;padding:1.5rem;border-radius:var(--radius) var(--radius) 0 0;text-align:center">
          <div style="font-size:2.5rem;margin-bottom:.5rem">🍃</div>
          <h3 style="font-size:1.1rem;font-weight:700">NutriChef Support</h3>
          <p style="opacity:.8;font-size:.85rem">Smart Recipe & Nutrition System</p>
        </div>
        <div class="card-body">
          <?php
            $contacts = [
              ['icon'=>'fa-envelope','label'=>'Email','val'=>'support@nutrichef.com','color'=>'#4CAF50'],
              ['icon'=>'fa-clock',   'label'=>'Response Time','val'=>'Within 24 hours','color'=>'#2196F3'],
              ['icon'=>'fa-globe',   'label'=>'Platform','val'=>'Web (All Browsers)','color'=>'#FF9800'],
              ['icon'=>'fa-server',  'label'=>'Hosting','val'=>'localhost / XAMPP','color'=>'#9C27B0'],
            ];
            foreach ($contacts as $c):
          ?>
            <div style="display:flex;align-items:center;gap:.85rem;padding:.65rem 0;border-bottom:1px solid var(--border)">
              <div style="width:36px;height:36px;border-radius:50%;background:<?= $c['color'] ?>22;
                          display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:.9rem"></i>
              </div>
              <div>
                <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px"><?= $c['label'] ?></div>
                <div style="font-weight:600;font-size:.875rem"><?= htmlspecialchars($c['val']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- FAQ -->
      <div class="card" style="margin-bottom:1rem">
        <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border)">
          <h3 style="font-size:.95rem;font-weight:700;color:var(--primary-dark)">
            <i class="fas fa-question-circle" style="color:var(--accent)"></i> Frequently Asked
          </h3>
        </div>
        <div class="card-body" style="padding:.75rem 1.5rem">
          <?php
            $faqs = [
              ['q'=>'How are calories calculated?','a'=>'Nutrition totals are auto-calculated from the per-100g data of each ingredient multiplied by the quantity used in the recipe.'],
              ['q'=>'Can I change my health goal?','a'=>'Yes — go to Profile and update your health goal at any time. Recommendations update immediately.'],
              ['q'=>'Is my data private?','a'=>'Absolutely. Passwords are bcrypt-hashed. Your profile data is never shared or sold.'],
              ['q'=>'How do I add a recipe?','a'=>'Click "Add Recipe" in the navigation. Select ingredients from the database and nutrition is calculated automatically.'],
              ['q'=>'Why are my recommendations empty?','a'=>'Complete your profile (weight, height, goal, dietary preference) for the best personalised suggestions.'],
            ];
            foreach ($faqs as $i => $faq):
          ?>
            <div style="border-bottom:1px solid var(--border);padding:.6rem 0" x-data="{open:false}">
              <button onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none';this.querySelector('i').className='fas '+(this.nextElementSibling.style.display==='block'?'fa-chevron-up':'fa-chevron-down')+' fa-fw'"
                      style="background:none;border:none;width:100%;text-align:left;font-family:var(--font);
                             font-size:.85rem;font-weight:600;color:var(--text);cursor:pointer;
                             display:flex;justify-content:space-between;align-items:center;padding:.25rem 0">
                <?= $faq['q'] ?>
                <i class="fas fa-chevron-down fa-fw" style="color:var(--primary);font-size:.8rem;flex-shrink:0"></i>
              </button>
              <div style="display:none;font-size:.82rem;color:var(--text-muted);line-height:1.6;padding-top:.4rem">
                <?= $faq['a'] ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="card">
        <div class="card-body">
          <h4 style="font-size:.875rem;font-weight:700;color:var(--primary-dark);margin-bottom:.85rem">
            <i class="fas fa-link" style="color:var(--primary)"></i> Helpful Links
          </h4>
          <div style="display:flex;flex-direction:column;gap:.4rem">
            <a href="recipes.php" class="btn btn-outline btn-sm" style="justify-content:flex-start"><i class="fas fa-utensils"></i> Browse All Recipes</a>
            <a href="nutrition_calculator.php" class="btn btn-outline btn-sm" style="justify-content:flex-start"><i class="fas fa-calculator"></i> Nutrition Calculator</a>
            <a href="about.php" class="btn btn-outline btn-sm" style="justify-content:flex-start"><i class="fas fa-info-circle"></i> About NutriChef</a>
            <?php if (!$isLoggedIn): ?>
              <a href="register.php" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Create Free Account</a>
            <?php else: ?>
              <a href="dashboard.php" class="btn btn-primary btn-sm"><i class="fas fa-chart-pie"></i> My Dashboard</a>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
