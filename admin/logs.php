<?php
/**
 * SIZSR Admin - Activity logs
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'logs';
$pageTitle = 'Activity Logs';

if (is_post()) {
    csrf_check();
    if (input('action') === 'clear') {
        db_exec("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        log_activity('logs_cleared', 'Cleared logs older than 30 days');
        flash('success', 'Old logs cleared.');
    }
    redirect('admin/logs.php');
}

$page = max(1, (int)input('page', 1));
$p = paginate(
    "SELECT l.*, a.name AS admin_name FROM activity_logs l LEFT JOIN admins a ON a.id=l.admin_id ORDER BY l.created_at DESC",
    "SELECT COUNT(*) FROM activity_logs", [], $page, 30
);
$logs = $p['items'];

require __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-5">
  <p class="text-slate-500"><?= (int)$p['total'] ?> log entries</p>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="clear">
    <button data-confirm="Delete logs older than 30 days?" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300">Clear old logs</button>
  </form>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-slate-400 bg-slate-50 dark:bg-white/5">
        <th class="px-5 py-3 font-semibold">Action</th>
        <th class="px-5 py-3 font-semibold">Description</th>
        <th class="px-5 py-3 font-semibold">Admin</th>
        <th class="px-5 py-3 font-semibold">IP</th>
        <th class="px-5 py-3 font-semibold">When</th>
      </tr></thead>
      <tbody>
        <?php foreach ($logs as $l): ?>
          <tr class="border-t border-slate-100 dark:border-white/5">
            <td class="px-5 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300"><?= e(str_replace('_',' ', $l['action'])) ?></span></td>
            <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($l['description'] ?? '') ?></td>
            <td class="px-5 py-3 text-slate-500"><?= e($l['admin_name'] ?? 'System') ?></td>
            <td class="px-5 py-3 text-slate-400 font-mono text-xs"><?= e($l['ip_address'] ?? '-') ?></td>
            <td class="px-5 py-3 text-slate-400"><?= e(format_date($l['created_at'], 'M d, Y g:i A')) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?><tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No activity recorded.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= pagination_links($p, admin_url('logs.php')) ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
