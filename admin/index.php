<?php
/**
 * SIZSR Admin - Dashboard
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'dashboard';
$pageTitle = 'Dashboard';

// Stat cards
$stats = [
    'total_courses'    => admin_count("SELECT COUNT(*) FROM courses"),
    'active_courses'   => admin_count("SELECT COUNT(*) FROM courses WHERE status='published'"),
    'total_apps'       => admin_count("SELECT COUNT(*) FROM applications"),
    'pending_apps'     => admin_count("SELECT COUNT(*) FROM applications WHERE status='pending'"),
    'approved_apps'    => admin_count("SELECT COUNT(*) FROM applications WHERE status='approved'"),
    'rejected_apps'    => admin_count("SELECT COUNT(*) FROM applications WHERE status='rejected'"),
    'messages'         => admin_count("SELECT COUNT(*) FROM contacts WHERE status='new'"),
    'subscribers'      => admin_count("SELECT COUNT(*) FROM newsletter_subscribers WHERE status='subscribed'"),
];

// Monthly applications (last 6 months)
$monthly = db_all(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
     FROM applications
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
     GROUP BY ym ORDER BY ym ASC"
);
$monthsMap = [];
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-$i month"));
    $monthsMap[$key] = 0;
}
foreach ($monthly as $row) {
    if (isset($monthsMap[$row['ym']])) $monthsMap[$row['ym']] = (int)$row['total'];
}
$monthLabels = array_map(fn($k) => date('M', strtotime($k . '-01')), array_keys($monthsMap));
$monthValues = array_values($monthsMap);

// Category distribution
$catDist = db_all(
    "SELECT cat.name, COUNT(c.id) AS total
     FROM categories cat LEFT JOIN courses c ON c.category_id = cat.id AND c.status='published'
     GROUP BY cat.id HAVING total > 0 ORDER BY total DESC"
);

// Gender distribution
$genderDist = db_all("SELECT COALESCE(gender,'Unknown') AS gender, COUNT(*) AS total FROM applications GROUP BY gender");

// Recent activities + applications
$recentApps = db_all(
    "SELECT a.*, c.title AS course_title FROM applications a
     LEFT JOIN courses c ON c.id = a.course_id ORDER BY a.created_at DESC LIMIT 6"
);
$activities = db_all("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 8");

require __DIR__ . '/includes/header.php';

// label, value, accent (gradient), icon SVG path(s), link
$cards = [
    ['Total Courses', $stats['total_courses'], 'from-sky-500 to-blue-600', 'sky', '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>', 'courses.php'],
    ['Active Courses', $stats['active_courses'], 'from-emerald-500 to-teal-600', 'emerald', '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>', 'courses.php'],
    ['Total Applications', $stats['total_apps'], 'from-primary to-secondary', 'teal', '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8M16 17H8M10 9H8"/>', 'applications.php'],
    ['Pending', $stats['pending_apps'], 'from-amber-500 to-orange-600', 'amber', '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>', 'applications.php?status=pending'],
    ['Approved', $stats['approved_apps'], 'from-green-500 to-emerald-600', 'green', '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>', 'applications.php?status=approved'],
    ['Rejected', $stats['rejected_apps'], 'from-red-500 to-rose-600', 'rose', '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>', 'applications.php?status=rejected'],
    ['New Messages', $stats['messages'], 'from-indigo-500 to-violet-600', 'indigo', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/>', 'contacts.php'],
    ['Subscribers', $stats['subscribers'], 'from-fuchsia-500 to-pink-600', 'pink', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>', 'reports.php'],
];

$tints = [
    'sky'     => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
    'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    'teal'    => 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400',
    'amber'   => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    'green'   => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
    'rose'    => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
    'indigo'  => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    'pink'    => 'bg-pink-50 text-pink-600 dark:bg-pink-500/10 dark:text-pink-400',
];

$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
?>

<style>
  @keyframes statIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
  .stat-card { opacity: 0; animation: statIn .5s cubic-bezier(.2,.7,.2,1) forwards; }
  @keyframes panelIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
  .panel-in { opacity: 0; animation: panelIn .6s cubic-bezier(.2,.7,.2,1) forwards; }
  @media (prefers-reduced-motion: reduce) { .stat-card, .panel-in { opacity: 1; animation: none; } }
</style>

<!-- WELCOME HEADER -->
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
  <div>
    <p class="text-sm font-semibold text-primary dark:text-secondary"><?= e($greeting) ?>, <?= e(explode(' ', (string)($admin['name'] ?? 'Admin'))[0]) ?> 👋</p>
    <h2 class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">Dashboard Overview</h2>
    <p class="mt-1 text-sm text-slate-500"><?= date('l, F j, Y') ?></p>
  </div>
  <div class="flex items-center gap-2">
    <a href="<?= e(admin_url('course-form.php')) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold shadow-lg shadow-primary/20 hover:-translate-y-0.5 transition">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
      New Course
    </a>
    <a href="<?= e(admin_url('reports.php')) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800/60 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
      Reports
    </a>
  </div>
</div>

<!-- STAT CARDS -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
  <?php foreach ($cards as $i => [$label, $value, $grad, $tint, $iconPath, $link]): ?>
    <a href="<?= e(admin_url($link)) ?>" class="stat-card group relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300" style="animation-delay: <?= $i * 60 ?>ms">
      <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r <?= $grad ?> opacity-0 group-hover:opacity-100 transition-opacity"></span>
      <div class="flex items-center justify-between">
        <span class="grid place-items-center h-11 w-11 rounded-xl <?= $tints[$tint] ?> group-hover:scale-110 transition-transform">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><?= $iconPath ?></svg>
        </span>
        <svg class="h-4 w-4 text-slate-300 dark:text-slate-600 group-hover:text-primary dark:group-hover:text-secondary group-hover:translate-x-0.5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M7 7h10v10"/></svg>
      </div>
      <p class="mt-4 text-3xl font-extrabold text-slate-900 dark:text-white" data-count="<?= (int)$value ?>">0</p>
      <p class="text-sm text-slate-500"><?= e($label) ?></p>
    </a>
  <?php endforeach; ?>
</div>

<!-- CHARTS -->
<div class="grid lg:grid-cols-3 gap-4 mt-6">
  <div class="panel-in lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-900 dark:text-white">Monthly Applications</h3>
      <span class="text-xs font-semibold text-slate-400">Last 6 months</span>
    </div>
    <canvas id="monthlyChart" height="110"></canvas>
  </div>
  <div class="panel-in rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm" style="animation-delay:.08s">
    <h3 class="font-bold text-slate-900 dark:text-white mb-4">Gender Distribution</h3>
    <canvas id="genderChart" height="200"></canvas>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
  <div class="panel-in rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm">
    <h3 class="font-bold text-slate-900 dark:text-white mb-4">Courses by Category</h3>
    <canvas id="catChart" height="220"></canvas>
  </div>

  <!-- Recent applications -->
  <div class="panel-in lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm" style="animation-delay:.08s">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-900 dark:text-white">Recent Applications</h3>
      <a href="<?= e(admin_url('applications.php')) ?>" class="text-sm font-semibold text-primary dark:text-secondary">View all &rarr;</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-400 border-b border-slate-100 dark:border-white/5">
          <th class="pb-2 font-semibold">Applicant</th><th class="pb-2 font-semibold">Course</th><th class="pb-2 font-semibold">Status</th><th class="pb-2 font-semibold">Date</th>
        </tr></thead>
        <tbody>
          <?php foreach ($recentApps as $a): ?>
            <tr class="border-b border-slate-50 dark:border-white/5">
              <td class="py-3"><a href="<?= e(admin_url('application-view.php?id=' . $a['id'])) ?>" class="font-semibold text-slate-800 dark:text-slate-200 hover:text-primary"><?= e($a['full_name']) ?></a></td>
              <td class="py-3 text-slate-500"><?= e(excerpt((string)($a['course_title'] ?? '-'), 28)) ?></td>
              <td class="py-3"><span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= status_badge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
              <td class="py-3 text-slate-400"><?= e(time_ago($a['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$recentApps): ?><tr><td colspan="4" class="py-6 text-center text-slate-400">No applications yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Activity feed -->
<div class="panel-in rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm mt-4">
  <h3 class="font-bold text-slate-900 dark:text-white mb-4">Recent Activity</h3>
  <ul class="space-y-3">
    <?php foreach ($activities as $act): ?>
      <li class="flex items-start gap-3 text-sm">
        <span class="mt-1.5 h-2 w-2 rounded-full bg-primary shrink-0"></span>
        <div>
          <p class="text-slate-700 dark:text-slate-200"><span class="font-semibold"><?= e(str_replace('_',' ', $act['action'])) ?></span> &mdash; <?= e($act['description'] ?? '') ?></p>
          <p class="text-xs text-slate-400"><?= e(time_ago($act['created_at'])) ?></p>
        </div>
      </li>
    <?php endforeach; ?>
    <?php if (!$activities): ?><li class="text-slate-400 text-sm">No activity recorded yet.</li><?php endif; ?>
  </ul>
</div>

<?php
$monthLabelsJson = json_encode($monthLabels);
$monthValuesJson = json_encode($monthValues);
$catLabelsJson   = json_encode(array_column($catDist, 'name'));
$catValuesJson   = json_encode(array_map('intval', array_column($catDist, 'total')));
$genderLabelsJson= json_encode(array_column($genderDist, 'gender'));
$genderValuesJson= json_encode(array_map('intval', array_column($genderDist, 'total')));

$adminScripts = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  // Animated count-up for stat values
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.querySelectorAll('[data-count]').forEach((el) => {
    const target = parseInt(el.getAttribute('data-count'), 10) || 0;
    if (reduce || target === 0) { el.textContent = target.toLocaleString(); return; }
    const dur = 1100, start = performance.now();
    function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased).toLocaleString();
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  });

  const palette = ['#B11314','#DEAE1B','#F9CC21','#F8DF74','#10B981','#6366F1','#EC4899','#F59E0B'];
  const grid = getComputedStyle(document.documentElement).getPropertyValue('color');
  const isDark = document.documentElement.classList.contains('dark');
  const tick = isDark ? '#94a3b8' : '#64748b';

  new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: { labels: $monthLabelsJson, datasets: [{ label: 'Applications', data: $monthValuesJson,
      borderColor: '#B11314', backgroundColor: 'rgba(177,19,20,.12)', fill: true, tension: .4, pointRadius: 4, pointBackgroundColor: '#DEAE1B' }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: 'rgba(148,163,184,.15)' } }, x: { ticks: { color: tick }, grid: { display: false } } } }
  });

  const genderData = $genderValuesJson;
  if (genderData.reduce((a,b)=>a+b,0) > 0) {
    new Chart(document.getElementById('genderChart'), {
      type: 'doughnut',
      data: { labels: $genderLabelsJson, datasets: [{ data: genderData, backgroundColor: palette }] },
      options: { plugins: { legend: { position: 'bottom', labels: { color: tick } } }, cutout: '60%' }
    });
  }

  const catData = $catValuesJson;
  if (catData.reduce((a,b)=>a+b,0) > 0) {
    new Chart(document.getElementById('catChart'), {
      type: 'bar',
      data: { labels: $catLabelsJson, datasets: [{ data: catData, backgroundColor: '#DEAE1B', borderRadius: 6 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: 'rgba(148,163,184,.15)' } }, y: { ticks: { color: tick }, grid: { display: false } } } }
    });
  }
})();
</script>
HTML;

require __DIR__ . '/includes/footer.php';
