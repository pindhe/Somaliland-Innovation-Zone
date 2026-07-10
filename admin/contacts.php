<?php
/**
 * SIZSR Admin - Contact messages
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'contacts';
$pageTitle = 'Messages';

if (is_post()) {
    csrf_check();
    $action = (string)input('action');
    $id     = (int)input('id');

    if ($action === 'reply') {
        $reply = clean((string)input('reply'));
        db_exec("UPDATE contacts SET reply=?, status='replied' WHERE id=?", [$reply ?: null, $id]);
        log_activity('message_replied', "Replied to message #{$id}");
        flash('success', 'Reply saved. Use the email link to send it.');
    } elseif ($action === 'read') {
        db_exec("UPDATE contacts SET status='read' WHERE id=? AND status='new'", [$id]);
    } elseif ($action === 'delete') {
        db_exec("DELETE FROM contacts WHERE id=?", [$id]);
        log_activity('message_deleted', "Deleted message #{$id}");
        flash('success', 'Message deleted.');
    }
    redirect('admin/contacts.php' . ($action==='read' ? '?open='.$id : ''));
}

$page = max(1, (int)input('page', 1));
$p = paginate("SELECT * FROM contacts ORDER BY created_at DESC", "SELECT COUNT(*) FROM contacts", [], $page, 15);
$messages = $p['items'];
$openId = (int)input('open', 0);

require __DIR__ . '/includes/header.php';
?>

<div class="grid lg:grid-cols-3 gap-5">
  <!-- List -->
  <div class="lg:col-span-1 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 dark:border-white/5"><h3 class="font-bold text-slate-900 dark:text-white">Inbox (<?= (int)$p['total'] ?>)</h3></div>
    <div class="divide-y divide-slate-100 dark:divide-white/5 max-h-[70vh] overflow-y-auto">
      <?php foreach ($messages as $m): ?>
        <a href="<?= e(admin_url('contacts.php?open=' . $m['id'])) ?>" class="block p-4 hover:bg-slate-50 dark:hover:bg-white/5 <?= $openId===(int)$m['id'] ? 'bg-primary/5' : '' ?>">
          <div class="flex items-center justify-between gap-2">
            <p class="font-semibold text-slate-800 dark:text-slate-200 truncate"><?= e($m['name']) ?></p>
            <?php if ($m['status']==='new'): ?><span class="h-2 w-2 rounded-full bg-primary shrink-0"></span><?php endif; ?>
          </div>
          <p class="text-sm text-slate-500 truncate"><?= e($m['subject'] ?: 'No subject') ?></p>
          <p class="text-xs text-slate-400 mt-1"><?= e(time_ago($m['created_at'])) ?></p>
        </a>
      <?php endforeach; ?>
      <?php if (!$messages): ?><p class="p-6 text-center text-slate-400 text-sm">No messages.</p><?php endif; ?>
    </div>
  </div>

  <!-- Detail -->
  <div class="lg:col-span-2">
    <?php
    $msg = $openId ? db_one("SELECT * FROM contacts WHERE id=?", [$openId]) : ($messages[0] ?? null);
    if ($msg):
      if ($msg['status'] === 'new') { db_exec("UPDATE contacts SET status='read' WHERE id=?", [$msg['id']]); $msg['status']='read'; }
    ?>
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white"><?= e($msg['subject'] ?: 'No subject') ?></h2>
            <p class="text-sm text-slate-500 mt-1"><?= e($msg['name']) ?> &lt;<?= e($msg['email']) ?>&gt;</p>
            <?php if ($msg['phone']): ?><p class="text-sm text-slate-500"><?= e($msg['phone']) ?></p><?php endif; ?>
          </div>
          <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= status_badge($msg['status']) ?>"><?= ucfirst($msg['status']) ?></span>
        </div>
        <p class="text-xs text-slate-400 mt-2"><?= e(format_date($msg['created_at'], 'M d, Y g:i A')) ?></p>
        <div class="mt-5 rounded-xl bg-slate-50 dark:bg-white/5 p-4 text-slate-700 dark:text-slate-200 text-sm whitespace-pre-line leading-relaxed"><?= e($msg['message']) ?></div>

        <?php if ($msg['reply']): ?>
          <div class="mt-4 rounded-xl bg-primary/5 border border-primary/10 p-4">
            <p class="text-xs font-semibold text-primary mb-1">Your reply</p>
            <p class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line"><?= e($msg['reply']) ?></p>
          </div>
        <?php endif; ?>

        <form method="post" class="mt-5 space-y-3">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="reply">
          <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
          <label class="form-label">Reply</label>
          <textarea name="reply" rows="4" class="form-textarea" placeholder="Type your reply..."><?= e($msg['reply'] ?? '') ?></textarea>
          <div class="flex flex-wrap gap-2">
            <button class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold">Save Reply</button>
            <a href="mailto:<?= e($msg['email']) ?>?subject=<?= e(rawurlencode('RE: ' . ($msg['subject'] ?: 'Your message'))) ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300">Open in Email</a>
            <button form="delMsg" data-confirm="Delete this message?" class="ml-auto px-5 py-2.5 rounded-xl border border-red-200 text-sm font-semibold text-red-600">Delete</button>
          </div>
        </form>
        <form method="post" id="delMsg"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$msg['id'] ?>"></form>
      </div>
    <?php else: ?>
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-12 text-center text-slate-400 shadow-sm">Select a message to read.</div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
