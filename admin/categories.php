<?php
/**
 * SIZSR Admin - Categories CRUD
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'categories';
$pageTitle = 'Categories';

// Handle actions
if (is_post()) {
    csrf_check();
    $action = (string)input('action');

    if ($action === 'save') {
        $id    = (int)input('id', 0);
        $name  = clean((string)input('name'));
        $desc  = clean((string)input('description'));
        $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string)input('color')) ? (string)input('color') : '#B11314';
        $status= input('status') === 'inactive' ? 'inactive' : 'active';

        if ($name === '') {
            flash('error', 'Category name is required.');
        } else {
            $slug = slugify($name);
            if ($id > 0) {
                db_exec("UPDATE categories SET name=?, slug=?, description=?, color=?, status=? WHERE id=?",
                    [$name, $slug, $desc ?: null, $color, $status, $id]);
                log_activity('category_updated', "Updated category: {$name}");
                flash('success', 'Category updated.');
            } else {
                db_exec("INSERT INTO categories (name, slug, description, color, status) VALUES (?,?,?,?,?)",
                    [$name, $slug, $desc ?: null, $color, $status]);
                log_activity('category_created', "Created category: {$name}");
                flash('success', 'Category created.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)input('id');
        $cat = db_one("SELECT name FROM categories WHERE id=?", [$id]);
        if ($cat) {
            db_exec("DELETE FROM categories WHERE id=?", [$id]);
            log_activity('category_deleted', "Deleted category: {$cat['name']}");
            flash('success', 'Category deleted.');
        }
    }
    redirect('admin/categories.php');
}

$categories = db_all(
    "SELECT cat.*, (SELECT COUNT(*) FROM courses WHERE category_id = cat.id) AS course_count
     FROM categories cat ORDER BY cat.name ASC"
);

require __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
  <p class="text-slate-500"><?= count($categories) ?> categories</p>
  <button onclick="openCatModal()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold shadow-lg hover:-translate-y-0.5 transition">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
    Add Category
  </button>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <?php foreach ($categories as $cat): ?>
    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm">
      <div class="flex items-start justify-between">
        <span class="h-10 w-10 rounded-xl" style="background:<?= e($cat['color']) ?>"></span>
        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= status_badge($cat['status']) ?>"><?= ucfirst($cat['status']) ?></span>
      </div>
      <h3 class="mt-4 font-bold text-slate-900 dark:text-white"><?= e($cat['name']) ?></h3>
      <p class="text-sm text-slate-500 mt-1 line-clamp-2"><?= e($cat['description'] ?? '') ?></p>
      <p class="text-xs text-slate-400 mt-3"><?= (int)$cat['course_count'] ?> course(s)</p>
      <div class="mt-4 flex gap-2">
        <button onclick='openCatModal(<?= json_encode($cat, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' class="flex-1 py-2 rounded-lg border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5">Edit</button>
        <form method="post" class="flex-1">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
          <button data-confirm="Delete this category? Courses will be uncategorized." class="w-full py-2 rounded-lg border border-red-200 text-sm font-semibold text-red-600 hover:bg-red-50">Delete</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$categories): ?><p class="text-slate-400">No categories yet.</p><?php endif; ?>
</div>

<!-- Modal -->
<div id="catModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50" onclick="closeCatModal()"></div>
  <div class="absolute inset-0 grid place-items-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6">
      <h3 id="catModalTitle" class="text-lg font-bold text-slate-900 dark:text-white mb-5">Add Category</h3>
      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" id="catId" value="0">
        <div><label class="form-label">Name *</label><input name="name" id="catName" required class="form-input"></div>
        <div><label class="form-label">Description</label><textarea name="description" id="catDesc" rows="2" class="form-textarea"></textarea></div>
        <div class="grid grid-cols-2 gap-4">
          <div><label class="form-label">Color</label><input type="color" name="color" id="catColor" value="#B11314" class="h-11 w-full rounded-xl border border-slate-200 dark:border-white/10 cursor-pointer"></div>
          <div><label class="form-label">Status</label>
            <select name="status" id="catStatus" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select>
          </div>
        </div>
        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeCatModal()" class="flex-1 py-3 rounded-xl border border-slate-200 dark:border-white/10 font-semibold text-slate-600 dark:text-slate-300">Cancel</button>
          <button class="flex-1 py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$adminScripts = <<<'HTML'
<script>
function openCatModal(data) {
  document.getElementById('catModal').classList.remove('hidden');
  document.getElementById('catModalTitle').textContent = data ? 'Edit Category' : 'Add Category';
  document.getElementById('catId').value = data ? data.id : 0;
  document.getElementById('catName').value = data ? data.name : '';
  document.getElementById('catDesc').value = data && data.description ? data.description : '';
  document.getElementById('catColor').value = data && data.color ? data.color : '#B11314';
  document.getElementById('catStatus').value = data ? data.status : 'active';
}
function closeCatModal() { document.getElementById('catModal').classList.add('hidden'); }
</script>
HTML;
require __DIR__ . '/includes/footer.php';
