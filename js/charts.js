/* ============================================================
   NutriChef — Charts & Data Visualisation Engine
   Pure Canvas API — no external chart library required
   ============================================================ */

'use strict';

/* ─── Shared Utilities ───────────────────────────────────────── */
const NC_COLORS = {
  protein : ['#1565C0', '#42A5F5'],
  carbs   : ['#E65100', '#FFA726'],
  fat     : ['#AD1457', '#F48FB1'],
  fiber   : ['#2E7D32', '#81C784'],
  calories: ['#FF6F00', '#FFC107'],
  primary : '#2E7D32',
  accent  : '#00838F',
  muted   : '#BDBDBD',
};

function getPixelRatio(ctx) {
  return window.devicePixelRatio || 1;
}

function scaleCanvas(canvas) {
  const ratio  = getPixelRatio();
  const w = canvas.offsetWidth  || canvas.width;
  const h = canvas.offsetHeight || canvas.height;
  canvas.width  = w * ratio;
  canvas.height = h * ratio;
  canvas.style.width  = w + 'px';
  canvas.style.height = h + 'px';
  canvas.getContext('2d').scale(ratio, ratio);
  return { w, h };
}

/* ─── 1. MACRO DONUT CHART ───────────────────────────────────── */
function drawDonutChart(canvasId, data, centerLabel = '') {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const { w, h } = scaleCanvas(canvas);
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, w * 2, h * 2);

  const total = data.reduce((s, d) => s + Math.max(0, d.value), 0);
  if (!total) {
    ctx.beginPath();
    ctx.arc(w / 2, h / 2, Math.min(w, h) / 2 - 10, 0, Math.PI * 2);
    ctx.strokeStyle = NC_COLORS.muted;
    ctx.lineWidth = 14;
    ctx.stroke();
    ctx.fillStyle = '#757575';
    ctx.font = `bold 11px Poppins, Arial`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('No data', w / 2, h / 2);
    return;
  }

  const cx = w / 2, cy = h / 2;
  const radius     = Math.min(w, h) / 2 - 8;
  const innerRadius= radius * 0.56;
  let startAngle   = -Math.PI / 2;
  const gap        = 0.04;

  data.forEach((item, i) => {
    if (item.value <= 0) return;
    const slice    = (item.value / total) * (2 * Math.PI - gap * data.length);
    const endAngle = startAngle + slice;
    const mid      = startAngle + slice / 2;

    ctx.save();
    ctx.shadowColor = 'rgba(0,0,0,.12)';
    ctx.shadowBlur  = 6;

    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, radius, startAngle, endAngle);
    ctx.closePath();

    const grd = ctx.createLinearGradient(
      cx + Math.cos(mid) * innerRadius, cy + Math.sin(mid) * innerRadius,
      cx + Math.cos(mid) * radius,      cy + Math.sin(mid) * radius
    );
    const colors = Array.isArray(item.color) ? item.color : [item.color, item.color];
    grd.addColorStop(0, colors[0]);
    grd.addColorStop(1, colors[1] || colors[0]);
    ctx.fillStyle = grd;
    ctx.fill();
    ctx.restore();

    startAngle = endAngle + gap;
  });

  ctx.beginPath();
  ctx.arc(cx, cy, innerRadius, 0, 2 * Math.PI);
  ctx.fillStyle = '#fff';
  ctx.fill();

  if (centerLabel) {
    const [top, bot] = centerLabel.split('\n');
    ctx.fillStyle = '#212121';
    ctx.font = `bold 14px Poppins, Arial`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(top, cx, cy - (bot ? 9 : 0));
    if (bot) {
      ctx.font = `10px Poppins, Arial`;
      ctx.fillStyle = '#757575';
      ctx.fillText(bot, cx, cy + 10);
    }
  }
}

/* ─── 2. PROGRESS RING ───────────────────────────────────────── */
function drawProgressRing(canvasId, percent, color = '#4CAF50', label = '') {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const { w, h } = scaleCanvas(canvas);
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, w * 2, h * 2);

  const cx = w / 2, cy = h / 2;
  const r  = Math.min(w, h) / 2 - 10;
  const pct = Math.min(100, Math.max(0, percent));

  ctx.beginPath();
  ctx.arc(cx, cy, r, 0, 2 * Math.PI);
  ctx.strokeStyle = '#E0E0E0';
  ctx.lineWidth   = 10;
  ctx.lineCap     = 'round';
  ctx.stroke();

  const endAngle = -Math.PI / 2 + (pct / 100) * 2 * Math.PI;
  ctx.beginPath();
  ctx.arc(cx, cy, r, -Math.PI / 2, endAngle);
  ctx.strokeStyle = color;
  ctx.lineWidth   = 10;
  ctx.lineCap     = 'round';
  ctx.stroke();

  ctx.fillStyle = '#212121';
  ctx.font = `bold ${r > 45 ? 14 : 11}px Poppins, Arial`;
  ctx.textAlign    = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText(`${Math.round(pct)}%`, cx, cy - (label ? 8 : 0));

  if (label) {
    ctx.font = `10px Poppins, Arial`;
    ctx.fillStyle = '#757575';
    ctx.fillText(label, cx, cy + 10);
  }
}

/* ─── 3. ANIMATED PROGRESS RING ─────────────────────────────── */
function animateProgressRing(canvasId, targetPct, color = '#4CAF50', label = '') {
  let current = 0;
  const step = targetPct / 40;
  function frame() {
    current = Math.min(current + step, targetPct);
    drawProgressRing(canvasId, current, color, label);
    if (current < targetPct) requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
}

/* ─── 4. HORIZONTAL NUTRITION BAR ───────────────────────────── */
function drawNutritionBar(canvasId, value, max, color = '#4CAF50', label = '') {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const { w, h } = scaleCanvas(canvas);
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, w * 2, h * 2);

  const pct       = max > 0 ? Math.min(1, value / max) : 0;
  const trackH    = 10;
  const trackY    = h / 2 - trackH / 2;
  const labelW    = label ? 80 : 0;
  const barX      = labelW + 4;
  const barW      = w - labelW - 50;

  if (label) {
    ctx.fillStyle = '#757575';
    ctx.font = `11px Poppins, Arial`;
    ctx.textAlign    = 'left';
    ctx.textBaseline = 'middle';
    ctx.fillText(label, 0, h / 2);
  }

  ctx.beginPath();
  ctx.roundRect(barX, trackY, barW, trackH, 5);
  ctx.fillStyle = '#E0E0E0';
  ctx.fill();

  if (pct > 0) {
    const grd = ctx.createLinearGradient(barX, 0, barX + barW * pct, 0);
    grd.addColorStop(0, color);
    grd.addColorStop(1, color + 'aa');
    ctx.beginPath();
    ctx.roundRect(barX, trackY, barW * pct, trackH, 5);
    ctx.fillStyle = grd;
    ctx.fill();
  }

  ctx.fillStyle = '#212121';
  ctx.font = `bold 11px Poppins, Arial`;
  ctx.textAlign    = 'right';
  ctx.textBaseline = 'middle';
  ctx.fillText(Math.round(value), w, h / 2);
}

/* ─── 5. WEEKLY CALORIE BAR CHART ───────────────────────────── */
function drawWeeklyChart(canvasId, days) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const { w, h } = scaleCanvas(canvas);
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, w * 2, h * 2);

  if (!days || !days.length) return;

  const padLeft = 45, padRight = 15, padTop = 20, padBottom = 40;
  const chartW = w - padLeft - padRight;
  const chartH = h - padTop - padBottom;
  const maxVal = Math.max(...days.map(d => Math.max(d.calories, d.target || 0)), 500);
  const barW   = (chartW / days.length) * 0.55;
  const gap    = chartW / days.length;

  ctx.strokeStyle = '#F0F0F0';
  ctx.lineWidth   = 1;
  for (let i = 0; i <= 4; i++) {
    const y = padTop + chartH - (i / 4) * chartH;
    ctx.beginPath();
    ctx.moveTo(padLeft, y);
    ctx.lineTo(w - padRight, y);
    ctx.stroke();
    ctx.fillStyle = '#9E9E9E';
    ctx.font = '9px Poppins, Arial';
    ctx.textAlign    = 'right';
    ctx.textBaseline = 'middle';
    ctx.fillText(Math.round((i / 4) * maxVal), padLeft - 5, y);
  }

  days.forEach((d, i) => {
    const x       = padLeft + i * gap + gap / 2 - barW / 2;
    const barH    = chartH * (d.calories / maxVal);
    const barY    = padTop + chartH - barH;
    const isToday = d.today;

    const grd = ctx.createLinearGradient(x, barY, x, padTop + chartH);
    grd.addColorStop(0, isToday ? '#2E7D32' : '#4CAF50');
    grd.addColorStop(1, isToday ? '#1B5E20' : '#81C784');

    ctx.save();
    ctx.shadowColor  = 'rgba(0,0,0,.1)';
    ctx.shadowBlur   = 4;
    ctx.shadowOffsetY = 2;
    ctx.beginPath();
    if (ctx.roundRect) {
      ctx.roundRect(x, barY, barW, barH, [4, 4, 0, 0]);
    } else {
      ctx.rect(x, barY, barW, barH);
    }
    ctx.fillStyle = grd;
    ctx.fill();
    ctx.restore();

    if (d.target) {
      const ty = padTop + chartH - chartH * (d.target / maxVal);
      ctx.beginPath();
      ctx.setLineDash([3, 3]);
      ctx.moveTo(x - 4, ty);
      ctx.lineTo(x + barW + 4, ty);
      ctx.strokeStyle = '#FF6F00';
      ctx.lineWidth   = 1.5;
      ctx.stroke();
      ctx.setLineDash([]);
    }

    ctx.fillStyle = isToday ? NC_COLORS.primary : '#757575';
    ctx.font = `${isToday ? 'bold ' : ''}10px Poppins, Arial`;
    ctx.textAlign    = 'center';
    ctx.textBaseline = 'top';
    ctx.fillText(d.label, x + barW / 2, padTop + chartH + 6);

    if (d.calories > 0) {
      ctx.fillStyle = '#212121';
      ctx.font = '8px Poppins, Arial';
      ctx.textAlign    = 'center';
      ctx.textBaseline = 'bottom';
      ctx.fillText(d.calories, x + barW / 2, barY - 2);
    }
  });

  const legY = padTop + chartH + 26;
  ctx.fillStyle = NC_COLORS.primary;
  ctx.fillRect(padLeft, legY, 10, 8);
  ctx.fillStyle = '#757575';
  ctx.font = '9px Poppins, Arial';
  ctx.textAlign    = 'left';
  ctx.textBaseline = 'middle';
  ctx.fillText('Calories consumed', padLeft + 14, legY + 4);

  ctx.setLineDash([3, 3]);
  ctx.beginPath();
  ctx.moveTo(padLeft + 130, legY + 4);
  ctx.lineTo(padLeft + 145, legY + 4);
  ctx.strokeStyle = '#FF6F00';
  ctx.lineWidth   = 1.5;
  ctx.stroke();
  ctx.setLineDash([]);
  ctx.fillText('Daily target', padLeft + 148, legY + 4);
}

/* ─── 6. MACRO PIE MINI (for recipe cards) ───────────────────── */
function drawMiniPie(canvasId, protein, carbs, fat) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const { w, h } = scaleCanvas(canvas);
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, w * 2, h * 2);

  const total = protein * 4 + carbs * 4 + fat * 9;
  if (!total) return;

  const cx = w / 2, cy = h / 2, r = Math.min(w, h) / 2 - 2;
  const slices = [
    { value: protein * 4, color: NC_COLORS.protein[0] },
    { value: carbs   * 4, color: NC_COLORS.carbs[0]   },
    { value: fat     * 9, color: NC_COLORS.fat[0]      },
  ];

  let angle = -Math.PI / 2;
  slices.forEach(s => {
    const sweep = (s.value / total) * 2 * Math.PI;
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, r, angle, angle + sweep);
    ctx.closePath();
    ctx.fillStyle = s.color;
    ctx.fill();
    angle += sweep;
  });

  ctx.beginPath();
  ctx.arc(cx, cy, r * 0.5, 0, 2 * Math.PI);
  ctx.fillStyle = '#fff';
  ctx.fill();
}

/* ─── 7. BMI GAUGE (FIXED ZONES & MATH) ────────────────────── */
function drawBMIGauge(canvasId, bmi) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const { w, h } = scaleCanvas(canvas);
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, w * 2, h * 2);

  const cx     = w / 2;
  const cy     = h * 0.82;
  const r      = Math.min(w * 0.36, h * 0.48);
  const start  = Math.PI;
  const end    = 2 * Math.PI;
  const span   = end - start;

  const maxBMI = 40;
  
  // FIXED: Boundaries now perfectly match PHP's calculateBMI() engine!
  const zones = [
    { color: '#2196F3', from: 0,           to: 18.5 / maxBMI }, // Underweight
    { color: '#4CAF50', from: 18.5/maxBMI, to: 25.0 / maxBMI }, // Normal
    { color: '#FF9800', from: 25.0/maxBMI, to: 30.0 / maxBMI }, // Overweight
    { color: '#F44336', from: 30.0/maxBMI, to: 1.0           }, // Obese
  ];

  zones.forEach(z => {
    ctx.beginPath();
    ctx.arc(cx, cy, r, start + z.from * span, start + z.to * span);
    ctx.strokeStyle = z.color;
    ctx.lineWidth   = 14;
    ctx.lineCap     = 'butt';
    ctx.stroke();
  });

  const clampedBMI = Math.min(maxBMI, Math.max(0, bmi));
  const fraction   = clampedBMI / maxBMI;
  const needleAngle= Math.PI + fraction * span;
  const nx = cx + Math.cos(needleAngle) * (r - 6);
  const ny = cy + Math.sin(needleAngle) * (r - 6);

  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.lineTo(nx, ny);
  ctx.strokeStyle = '#212121';
  ctx.lineWidth   = 3;
  ctx.lineCap     = 'round';
  ctx.stroke();

  ctx.beginPath();
  ctx.arc(cx, cy, 5, 0, 2 * Math.PI);
  ctx.fillStyle = '#212121';
  ctx.fill();

  ctx.fillStyle = '#212121';
  ctx.font = `bold ${r > 50 ? 18 : 14}px Poppins, Arial`;
  ctx.textAlign    = 'center';
  ctx.textBaseline = 'top';
  ctx.fillText(bmi.toFixed(1), cx, cy + 6);

  // FIXED: Text labels now sit perfectly centered inside the new color zones
  const labelData = [
    { text: 'Under',  frac: 0.23 },
    { text: 'Normal', frac: 0.54 },
    { text: 'Over',   frac: 0.69 },
    { text: 'Obese',  frac: 0.88 },
  ];
  
  ctx.font = '9px Poppins, Arial';
  ctx.fillStyle = '#757575';
  labelData.forEach(l => {
    const a  = Math.PI + l.frac * span;
    const lx = cx + Math.cos(a) * (r + 14);
    const ly = cy + Math.sin(a) * (r + 14);
    ctx.textAlign    = lx < cx ? 'right' : lx > cx + 5 ? 'left' : 'center';
    ctx.textBaseline = ly < cy ? 'bottom' : 'top';
    ctx.fillText(l.text, lx, ly);
  });
}

/* ─── 8. CALORIE TREND SPARKLINE ────────────────────────────── */
function drawSparkline(canvasId, values, color = '#4CAF50') {
  const canvas = document.getElementById(canvasId);
  if (!canvas || values.length < 2) return;
  const { w, h } = scaleCanvas(canvas);
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, w * 2, h * 2);

  const max = Math.max(...values);
  const min = Math.min(...values);
  const range = max - min || 1;
  const pad = 4;
  const stepX = (w - pad * 2) / (values.length - 1);

  const pts = values.map((v, i) => ({
    x: pad + i * stepX,
    y: h - pad - ((v - min) / range) * (h - pad * 2),
  }));

  ctx.beginPath();
  ctx.moveTo(pts[0].x, h - pad);
  pts.forEach(p => ctx.lineTo(p.x, p.y));
  ctx.lineTo(pts[pts.length - 1].x, h - pad);
  ctx.closePath();
  const grd = ctx.createLinearGradient(0, 0, 0, h);
  grd.addColorStop(0, color + '55');
  grd.addColorStop(1, color + '00');
  ctx.fillStyle = grd;
  ctx.fill();

  ctx.beginPath();
  ctx.moveTo(pts[0].x, pts[0].y);
  pts.forEach(p => ctx.lineTo(p.x, p.y));
  ctx.strokeStyle = color;
  ctx.lineWidth   = 2;
  ctx.lineJoin    = 'round';
  ctx.lineCap     = 'round';
  ctx.stroke();

  const last = pts[pts.length - 1];
  ctx.beginPath();
  ctx.arc(last.x, last.y, 3, 0, 2 * Math.PI);
  ctx.fillStyle = color;
  ctx.fill();
}

/* ─── Auto-init on DOMContentLoaded ─────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-chart="donut"]').forEach(canvas => {
    try {
      const data = JSON.parse(canvas.dataset.values || '[]');
      const label= canvas.dataset.label || '';
      drawDonutChart(canvas.id, data, label);
    } catch(e) {}
  });

  document.querySelectorAll('[data-chart="ring"]').forEach(canvas => {
    const pct   = parseFloat(canvas.dataset.percent || '0');
    const color = canvas.dataset.color || '#4CAF50';
    const label = canvas.dataset.label || '';
    animateProgressRing(canvas.id, pct, color, label);
  });

  document.querySelectorAll('[data-chart="weekly"]').forEach(canvas => {
    try {
      const days = JSON.parse(canvas.dataset.days || '[]');
      drawWeeklyChart(canvas.id, days);
    } catch(e) {}
  });

  document.querySelectorAll('[data-chart="bmi"]').forEach(canvas => {
    const bmi = parseFloat(canvas.dataset.bmi || '22');
    drawBMIGauge(canvas.id, bmi);
  });

  document.querySelectorAll('[data-chart="sparkline"]').forEach(canvas => {
    try {
      const vals  = JSON.parse(canvas.dataset.values || '[]');
      const color = canvas.dataset.color || '#4CAF50';
      drawSparkline(canvas.id, vals, color);
    } catch(e) {}
  });
});