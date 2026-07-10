<?php
/**
 * SIZSR Admin - Media manager (upload / browse / delete)
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'media';
$pageTitle = 'Media Manager';

if (is_post()) {
    csrf_check();
    $action = (string)input('action');

    if ($action === 'upload' && !empty($_FILES['files']['name'][0])) {
        $count = 0;
        foreach ($_FILES['files']['name'] as $i => $name) {
            if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $file = [
                'name' => $name, 'type' => $_FILES['files']['type'][$i],
                'tmp_name' => $_FILES['files']['tmp_name'][$i],
                'error' => $_FILES['files']['error'][$i], 'size' => $_FILES['files']['size'][$i],
            ];
            $res = handle_upload($file, 'media', ALLOWED_IMAGE_TYPES);
            if ($res['success']) {
                db_exec("INSERT INTO media (file_name, file_path, file_type, file_size, folder) VALUES (?,?,?,?, 'general')",
                    [$res['filename'], $res['path'], pathinfo($name, PATHINFO_EXTENSION), $file['size']]);
                $count++;
            }
        }
        log_activity('media_uploaded', "Uploaded {$count} file(s)");
        flash('success', "{$count} file(s) uploaded.");
    } elseif ($action === 'delete') {
        $id = (int)input('id');
        $m = db_one("SELECT file_path FROM media WHERE id=?", [$id]);
        if ($m) {
            @unlink(UPLOAD_PATH . '/' . $m['file_path']);
            db_exec("DELETE FROM media WHERE id=?", [$id]);
            log_activity('media_deleted', "Deleted media #{$id}");
            flash('success', 'File deleted.');
        }
    }
    redirect('admin/media.php');
}

$page = max(1, (int)input('page', 1));
$p = paginate("SELECT * FROM media ORDER BY created_at DESC", "SELECT COUNT(*) FROM media", [], $page, 24);
$files = $p['items'];

require __DIR__ . '/includes/header.php';
?>

<form method="post" enctype="multipart/form-data" class="rounded-2xl bg-white dark:bg-slate-800/60 border-2 border-dashed border-slate-200 dark:border-white/10 p-8 text-center mb-6">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="upload">
  <svg class="h-12 w-12 mx-auto text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
  <p class="mt-3 font-semibold text-slate-700 dark:text-slate-200">Upload images</p>
  <p class="text-sm text-slate-400 mb-4">JPG, PNG, WEBP, GIF up to 5MB each</p>
  <input type="file" name="files[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif" class="block mx-auto text-sm text-slate-500 file:mr-3 file:py-2.5 file:px-5 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-semibold cursor-pointer">
  <button class="mt-4 px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold">Upload</button>
</form>

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
  <?php foreach ($files as $f): ?>
    <div class="group relative rounded-xl overflow-hidden border border-slate-100 dark:border-white/5 bg-white dark:bg-slate-800/60 aspect-square">
      <img src="<?= e(UPLOAD_URL . '/' . $f['file_path']) ?>" alt="<?= e($f['file_name']) ?>" class="h-full w-full object-cover">
      <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition grid place-items-center gap-2">
        <button onclick="navigator.clipboard.writeText('<?= e(UPLOAD_URL . '/' . $f['file_path']) ?>'); this.textContent='Copied!'" class="px-3 py-1.5 rounded-lg bg-white/90 text-slate-800 text-xs font-semibold">Copy URL</button>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
          <button data-confirm="Delete this file?" class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-xs font-semibold">Delete</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$files): ?><p class="col-span-full text-center text-slate-400 py-10">No media files yet.</p><?php endif; ?>
</div>

<?= pagination_links($p, admin_url('media.php')) ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
