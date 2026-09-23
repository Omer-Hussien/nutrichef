/* ============================================================
   Smart Recipe Recommendation & Nutrition Analysis System
   Main JavaScript - NutriChef
   ============================================================ */

'use strict';

/* ---- Toast Notifications ---- */
function showToast(message, type = 'success', duration = 3500) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
  const toast = document.createElement('div');
  toast.className = `toast ${type !== 'success' ? type : ''}`;
  toast.innerHTML = `<i class="fas ${icons[type] || icons.success}" style="color:${type==='error'?'#C62828':type==='warning'?'#F9A825':'#2E7D32'}"></i><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slideOut .3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

/* ---- Mobile Hamburger ---- */
document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger');
  const navLinks  = document.querySelector('.nav-links');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => navLinks.classList.toggle('open'));
    document.addEventListener('click', e => {
      if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
        navLinks.classList.remove('open');
      }
    });
  }

  /* ---- Active Nav Link ---- */
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-links a').forEach(link => {
    if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').split('/').pop())) {
      link.classList.add('active');
    }
  });

  /* ---- Tabs ---- */
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('.tabs-wrapper') || btn.parentElement.parentElement;
      group.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      group.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const target = document.getElementById(btn.dataset.target);
      if (target) target.classList.add('active');
    });
  });

  /* ---- Save Recipe Buttons ---- */
  document.querySelectorAll('.recipe-save-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const recipeId = btn.dataset.recipeId;
      try {
        const res  = await fetch(`../php/toggle_save.php?recipe_id=${recipeId}`);
        const data = await res.json();
        if (data.error) { showToast(data.error, 'error'); return; }
        btn.classList.toggle('saved', data.saved);
        btn.querySelector('i').className = data.saved ? 'fas fa-heart' : 'far fa-heart';
        showToast(data.message, 'success');
      } catch { showToast('Could not save recipe. Please log in.', 'error'); }
    });
  });

  /* ---- Animate Nutrition Bars on Scroll ---- */
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.querySelectorAll('.nutrition-bar-fill').forEach(fill => {
          fill.style.width = fill.dataset.width || '0%';
        });
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });
  document.querySelectorAll('.nutrition-section').forEach(el => observer.observe(el));

  /* ---- Smooth Counters ---- */
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10);
    let current = 0;
    const step = Math.ceil(target / 60);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString();
      if (current >= target) clearInterval(timer);
    }, 20);
  });

  /* ---- Filter Form Auto-Submit ---- */
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    filterForm.querySelectorAll('select').forEach(sel => {
      sel.addEventListener('change', () => filterForm.submit());
    });
  }

  /* ---- Character Counter for Textareas ---- */
  document.querySelectorAll('textarea[maxlength]').forEach(ta => {
    const hint = ta.nextElementSibling;
    if (hint && hint.classList.contains('form-hint')) {
      ta.addEventListener('input', () => {
        hint.textContent = `${ta.value.length} / ${ta.maxLength} characters`;
      });
    }
  });

  /* ---- BMI Calculator (inline) ---- */
  const bmiForm = document.getElementById('bmiCalcForm');
  if (bmiForm) {
    bmiForm.addEventListener('submit', e => {
      e.preventDefault();
      const weightInput = document.getElementById('bmiWeight').value;
      const heightInput = document.getElementById('bmiHeight').value;
      const w = parseFloat(weightInput);
      const h = parseFloat(heightInput) / 100;
      const resultBox = document.getElementById('bmiResult');

      // --- NEW VALIDATION & RESET LOGIC ---
      if (!w || !h || isNaN(w) || isNaN(h) || w <= 0 || h <= 0) {
        // Wipes away old BMI results and displays an instant alert banner
        resultBox.innerHTML = `
          <div class="alert alert-danger" style="text-align:left;font-size:0.85rem;margin-top:0.5rem;line-height:1.5;animation:fadeIn .3s ease">
            <i class="fas fa-exclamation-circle"></i> <span>Please enter a valid Weight (kg) and Height (cm).</span>
          </div>
        `;
        showToast('Please enter both weight and height.', 'error');
        return;
      }

      const bmi = (w / (h * h)).toFixed(1);
      let cat, color, alertType, alertMsg, icon;

      if (bmi < 18.5) { 
        cat = 'Underweight'; 
        color = '#2196F3'; 
        alertType = 'alert-info'; 
        icon = 'fa-info-circle';
        alertMsg = 'You are currently below the standard weight range. Focus on nutrient-dense caloric intake.';
      } else if (bmi < 25) { 
        cat = 'Normal weight'; 
        color = '#4CAF50'; 
        alertType = 'alert-success'; 
        icon = 'fa-check-circle';
        alertMsg = 'Great job! You are within a healthy weight range for your height.';
      } else if (bmi < 30) { 
        cat = 'Overweight'; 
        color = '#FF9800'; 
        alertType = 'alert-warning'; 
        icon = 'fa-exclamation-triangle';
        alertMsg = 'You are slightly above the standard range. A moderate calorie deficit and exercise can help.';
      } else { 
        cat = 'Obese'; 
        color = '#F44336'; 
        alertType = 'alert-danger'; 
        icon = 'fa-exclamation-circle';
        alertMsg = 'Your BMI falls into the obese range. Consulting a healthcare provider is recommended.';
      }

      resultBox.innerHTML = `
        <div class="bmi-value" style="color:${color}">${bmi}</div>
        <span class="bmi-category" style="background:${color}22;color:${color};margin-bottom:1rem;display:inline-block">${cat}</span>
        <div class="alert ${alertType}" style="text-align:left;font-size:0.85rem;margin-top:0.5rem;line-height:1.5;animation:fadeIn .3s ease">
          <i class="fas ${icon}"></i> <span>${alertMsg}</span>
        </div>
      `;

      showToast(`BMI Calculated: ${bmi} (${cat})`, alertType === 'alert-success' ? 'success' : 'info');
    });
  }
});

/* ---- Nutrition Donut Chart ---- */
function drawDonutChart(canvasId, data) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const total = data.reduce((s, d) => s + d.value, 0);
  if (!total) return;
  let startAngle = -Math.PI / 2;
  const cx = canvas.width / 2, cy = canvas.height / 2, r = 60, rInner = 35;

  ctx.clearRect(0, 0, canvas.width, canvas.height);
  data.forEach(item => {
    const slice = (item.value / total) * 2 * Math.PI;
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, r, startAngle, startAngle + slice);
    ctx.closePath();
    ctx.fillStyle = item.color;
    ctx.fill();
    startAngle += slice;
  });
  // Donut hole
  ctx.beginPath();
  ctx.arc(cx, cy, rInner, 0, 2 * Math.PI);
  ctx.fillStyle = '#fff';
  ctx.fill();
  // Center label
  ctx.fillStyle = '#212121';
  ctx.font = 'bold 14px Poppins, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText(`${Math.round(total)} cal`, cx, cy);
}

/* ---- Recipe Search (live) ---- */
let searchTimeout;
const searchInput = document.getElementById('liveSearch');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = searchInput.value.trim();
    const resultsBox = document.getElementById('searchResults');
    if (!q) { if (resultsBox) resultsBox.innerHTML = ''; return; }
    searchTimeout = setTimeout(async () => {
      try {
        const res  = await fetch(`../php/search_ajax.php?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        if (!resultsBox) return;
        if (!data.length) { resultsBox.innerHTML = '<p class="text-muted" style="padding:.75rem 1rem">No recipes found.</p>'; return; }
        resultsBox.innerHTML = data.slice(0, 6).map(r =>
          `<a href="recipe_detail.php?id=${r.recipe_id}" class="search-result-item">
             <span>${r.title}</span>
             <small class="text-muted">${Math.round(r.total_calories)} kcal</small>
           </a>`
        ).join('');
      } catch { /* silent */ }
    }, 320);
  });
}

/* ---- Star Rating UI ---- */
document.querySelectorAll('.star-rating label').forEach(label => {
  label.addEventListener('mouseover', function () {
    const labels = [...this.parentElement.querySelectorAll('label')];
    const idx = labels.indexOf(this);
    labels.forEach((l, i) => l.style.color = i <= idx ? 'var(--warning)' : 'var(--border)');
  });
  label.addEventListener('mouseleave', function () {
    this.parentElement.querySelectorAll('label').forEach(l => l.style.color = '');
  });
});

/* ---- Calorie Progress Ring ---- */
function drawProgressRing(canvasId, percent, color = '#4CAF50') {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const cx = canvas.width / 2, cy = canvas.height / 2, r = 50;
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  // Track
  ctx.beginPath(); ctx.arc(cx, cy, r, 0, 2 * Math.PI);
  ctx.strokeStyle = '#E0E0E0'; ctx.lineWidth = 10; ctx.stroke();
  // Fill
  const angle = (percent / 100) * 2 * Math.PI - Math.PI / 2;
  ctx.beginPath(); ctx.arc(cx, cy, r, -Math.PI / 2, angle);
  ctx.strokeStyle = color; ctx.lineWidth = 10; ctx.lineCap = 'round'; ctx.stroke();
  // Text
  ctx.fillStyle = '#212121'; ctx.font = 'bold 14px Poppins, sans-serif';
  ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
  ctx.fillText(`${Math.min(100, Math.round(percent))}%`, cx, cy);
}

/* ---- Confirm Delete ---- */
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
  });
});

/* ---- Print Recipe ---- */
function printRecipe() { window.print(); }