<?php
/**
 * SIZSR Admin - Reports
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'reports';
$pageTitle = 'Reports';

$totals = [
    'applications' => admin_count("SELECT COUNT(*) FROM applications"),
    'approved'     => admin_count("SELECT COUNT(*) FROM applications WHERE status='approved'"),
    'courses'      => admin_count("SELECT COUNT(*) FROM courses WHERE status='published'"),
    'subscribers'  => admin_count("SELECT COUNT(*) FROM newsletter_subscribers WHERE status='subscribed'"),
];

// Applications per course
$byCourse = db_all(
    "SELECT c.title, COUNT(a.id) AS total,
            SUM(a.status='approved') AS approved,
            SUM(a.status='pending') AS pending,
            SUM(a.status='rejected') AS rejected
     FROM courses c LEFT JOIN applications a ON a.course_id=c.id
     GROUP BY c.id HAVING total > 0 ORDER BY total DESC"
);

// Applications by status
$byStatus = db_all("SELECT status, COUNT(*) AS total FROM applications GROUP BY status");

require __DIR__ . '/includes/header.php';
?>

<!-- Export buttons -->
<div class="flex flex-wrap items-center gap-2 mb-6">
  <span class="text-sm font-semibold text-slate-500 mr-2">Export:</span>
  <a href="<?= e(admin_url('export.php?type=applications&format=csv')) ?>" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5">Applications CSV</a>
  <a href="<?= e(admin_url('export.php?type=applications&format=excel')) ?>" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5">Applications Excel</a>
  <a href="<?= e(admin_url('export.php?type=courses&format=csv')) ?>" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5">Courses CSV</a>
  <a href="<?= e(admin_url('export.php?type=students&format=csv')) ?>" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5">Students CSV</a>
  <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-800 dark:bg-white/10 text-white text-sm font-semibold no-print">Print Report (PDF)</button>
</div>

<div id="printArea">
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?php foreach ([['Total Applications',$totals['applications']],['Approved',$totals['approved']],['Published Courses',$totals['courses']],['Subscribers',$totals['subscribers']]] as [$label,$val]): ?>
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm">
        <p class="text-3xl font-extrabold text-slate-900 dark:text-white"><?= number_format($val) ?></p>
        <p class="text-sm text-slate-500"><?= e($label) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden mb-6">
    <div class="p-5 border-b border-slate-100 dark:border-white/5"><h3 class="font-bold text-slate-900 dark:text-white">Applications by Course</h3></div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-400 bg-slate-50 dark:bg-white/5">
          <th class="px-5 py-3 font-semibold">Course</th><th class="px-5 py-3 font-semibold">Total</th>
          <th class="px-5 py-3 font-semibold">Approved</th><th class="px-5 py-3 font-semibold">Pending</th><th class="px-5 py-3 font-semibold">Rejected</th>
        </tr></thead>
        <tbody>
          <?php foreach ($byCourse as $r): ?>
            <tr class="border-t border-slate-100 dark:border-white/5">
              <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= e($r['title']) ?></td>
              <td class="px-5 py-3"><?= (int)$r['total'] ?></td>
              <td class="px-5 py-3 text-emerald-600"><?= (int)$r['approved'] ?></td>
              <td class="px-5 py-3 text-amber-600"><?= (int)$r['pending'] ?></td>
              <td class="px-5 py-3 text-red-600"><?= (int)$r['rejected'] ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$byCourse): ?><tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No data yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm max-w-md">
    <h3 class="font-bold text-slate-900 dark:text-white mb-4">Applications by Status</h3>
    <?php foreach ($byStatus as $s):
      $pct = $totals['applications'] ? round($s['total'] / $totals['applications'] * 100) : 0; ?>
      <div class="mb-3">
        <div class="flex justify-between text-sm mb-1"><span class="font-semibold text-slate-700 dark:text-slate-200"><?= ucfirst($s['status']) ?></span><span class="text-slate-400"><?= (int)$s['total'] ?> (<?= $pct ?>%)</span></div>
        <div class="h-2 rounded-full bg-slate-100 dark:bg-white/10 overflow-hidden"><div class="h-full bg-gradient-to-r from-primary to-secondary" style="width: <?= $pct ?>%"></div></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$byStatus): ?><p class="text-slate-400 text-sm">No applications yet.</p><?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
