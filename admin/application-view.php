<?php
/**
 * SIZSR Admin - Application detail / student profile
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$id = (int)input('id');
$app = db_one("SELECT a.*, c.title AS course_title, c.required_documents, c.whatsapp_group_link FROM applications a LEFT JOIN courses c ON c.id=a.course_id WHERE a.id=?", [$id]);
if (!$app) { flash('error', 'Application not found.'); redirect('admin/applications.php'); }

if (is_post()) {
    csrf_check();
    $action = (string)input('action');
    if ($action === 'status') {
        $newStatus = in_array(input('status'), ['pending','approved','rejected','waitlist'], true) ? (string)input('status') : 'pending';
        $notes = clean((string)input('admin_notes'));
        $wasApproved = ($app['status'] === 'approved');
        db_exec("UPDATE applications SET status=?, admin_notes=? WHERE id=?", [$newStatus, $notes ?: null, $id]);
        log_activity('application_updated', "Updated {$app['reference']} -> {$newStatus}");
        flash('success', 'Application updated.');

        if ($newStatus === 'approved' && !$wasApproved && wa_enabled()) {
            $wa = notify_application_approved($id);
            if ($wa['success']) {
                flash('success', 'WhatsApp approval message sent to ' . $wa['to'] . '.');
            } elseif (!empty($wa['skipped'])) {
                flash('error', 'WhatsApp not sent: ' . $wa['error']);
            } else {
                flash('error', 'WhatsApp send failed: ' . $wa['error']);
            }
        }
        redirect('admin/application-view.php?id=' . $id);
    } elseif ($action === 'send_whatsapp') {
        $wa = notify_application_approved($id);
        if ($wa['success']) {
            flash('success', 'WhatsApp message sent to ' . $wa['to'] . '.');
        } else {
            flash('error', 'WhatsApp not sent: ' . $wa['error']);
        }
        redirect('admin/application-view.php?id=' . $id);
    }
}

$docs = db_all("SELECT * FROM application_documents WHERE application_id=? ORDER BY id ASC", [$id]);

$waLastLog   = db_one("SELECT * FROM whatsapp_logs WHERE application_id=? ORDER BY id DESC LIMIT 1", [$id]);
$waConfigured = wa_enabled();
$waHasLink   = trim((string)($app['whatsapp_group_link'] ?? '')) !== '';
$waHasPhone  = (trim((string)($app['whatsapp'] ?? '')) !== '') || (trim((string)($app['phone'] ?? '')) !== '');
$waClick     = wa_click_link_for_application($app); // ['url','reason']

// Qualification evaluation
$qa = evaluate_application($app, array_column($docs, 'doc_type'), ['required_documents' => $app['required_documents'] ?? '']);

$adminPage = 'applications';
$pageTitle = 'Application: ' . $app['reference'];

require __DIR__ . '/includes/header.php';

$row = function (string $label, $value) {
    if ($value === null || $value === '' || $value === '-') return '';
    return '<div class="flex justify-between gap-4 py-2.5 border-b border-slate-50 dark:border-white/5 last:border-0">'
        . '<dt class="text-slate-400 shrink-0">' . e($label) . '</dt>'
        . '<dd class="font-semibold text-slate-800 dark:text-slate-200 text-right break-words">' . e((string)$value) . '</dd></div>';
};
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-5 no-print">
  <a href="<?= e(admin_url('applications.php')) ?>" class="text-sm font-semibold text-slate-500 hover:text-primary">&larr; Back to applications</a>
  <div class="flex gap-2">
    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300">
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      Print / PDF
    </button>
  </div>
</div>

<div id="printArea" class="grid lg:grid-cols-3 gap-5">
  <!-- Profile -->
  <div class="lg:col-span-2 space-y-5">
    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
      <div class="flex items-start gap-4">
        <span class="h-16 w-16 rounded-2xl bg-gradient-to-br from-primary to-secondary text-white grid place-items-center text-2xl font-extrabold shrink-0"><?= e(mb_substr($app['full_name'],0,1)) ?></span>
        <div class="flex-1">
          <h2 class="text-xl font-extrabold text-slate-900 dark:text-white"><?= e($app['full_name']) ?></h2>
          <p class="text-sm text-slate-500"><?= e($app['email']) ?> &middot; <?= e($app['phone']) ?></p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span style="background:<?= $qa['bg'] ?>;color:<?= $qa['text'] ?>" class="px-2.5 py-1 rounded-full text-xs font-bold"><?= e($qa['label']) ?> &middot; <?= (int)$qa['percent'] ?>%</span>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= status_badge($app['status']) ?>"><?= ucfirst($app['status']) ?></span>
            <span class="font-mono text-xs text-slate-400"><?= e($app['reference']) ?></span>
          </div>
        </div>
      </div>
      <p class="mt-4 text-sm text-slate-500">Applied for: <span class="font-semibold text-primary dark:text-secondary"><?= e($app['course_title'] ?? 'N/A') ?></span> on <?= e(format_date($app['created_at'], 'M d, Y')) ?></p>
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <h3 class="font-bold text-slate-900 dark:text-white mb-3">Personal</h3>
        <dl class="text-sm">
          <?= $row('Gender', $app['gender']) ?>
          <?= $row('Date of Birth', format_date($app['date_of_birth'])) ?>
          <?= $row('National ID', $app['national_id']) ?>
          <?= $row('WhatsApp', $app['whatsapp']) ?>
        </dl>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <h3 class="font-bold text-slate-900 dark:text-white mb-3">Education</h3>
        <dl class="text-sm">
          <?= $row('Level', $app['education_level']) ?>
          <?= $row('School', $app['school']) ?>
          <?= $row('University', $app['university']) ?>
          <?= $row('Faculty', $app['faculty']) ?>
          <?= $row('Department', $app['department']) ?>
          <?= $row('Graduation Year', $app['graduation_year']) ?>
        </dl>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <h3 class="font-bold text-slate-900 dark:text-white mb-3">Address</h3>
        <dl class="text-sm">
          <?= $row('Region', $app['region']) ?>
          <?= $row('District', $app['district']) ?>
          <?= $row('Address', $app['address']) ?>
        </dl>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <h3 class="font-bold text-slate-900 dark:text-white mb-3">Professional</h3>
        <dl class="text-sm">
          <?= $row('Skills', $app['skills']) ?>
          <?= $row('Experience', $app['experience']) ?>
          <?= $row('Certifications', $app['certifications']) ?>
          <?= $row('Portfolio', $app['portfolio_url']) ?>
        </dl>
      </div>
    </div>

    <?php if ($app['why_join'] || $app['goals'] || $app['expectations']): ?>
    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
      <h3 class="font-bold text-slate-900 dark:text-white mb-3">Motivation</h3>
      <div class="space-y-4 text-sm">
        <?php foreach (['Why join?'=>$app['why_join'],'Goals'=>$app['goals'],'Expectations'=>$app['expectations']] as $lbl=>$txt): if(!$txt) continue; ?>
          <div><p class="text-slate-400 mb-1"><?= e($lbl) ?></p><p class="text-slate-700 dark:text-slate-200 whitespace-pre-line"><?= e($txt) ?></p></div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Documents -->
    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
      <h3 class="font-bold text-slate-900 dark:text-white mb-3">Documents</h3>
      <?php if ($docs): ?>
        <div class="grid sm:grid-cols-2 gap-3">
          <?php foreach ($docs as $d): ?>
            <a href="<?= e(UPLOAD_URL . '/' . $d['file_path']) ?>" target="_blank" class="flex items-center gap-3 rounded-xl border border-slate-100 dark:border-white/5 p-3 hover:bg-slate-50 dark:hover:bg-white/5">
              <span class="grid place-items-center h-10 w-10 rounded-lg bg-primary/10 text-primary shrink-0">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </span>
              <div class="min-w-0"><p class="font-semibold text-slate-800 dark:text-slate-200 text-sm"><?= e($d['doc_type']) ?></p><p class="text-xs text-slate-400 truncate"><?= e($d['original_name'] ?? '') ?></p></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-sm text-slate-400">No documents uploaded.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sidebar: qualification + status + notes -->
  <div class="space-y-5">
    <!-- Qualification card -->
    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
      <h3 class="font-bold text-slate-900 dark:text-white mb-3">Application Completion</h3>
      <div class="flex items-center justify-between gap-3">
        <span style="background:<?= $qa['bg'] ?>;color:<?= $qa['text'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold"><?= e($qa['label']) ?></span>
        <span class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= (int)$qa['percent'] ?>%</span>
      </div>
      <div class="mt-3 h-2.5 rounded-full bg-slate-100 dark:bg-white/10 overflow-hidden">
        <div class="h-full rounded-full" style="width:<?= (int)$qa['percent'] ?>%;background:<?= $qa['bg'] ?>"></div>
      </div>
      <p class="mt-2 text-xs text-slate-400"><?= (int)$qa['done'] ?> of <?= (int)$qa['total'] ?> required items completed.</p>

      <?php if (!empty($qa['missing'])): ?>
        <div class="mt-4 rounded-xl border border-amber-200 dark:border-amber-400/20 bg-amber-50 dark:bg-amber-400/10 p-4">
          <p class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-300 mb-2">Missing Requirements</p>
          <ul class="space-y-1.5">
            <?php foreach ($qa['missing'] as $m): ?>
              <li class="flex items-start gap-2 text-sm text-amber-800 dark:text-amber-200">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                <?= e($m) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php else: ?>
        <div class="mt-4 flex items-center gap-2 rounded-xl border border-emerald-200 dark:border-emerald-400/20 bg-emerald-50 dark:bg-emerald-400/10 p-4 text-sm font-semibold text-emerald-700 dark:text-emerald-300">
          <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
          All required information provided.
        </div>
      <?php endif; ?>
    </div>

    <form method="post" class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4 sticky top-24 no-print">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="status">
      <h3 class="font-bold text-slate-900 dark:text-white">Manage Application</h3>
      <div><label class="form-label">Status</label>
        <select name="status" class="form-select">
          <?php foreach (['pending'=>'Pending','approved'=>'Approved','waitlist'=>'Waitlist','rejected'=>'Rejected'] as $k=>$lbl): ?>
            <option value="<?= $k ?>" <?= $app['status']===$k?'selected':'' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label class="form-label">Admin Notes</label><textarea name="admin_notes" rows="5" class="form-textarea" placeholder="Internal notes..."><?= e($app['admin_notes'] ?? '') ?></textarea></div>
      <button class="w-full py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold">Save Changes</button>

      <div class="pt-3 border-t border-slate-100 dark:border-white/5 grid grid-cols-2 gap-2 text-sm">
        <a href="mailto:<?= e($app['email']) ?>" class="text-center py-2.5 rounded-lg border border-slate-200 dark:border-white/10 font-semibold text-slate-600 dark:text-slate-300">Email</a>
        <a href="tel:<?= e($app['phone']) ?>" class="text-center py-2.5 rounded-lg border border-slate-200 dark:border-white/10 font-semibold text-slate-600 dark:text-slate-300">Call</a>
      </div>
    </form>

    <!-- WhatsApp notification -->
    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4 no-print">
      <div class="flex items-center gap-2">
        <span class="grid place-items-center h-8 w-8 rounded-lg bg-emerald-500/10 text-emerald-500 shrink-0">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.25.69-1.45 1.32-1.99 1.36-.53.05-.53.42-3.34-.7-2.81-1.12-4.6-3.99-4.74-4.17-.14-.18-1.13-1.5-1.13-2.87 0-1.36.71-2.03.97-2.31.25-.28.55-.35.74-.35.18 0 .37 0 .53.01.17.01.4-.06.62.48.25.59.83 2.04.9 2.19.07.14.11.31.02.49-.08.18-.13.3-.25.45-.13.15-.27.34-.39.45-.13.13-.26.27-.11.52.14.25.64 1.05 1.37 1.7.94.83 1.73 1.09 1.98 1.21.25.12.39.1.53-.06.14-.16.61-.71.78-.95.16-.25.32-.21.54-.13.22.08 1.41.66 1.65.78.25.12.41.18.47.28.06.1.06.59-.19 1.28Z"/></svg>
        </span>
        <h3 class="font-bold text-slate-900 dark:text-white">WhatsApp Notification</h3>
      </div>

      <?php if ($waLastLog): ?>
        <div class="flex items-center justify-between gap-2 text-sm">
          <span class="text-slate-400">Last message</span>
          <?php if ($waLastLog['status'] === 'sent'): ?>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Sent &middot; <?= e(format_date($waLastLog['created_at'], 'M d, H:i')) ?></span>
          <?php else: ?>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400">Failed</span>
          <?php endif; ?>
        </div>
        <?php if ($waLastLog['status'] !== 'sent' && !empty($waLastLog['error'])): ?>
          <p class="text-xs text-rose-500 break-words"><?= e((string)$waLastLog['error']) ?></p>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (!$waHasLink): ?>
        <p class="text-xs text-amber-600 dark:text-amber-400">No WhatsApp group link is set on this course. Add one on the course edit page.</p>
      <?php elseif (!$waHasPhone): ?>
        <p class="text-xs text-amber-600 dark:text-amber-400">This student has no phone/WhatsApp number on file.</p>
      <?php endif; ?>

      <!-- Primary: open WhatsApp with the message pre-filled (works on any host) -->
      <?php if ($waClick['url'] !== ''): ?>
        <a href="<?= e($waClick['url']) ?>" target="_blank" rel="noopener"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold transition">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.25.69-1.45 1.32-1.99 1.36-.53.05-.53.42-3.34-.7-2.81-1.12-4.6-3.99-4.74-4.17-.14-.18-1.13-1.5-1.13-2.87 0-1.36.71-2.03.97-2.31.25-.28.55-.35.74-.35.18 0 .37 0 .53.01.17.01.4-.06.62.48.25.59.83 2.04.9 2.19.07.14.11.31.02.49-.08.18-.13.3-.25.45-.13.15-.27.34-.39.45-.13.13-.26.27-.11.52.14.25.64 1.05 1.37 1.7.94.83 1.73 1.09 1.98 1.21.25.12.39.1.53-.06.14-.16.61-.71.78-.95.16-.25.32-.21.54-.13.22.08 1.41.66 1.65.78.25.12.41.18.47.28.06.1.06.59-.19 1.28Z"/></svg>
          Open WhatsApp &amp; Send
        </a>
        <p class="text-[11px] text-slate-400 text-center">Opens WhatsApp with the approval message ready for <?= e($app['whatsapp'] ?: $app['phone'] ?: '—') ?>. Just press send.</p>
      <?php endif; ?>

      <!-- Optional: server-side auto-send via the Cloud API (only when configured) -->
      <?php if ($waConfigured && $waHasLink && $waHasPhone): ?>
        <form method="post" class="pt-2 border-t border-slate-100 dark:border-white/5">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="send_whatsapp">
          <button class="w-full py-2.5 rounded-xl border border-emerald-500/40 text-emerald-600 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition">
            <?= $waLastLog && $waLastLog['status'] === 'sent' ? 'Auto-resend via Cloud API' : 'Auto-send via Cloud API' ?>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
