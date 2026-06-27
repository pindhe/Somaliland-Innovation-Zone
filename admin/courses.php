<?php
/**
 * SIZSR Admin - Courses list + quick actions
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'courses';
$pageTitle = 'Courses';

// Quick actions
if (is_post()) {
    csrf_check();
    $action = (string)input('action');
    $id     = (int)input('id');
    $course = db_one("SELECT title, status FROM courses WHERE id=?", [$id]);

    if ($course) {
        if ($action === 'publish') {
            db_exec("UPDATE courses SET status='published' WHERE id=?", [$id]);
            log_activity('course_published', "Published: {$course['title']}");
            flash('success', 'Course published.');
        } elseif ($action === 'archive') {
            db_exec("UPDATE courses SET status='archived' WHERE id=?", [$id]);
            log_activity('course_archived', "Archived: {$course['title']}");
            flash('success', 'Course archived.');
        } elseif ($action === 'draft') {
            db_exec("UPDATE courses SET status='draft' WHERE id=?", [$id]);
            flash('success', 'Course set to draft.');
        } elseif ($action === 'feature') {
            db_exec("UPDATE courses SET is_featured = 1 - is_featured WHERE id=?", [$id]);
            flash('success', 'Featured status updated.');
        } elseif ($action === 'delete') {
            $img = db_scalar("SELECT image FROM courses WHERE id=?", [$id]);
            db_exec("DELETE FROM courses WHERE id=?", [$id]);
            if ($img) @unlink(UPLOAD_PATH . '/courses/' . $img);
            log_activity('course_deleted', "Deleted: {$course['title']}");
            flash('success', 'Course deleted.');
        }
    }
    redirect('admin/courses.php' . (input('q') ? '?q=' . urlencode((string)input('q')) : ''));
}

// Filters
$q      = trim((string)input('q', ''));
$status = (string)input('status', '');
$page   = max(1, (int)input('page', 1));

$where = ['1=1'];
$params = [];
if ($q !== '') { $where[] = '(c.title LIKE ? OR c.trainer LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
if (in_array($status, ['draft','published','archived'], true)) { $where[] = 'c.status = ?'; $params[] = $status; }
$whereSql = implode(' AND ', $where);

$p = paginate(
    "SELECT c.*, cat.name AS category_name FROM courses c LEFT JOIN categories cat ON cat.id=c.category_id WHERE $whereSql ORDER BY c.created_at DESC",
    "SELECT COUNT(*) FROM courses c WHERE $whereSql",
    $params, $page, 10
);
$courses = $p['items'];

$pagBase = admin_url('courses.php') . '?' . http_build_query(array_filter(['q'=>$q ?: null, 'status'=>$status ?: null]));

// Data for the course form modal
$categories = db_all("SELECT id, name FROM categories WHERE status='active' ORDER BY name ASC");
$editId     = (int)input('edit', 0);
$editCourse = $editId ? db_one("SELECT * FROM courses WHERE id=?", [$editId]) : null;
if ($editId && !$editCourse) { flash('error', 'Course not found.'); }
$openForm   = ($editCourse !== null) || (input('new') !== null);

// Summary stats
$cStats = [
    'total'     => admin_count("SELECT COUNT(*) FROM courses"),
    'published' => admin_count("SELECT COUNT(*) FROM courses WHERE status='published'"),
    'draft'     => admin_count("SELECT COUNT(*) FROM courses WHERE status='draft'"),
    'archived'  => admin_count("SELECT COUNT(*) FROM courses WHERE status='archived'"),
];

require __DIR__ . '/includes/header.php';
?>

<!-- Page header -->
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
  <div>
    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Courses</h2>
    <p class="mt-1 text-sm text-slate-500">Create, edit and manage all training courses.</p>
  </div>
  <button type="button" data-course-open class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold shadow-lg shadow-primary/20 hover:-translate-y-0.5 transition whitespace-nowrap">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
    Add Course
  </button>
</div>

<!-- Summary stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
  <?php
  $statChips = [
    ['Total', $cStats['total'], 'text-slate-900 dark:text-white', 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300', '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>'],
    ['Published', $cStats['published'], 'text-emerald-600 dark:text-emerald-400', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400', '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>'],
    ['Drafts', $cStats['draft'], 'text-amber-600 dark:text-amber-400', 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400', '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>'],
    ['Archived', $cStats['archived'], 'text-slate-500', 'bg-slate-100 dark:bg-white/10 text-slate-500', '<rect x="2" y="4" width="20" height="5" rx="1"/><path d="M4 9v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9M10 13h4"/>'],
  ];
  foreach ($statChips as [$lbl, $num, $numCls, $iconCls, $path]): ?>
    <div class="flex items-center gap-3 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-4 shadow-sm">
      <span class="grid place-items-center h-10 w-10 rounded-xl <?= $iconCls ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><?= $path ?></svg>
      </span>
      <div>
        <p class="text-xl font-extrabold <?= $numCls ?>"><?= number_format($num) ?></p>
        <p class="text-xs text-slate-400"><?= $lbl ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Filter bar -->
<div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
  <div class="flex items-center gap-1 rounded-xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-1 shadow-sm">
    <?php
    $tabs = ['' => 'All', 'published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'];
    foreach ($tabs as $tk => $tl):
      $href = admin_url('courses.php') . ($tk ? '?status=' . $tk : '') . ($q ? ($tk ? '&' : '?') . 'q=' . urlencode($q) : '');
      $on = $status === $tk; ?>
      <a href="<?= e($href) ?>" class="px-3.5 py-1.5 rounded-lg text-sm font-semibold transition <?= $on ? 'bg-gradient-to-r from-primary to-secondary text-white shadow' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white' ?>"><?= e($tl) ?></a>
    <?php endforeach; ?>
  </div>
  <form method="get" class="flex gap-2 flex-1 sm:max-w-md sm:ml-auto">
    <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <div class="field relative flex-1">
      <span class="field-icon absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg></span>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search courses or trainer..." class="form-input pl-10">
    </div>
    <button class="px-4 rounded-xl bg-slate-800 dark:bg-white/10 text-white text-sm font-semibold hover:bg-slate-900 transition">Search</button>
  </form>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-slate-400 bg-slate-50 dark:bg-white/5">
        <th class="px-5 py-3 font-semibold">Course</th>
        <th class="px-5 py-3 font-semibold">Category</th>
        <th class="px-5 py-3 font-semibold">Status</th>
        <th class="px-5 py-3 font-semibold">Apps</th>
        <th class="px-5 py-3 font-semibold text-right">Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach ($courses as $c):
          $appCount = admin_count("SELECT COUNT(*) FROM applications WHERE course_id=?", [$c['id']]); ?>
          <tr class="border-t border-slate-100 dark:border-white/5 hover:bg-slate-50/50 dark:hover:bg-white/5">
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <span class="h-10 w-10 rounded-lg bg-gradient-to-br from-primary to-secondary text-white grid place-items-center font-bold shrink-0"><?= e(mb_substr($c['title'],0,1)) ?></span>
                <div class="min-w-0">
                  <p class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[220px] flex items-center gap-1.5">
                    <?= e($c['title']) ?>
                    <?php if ($c['is_featured']): ?><span class="text-accent" title="Featured">&#9733;</span><?php endif; ?>
                  </p>
                  <p class="text-xs text-slate-400"><?= e($c['trainer'] ?? '') ?></p>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 text-slate-500"><?= e($c['category_name'] ?? '-') ?></td>
            <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= status_badge($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
            <td class="px-5 py-4"><a href="<?= e(admin_url('applications.php?course=' . $c['id'])) ?>" class="font-semibold text-primary dark:text-secondary"><?= $appCount ?></a></td>
            <td class="px-5 py-4">
              <div class="flex items-center justify-end gap-1">
                <a href="<?= e(admin_url('courses.php?edit=' . $c['id'])) ?>" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 text-slate-500" title="Edit">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                <a href="<?= e(url('course/' . $c['slug'])) ?>" target="_blank" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 text-slate-500" title="View">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <div class="relative group">
                  <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 text-slate-500"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></button>
                  <div class="hidden group-hover:block absolute right-0 top-full z-10 w-44 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 shadow-xl py-1.5">
                    <?php
                    $menu = [];
                    if ($c['status'] !== 'published') $menu[] = ['publish','Publish'];
                    if ($c['status'] !== 'draft') $menu[] = ['draft','Set as draft'];
                    if ($c['status'] !== 'archived') $menu[] = ['archive','Archive'];
                    $menu[] = ['feature', $c['is_featured'] ? 'Unfeature' : 'Feature'];
                    foreach ($menu as [$act,$lbl]): ?>
                      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $act ?>"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5"><?= $lbl ?></button>
                      </form>
                    <?php endforeach; ?>
                    <form method="post" class="border-t border-slate-100 dark:border-white/5"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button data-confirm="Delete this course permanently?" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                    </form>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$courses): ?><tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No courses found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= pagination_links($p, $pagBase) ?>

<!-- ============ COURSE FORM MODAL (blurred backdrop) ============ -->
<?php
// Prepare variables for the shared form partial
$course = $editCourse;          // null = new course
$id     = $editCourse['id'] ?? 0;
$val    = fn($k, $d = '') => e((string)($course[$k] ?? $d));
?>
<div id="courseModal" class="fixed inset-0 z-50 <?= $openForm ? 'flex' : 'hidden' ?> items-start justify-center p-4 sm:p-6 overflow-y-auto">
  <!-- Blurred backdrop -->
  <div data-course-close class="fixed inset-0 bg-slate-900/40 backdrop-blur-md"></div>

  <!-- Modal panel -->
  <div id="courseModalPanel" class="relative w-full max-w-5xl my-4 rounded-3xl bg-[#F1F5F9] dark:bg-dark border border-white/40 dark:border-white/10 shadow-2xl ring-1 ring-black/5">
    <!-- Sticky header -->
    <div class="sticky top-0 z-10 flex items-center justify-between gap-3 px-6 py-4 rounded-t-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur border-b border-slate-200 dark:border-white/10">
      <div>
        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white"><?= $editCourse ? 'Edit Course' : 'Add New Course' ?></h3>
        <p class="text-xs text-slate-500"><?= $editCourse ? e($editCourse['title']) : 'Fill in the details to create a course.' ?></p>
      </div>
      <button type="button" data-course-close class="grid place-items-center h-9 w-9 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Form body -->
    <form method="post" action="<?= e(admin_url('course-form.php')) ?>" enctype="multipart/form-data" class="p-6">
      <?= csrf_field() ?>
      <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

      <?php $submitLabel = $editCourse ? 'Save Changes' : 'Create Course'; require __DIR__ . '/includes/_course_form_fields.php'; ?>
    </form>
  </div>
</div>

<style>
  @keyframes courseModalIn { from { opacity: 0; transform: translateY(24px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
  #courseModal:not(.hidden) #courseModalPanel { animation: courseModalIn .35s cubic-bezier(.2,.7,.2,1); }
  @media (prefers-reduced-motion: reduce) { #courseModal #courseModalPanel { animation: none; } }
</style>

<script>
(function () {
  const modal = document.getElementById('courseModal');
  if (!modal) return;
  const body = document.body;
  function openModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    body.style.overflow = 'hidden';
  }
  function closeModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    body.style.overflow = '';
    // Clean ?edit / ?new from URL without reloading
    const url = new URL(window.location.href);
    if (url.searchParams.has('edit') || url.searchParams.has('new')) {
      url.searchParams.delete('edit');
      url.searchParams.delete('new');
      window.history.replaceState({}, '', url.pathname + (url.search ? url.search : ''));
    }
  }
  document.querySelectorAll('[data-course-open]').forEach((b) => b.addEventListener('click', openModal));
  document.querySelectorAll('[data-course-close]').forEach((b) => b.addEventListener('click', closeModal));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
  // Lock scroll if opened on load (edit/new)
  if (!modal.classList.contains('hidden')) body.style.overflow = 'hidden';
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
