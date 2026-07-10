<?php
/**
 * SIZSR Admin - Applications
 *  - Landing: pick a course (open courses first, then closed-by-deadline)
 *  - Course view: list students who registered for that course, with a
 *    color-coded qualification status (Qualified / Incomplete / Not Qualified).
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'applications';
$pageTitle = 'Applications';

if (is_post()) {
    csrf_check();
    $action = (string)input('action');

    // -------- Bulk: send WhatsApp to ALL approved students (Cloud API) --------
    if ($action === 'send_all_whatsapp') {
        $cId = (int)input('course');
        $back = $cId ? '?course=' . $cId : '?all=1';

        if (!wa_enabled()) {
            flash('error', 'WhatsApp Cloud API is not enabled. Configure it in Settings first.');
            redirect('admin/applications.php' . $back);
        }

        $approved = $cId
            ? db_all("SELECT id FROM applications WHERE status='approved' AND course_id=?", [$cId])
            : db_all("SELECT id FROM applications WHERE status='approved'");

        $sent = 0; $failed = 0; $skipped = 0;
        foreach ($approved as $row) {
            $wa = notify_application_approved((int)$row['id']);
            if ($wa['success']) $sent++;
            elseif (!empty($wa['skipped'])) $skipped++;
            else $failed++;
        }
        log_activity('whatsapp_bulk_send', "Bulk WhatsApp: {$sent} sent, {$failed} failed, {$skipped} skipped");

        if ($sent > 0) flash('success', "WhatsApp sent to {$sent} approved student(s).");
        if ($skipped > 0) flash('warning', "{$skipped} skipped (missing group link or phone number).");
        if ($failed > 0) flash('error', "{$failed} failed to send. Check whatsapp logs / API settings.");
        if ($sent === 0 && $failed === 0 && $skipped === 0) flash('info', 'No approved students to message.');

        redirect('admin/applications.php' . $back);
    }

    $id     = (int)input('id');
    $app    = db_one("SELECT reference, full_name, status FROM applications WHERE id=?", [$id]);

    if ($app) {
        $statuses = ['approve'=>'approved','reject'=>'rejected','waitlist'=>'waitlist','pending'=>'pending'];
        if (isset($statuses[$action])) {
            $newStatus = $statuses[$action];
            $wasApproved = ($app['status'] === 'approved');
            db_exec("UPDATE applications SET status=? WHERE id=?", [$newStatus, $id]);
            log_activity('application_status', "Set {$app['reference']} to {$newStatus}");
            flash('success', "Application marked as {$newStatus}.");

            if ($newStatus === 'approved' && !$wasApproved && wa_enabled()) {
                $wa = notify_application_approved($id);
                if ($wa['success']) {
                    flash('success', 'WhatsApp approval message sent to ' . e((string)$wa['to']) . '.');
                } elseif (!empty($wa['skipped'])) {
                    flash('error', 'WhatsApp not sent: ' . e((string)$wa['error']));
                } else {
                    flash('error', 'WhatsApp send failed: ' . e((string)$wa['error']));
                }
            }
        } elseif ($action === 'delete') {
            db_exec("DELETE FROM applications WHERE id=?", [$id]);
            log_activity('application_deleted', "Deleted {$app['reference']}");
            flash('success', 'Application deleted.');
        }
    }
    $back = (int)input('course') ? '?course=' . (int)input('course') : '';
    redirect('admin/applications.php' . $back);
}

$courseId = (int)input('course', 0);
$showAll  = input('all') !== null;
$isList   = $courseId > 0 || $showAll;

/** Deadline status for a course row. */
function app_deadline_state(?string $deadline): array
{
    if (empty($deadline)) return ['open', 'No deadline', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'];
    $ts = strtotime($deadline);
    if ($ts < strtotime('today')) return ['closed', 'Closed ' . date('M j, Y', $ts), 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'];
    return ['open', 'Open until ' . date('M j, Y', $ts), 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'];
}

require __DIR__ . '/includes/header.php';
?>

<?php if (!$isList): /* ============ COURSE LANDING ============ */ ?>
<?php
$courseRows = db_all(
    "SELECT c.id, c.title, c.registration_deadline, c.seats_available,
            (SELECT COUNT(*) FROM applications WHERE course_id=c.id) AS app_count,
            (SELECT COUNT(*) FROM applications WHERE course_id=c.id AND status='pending') AS pending_count
     FROM courses c ORDER BY c.created_at DESC"
);
$generalCount = admin_count("SELECT COUNT(*) FROM applications WHERE course_id IS NULL");
$totalApps    = admin_count("SELECT COUNT(*) FROM applications");

$open = $closed = [];
foreach ($courseRows as $c) {
    [$state] = app_deadline_state($c['registration_deadline']);
    if ($state === 'closed') $closed[] = $c; else $open[] = $c;
}

$cardFor = function (array $c): string {
    [$state, $label, $cls] = app_deadline_state($c['registration_deadline']);
    $href = admin_url('applications.php?course=' . (int)$c['id']);
    ob_start(); ?>
    <a href="<?= e($href) ?>" class="group relative rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">
      <div class="flex items-start justify-between gap-3">
        <span class="grid place-items-center h-11 w-11 rounded-xl bg-primary/10 text-primary dark:bg-secondary/15 dark:text-secondary font-extrabold shrink-0"><?= e(mb_strtoupper(mb_substr($c['title'], 0, 1))) ?></span>
        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $cls ?>"><?= e($label) ?></span>
      </div>
      <h3 class="mt-3 font-bold text-slate-900 dark:text-white leading-snug line-clamp-2"><?= e($c['title']) ?></h3>
      <div class="mt-4 pt-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-between text-sm">
        <span class="text-slate-500"><span class="font-extrabold text-slate-900 dark:text-white text-lg"><?= (int)$c['app_count'] ?></span> applicants</span>
        <?php if ((int)$c['pending_count'] > 0): ?>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-400/15 dark:text-amber-300"><?= (int)$c['pending_count'] ?> pending</span>
        <?php endif; ?>
      </div>
      <span class="absolute right-4 bottom-4 text-slate-300 group-hover:text-primary dark:group-hover:text-secondary group-hover:translate-x-0.5 transition opacity-0 group-hover:opacity-100">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </span>
    </a>
    <?php return ob_get_clean();
};
?>

<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
  <div>
    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Applications by Course</h2>
    <p class="mt-1 text-sm text-slate-500">Select a course to review the students who registered for it.</p>
  </div>
  <a href="<?= e(admin_url('applications.php?all=1')) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800/60 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition">
    All applications (<?= (int)$totalApps ?>)
  </a>
</div>

<?php if ($open): ?>
  <div class="flex items-center gap-2 mb-3">
    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Open for Registration</h3>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <?php foreach ($open as $c) echo $cardFor($c); ?>
  </div>
<?php endif; ?>

<?php if ($closed): ?>
  <div class="flex items-center gap-2 mb-3">
    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Closed (deadline passed)</h3>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <?php foreach ($closed as $c) echo $cardFor($c); ?>
  </div>
<?php endif; ?>

<?php if ($generalCount > 0): ?>
  <a href="<?= e(admin_url('applications.php?all=1')) ?>" class="block rounded-2xl bg-slate-50 dark:bg-white/5 border border-dashed border-slate-200 dark:border-white/10 p-5 text-sm text-slate-500 hover:border-primary/40 transition">
    <span class="font-semibold text-slate-700 dark:text-slate-200"><?= (int)$generalCount ?></span> general application(s) not linked to a course &mdash; view in all applications.
  </a>
<?php endif; ?>

<?php if (!$courseRows): ?>
  <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-10 text-center text-slate-400 shadow-sm">No courses yet.</div>
<?php endif; ?>

<?php else: /* ============ APPLICATIONS LIST (per course or all) ============ */ ?>
<?php
$q      = trim((string)input('q', ''));
$status = (string)input('status', '');
$page   = max(1, (int)input('page', 1));

$selCourse = $courseId ? db_one("SELECT * FROM courses WHERE id=?", [$courseId]) : null;

$where = ['1=1'];
$params = [];
if ($q !== '') { $where[] = '(a.full_name LIKE ? OR a.email LIKE ? OR a.reference LIKE ? OR a.phone LIKE ?)'; $like="%$q%"; array_push($params,$like,$like,$like,$like); }
if (in_array($status, ['pending','approved','rejected','waitlist'], true)) { $where[] = 'a.status=?'; $params[]=$status; }
if ($courseId) { $where[] = 'a.course_id=?'; $params[]=$courseId; }
$whereSql = implode(' AND ', $where);

$p = paginate(
    "SELECT a.*, c.title AS course_title, c.required_documents, c.whatsapp_group_link FROM applications a LEFT JOIN courses c ON c.id=a.course_id WHERE $whereSql ORDER BY a.created_at DESC",
    "SELECT COUNT(*) FROM applications a WHERE $whereSql",
    $params, $page, 12
);
$apps = $p['items'];

// Uploaded document types per application (for qualification)
$docsByApp = [];
$appIds = array_map(fn($a) => (int)$a['id'], $apps);
if ($appIds) {
    $in = implode(',', array_fill(0, count($appIds), '?'));
    foreach (db_all("SELECT application_id, doc_type FROM application_documents WHERE application_id IN ($in)", $appIds) as $d) {
        $docsByApp[(int)$d['application_id']][] = $d['doc_type'];
    }
}

// Status counts scoped to course (or global)
$scope = $courseId ? ' WHERE course_id=' . (int)$courseId : '';
$counts = [
    'all'      => admin_count("SELECT COUNT(*) FROM applications" . $scope),
    'pending'  => admin_count("SELECT COUNT(*) FROM applications" . ($scope ? $scope . " AND" : " WHERE") . " status='pending'"),
    'approved' => admin_count("SELECT COUNT(*) FROM applications" . ($scope ? $scope . " AND" : " WHERE") . " status='approved'"),
    'waitlist' => admin_count("SELECT COUNT(*) FROM applications" . ($scope ? $scope . " AND" : " WHERE") . " status='waitlist'"),
    'rejected' => admin_count("SELECT COUNT(*) FROM applications" . ($scope ? $scope . " AND" : " WHERE") . " status='rejected'"),
];

$baseQs = array_filter(['course' => $courseId ?: null, 'all' => $showAll ? 1 : null, 'q' => $q ?: null]);
$pagBase = admin_url('applications.php') . '?' . http_build_query(array_filter(['q'=>$q?:null,'status'=>$status?:null,'course'=>$courseId?:null,'all'=>$showAll?1:null]));
$tabHref = function ($k) use ($courseId, $showAll, $q) {
    return admin_url('applications.php') . '?' . http_build_query(array_filter([
        'course' => $courseId ?: null, 'all' => $showAll ? 1 : null, 'status' => $k ?: null, 'q' => $q ?: null,
    ]));
};
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
  <div>
    <a href="<?= e(admin_url('applications.php')) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-primary mb-2">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
      All courses
    </a>
    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $selCourse ? e($selCourse['title']) : 'All Applications' ?></h2>
    <?php if ($selCourse): [$st,$dl,$cls] = app_deadline_state($selCourse['registration_deadline']); ?>
      <div class="mt-1.5 flex items-center gap-2 text-sm">
        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $cls ?>"><?= e($dl) ?></span>
        <span class="text-slate-500"><?= (int)$counts['all'] ?> applicant(s)</span>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($counts['approved'] > 0): ?>
  <form method="post" onsubmit="return confirm('Send the WhatsApp approval message to all <?= (int)$counts['approved'] ?> approved student(s)?');">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="send_all_whatsapp">
    <input type="hidden" name="course" value="<?= (int)$courseId ?>">
    <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold shadow-lg shadow-emerald-500/20 transition">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.25.69-1.45 1.32-1.99 1.36-.53.05-.53.42-3.34-.7-2.81-1.12-4.6-3.99-4.74-4.17-.14-.18-1.13-1.5-1.13-2.87 0-1.36.71-2.03.97-2.31.25-.28.55-.35.74-.35.18 0 .37 0 .53.01.17.01.4-.06.62.48.25.59.83 2.04.9 2.19.07.14.11.31.02.49-.08.18-.13.3-.25.45-.13.15-.27.34-.39.45-.13.13-.26.27-.11.52.14.25.64 1.05 1.37 1.7.94.83 1.73 1.09 1.98 1.21.25.12.39.1.53-.06.14-.16.61-.71.78-.95.16-.25.32-.21.54-.13.22.08 1.41.66 1.65.78.25.12.41.18.47.28.06.1.06.59-.19 1.28Z"/></svg>
      Send WhatsApp to all approved (<?= (int)$counts['approved'] ?>)
    </button>
  </form>
  <?php endif; ?>
</div>

<!-- Qualification legend -->
<div class="flex flex-wrap items-center gap-4 mb-4 text-xs text-slate-500">
  <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full" style="background:#16A34A"></span> Qualified</span>
  <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full" style="background:#FACC15"></span> Incomplete</span>
  <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full" style="background:#DC2626"></span> Not Qualified</span>
</div>

<!-- Status tabs -->
<div class="flex flex-wrap gap-2 mb-5">
  <?php
  $tabs = ['' => 'All ('.$counts['all'].')', 'pending'=>'Pending ('.$counts['pending'].')', 'approved'=>'Approved ('.$counts['approved'].')', 'waitlist'=>'Waitlist ('.$counts['waitlist'].')', 'rejected'=>'Rejected ('.$counts['rejected'].')'];
  foreach ($tabs as $k=>$lbl):
    $active = $status === $k; ?>
    <a href="<?= e($tabHref($k)) ?>" class="px-4 py-2 rounded-xl text-sm font-semibold transition <?= $active ? 'bg-primary text-white' : 'bg-white dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-slate-100 dark:border-white/5' ?>"><?= $lbl ?></a>
  <?php endforeach; ?>
</div>

<form method="get" class="flex flex-col sm:flex-row gap-2 mb-5">
  <?php if ($courseId): ?><input type="hidden" name="course" value="<?= (int)$courseId ?>"><?php endif; ?>
  <?php if ($showAll): ?><input type="hidden" name="all" value="1"><?php endif; ?>
  <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, email, reference..." class="form-input flex-1">
  <button class="px-5 py-2.5 rounded-xl bg-slate-800 dark:bg-white/10 text-white text-sm font-semibold">Search</button>
</form>

<div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-slate-400 bg-slate-50 dark:bg-white/5">
        <th class="px-5 py-3 font-semibold">Applicant</th>
        <?php if (!$courseId): ?><th class="px-5 py-3 font-semibold">Course</th><?php endif; ?>
        <th class="px-5 py-3 font-semibold">Qualification</th>
        <th class="px-5 py-3 font-semibold">Status</th>
        <th class="px-5 py-3 font-semibold">Date</th>
        <th class="px-5 py-3 font-semibold text-right">Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach ($apps as $a):
          $qa = evaluate_application($a, $docsByApp[(int)$a['id']] ?? [], ['required_documents' => $a['required_documents'] ?? '']);
          $miss = $qa['missing'];
          $missShort = array_slice($miss, 0, 3);
        ?>
          <tr class="border-t border-slate-100 dark:border-white/5 hover:bg-slate-50/50 dark:hover:bg-white/5 align-top">
            <td class="px-5 py-4">
              <a href="<?= e(admin_url('application-view.php?id=' . $a['id'])) ?>" class="font-semibold text-slate-800 dark:text-slate-200 hover:text-primary"><?= e($a['full_name']) ?></a>
              <p class="text-xs text-slate-400"><?= e($a['email']) ?></p>
              <p class="font-mono text-[11px] text-slate-400 mt-0.5"><?= e($a['reference']) ?></p>
            </td>
            <?php if (!$courseId): ?><td class="px-5 py-4 text-slate-500"><?= e(excerpt((string)($a['course_title'] ?? '-'), 22)) ?></td><?php endif; ?>
            <td class="px-5 py-4">
              <div class="flex items-center gap-2">
                <span style="background:<?= $qa['bg'] ?>;color:<?= $qa['text'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold"><?= e($qa['label']) ?></span>
                <span class="text-xs font-semibold text-slate-400"><?= (int)$qa['percent'] ?>%</span>
              </div>
              <?php if ($miss): ?>
                <p class="mt-1 text-[11px] text-slate-400">Missing: <?= e(implode(', ', $missShort)) ?><?= count($miss) > 3 ? ' +' . (count($miss) - 3) : '' ?></p>
              <?php endif; ?>
            </td>
            <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= status_badge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="px-5 py-4 text-slate-400 whitespace-nowrap"><?= e(format_date($a['created_at'], 'M d, Y')) ?></td>
            <td class="px-5 py-4">
              <div class="flex items-center justify-end gap-1">
                <a href="<?= e(admin_url('application-view.php?id=' . $a['id'])) ?>" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 text-slate-500" title="View">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <?php if ($a['status'] !== 'approved'): ?>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="course" value="<?= (int)$courseId ?>">
                  <button class="p-2 rounded-lg hover:bg-emerald-50 text-emerald-600" title="Approve"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg></button>
                </form>
                <?php endif; ?>
                <?php $waRow = wa_click_link_for_application($a); if ($a['status'] === 'approved' && $waRow['url'] !== ''): ?>
                <a href="<?= e($waRow['url']) ?>" target="_blank" rel="noopener" class="p-2 rounded-lg hover:bg-emerald-50 text-emerald-600" title="Send WhatsApp group link">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.25.69-1.45 1.32-1.99 1.36-.53.05-.53.42-3.34-.7-2.81-1.12-4.6-3.99-4.74-4.17-.14-.18-1.13-1.5-1.13-2.87 0-1.36.71-2.03.97-2.31.25-.28.55-.35.74-.35.18 0 .37 0 .53.01.17.01.4-.06.62.48.25.59.83 2.04.9 2.19.07.14.11.31.02.49-.08.18-.13.3-.25.45-.13.15-.27.34-.39.45-.13.13-.26.27-.11.52.14.25.64 1.05 1.37 1.7.94.83 1.73 1.09 1.98 1.21.25.12.39.1.53-.06.14-.16.61-.71.78-.95.16-.25.32-.21.54-.13.22.08 1.41.66 1.65.78.25.12.41.18.47.28.06.1.06.59-.19 1.28Z"/></svg>
                </a>
                <?php endif; ?>
                <?php if ($a['status'] !== 'rejected'): ?>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="course" value="<?= (int)$courseId ?>">
                  <button class="p-2 rounded-lg hover:bg-red-50 text-red-600" title="Reject"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$apps): ?><tr><td colspan="<?= $courseId ? 5 : 6 ?>" class="px-5 py-10 text-center text-slate-400">No applications found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= pagination_links($p, $pagBase) ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
