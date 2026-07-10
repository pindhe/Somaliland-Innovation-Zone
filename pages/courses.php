<?php
if (!defined('ROOT_PATH')) { require_once dirname(__DIR__) . '/config/config.php'; }

$pageTitle = 'Courses';
$activeNav = 'courses';
$metaDesc  = 'Browse innovation programs, bootcamps, workshops and training courses at Somaliland Innovation Zone.';

// Filters
$q          = trim((string)input('q', ''));
$categorySl = trim((string)input('category', ''));
$page       = max(1, (int)input('page', 1));

$where  = ["c.status = 'published'"];
$params = [];

if ($q !== '') {
    $where[] = '(c.title LIKE ? OR c.short_description LIKE ? OR c.trainer LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($categorySl !== '') {
    $where[] = 'cat.slug = ?';
    $params[] = $categorySl;
}
$whereSql = implode(' AND ', $where);

$baseSql  = "SELECT c.*, cat.name AS category_name, cat.color AS category_color, cat.slug AS category_slug
             FROM courses c LEFT JOIN categories cat ON cat.id = c.category_id
             WHERE $whereSql ORDER BY c.created_at DESC, c.id DESC";
$countSql = "SELECT COUNT(*) FROM courses c LEFT JOIN categories cat ON cat.id = c.category_id WHERE $whereSql";

$p = paginate($baseSql, $countSql, $params, $page, 9);
$courses = $p['items'];

$categories = db_all("SELECT * FROM categories WHERE status='active' ORDER BY name ASC");

// Build base URL preserving filters for pagination
$qsParts = array_filter([
    'q' => $q ?: null,
    'category' => $categorySl ?: null,
]);
$pagBase = url('courses') . ($qsParts ? '?' . http_build_query($qsParts) : '');

require INCLUDES_PATH . '/header.php';
?>

<!-- HEADER -->
<section class="relative -mt-24 overflow-hidden">
  <!-- Background image -->
  <div class="absolute inset-0 -z-10">
    <img src="<?= asset('images/contactbg.jpg') ?>" alt="SIZ graduates with certificates" aria-hidden="true"
         class="h-full w-full object-cover object-center scale-105 animate-[heroZoom_22s_ease-in-out_infinite_alternate]">
    <div class="absolute inset-0 bg-gradient-to-b from-dark/85 via-dark/75 to-dark/90"></div>
    <div class="absolute inset-0 bg-dark/30"></div>
    <div class="absolute -top-24 right-1/4 h-80 w-80 rounded-full bg-secondary/20 blur-3xl"></div>
    <div class="absolute bottom-0 left-1/4 h-72 w-72 rounded-full bg-primary/20 blur-3xl"></div>
  </div>

  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-40 pb-24 text-center text-white">
    <span class="animate-in inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 backdrop-blur text-secondary text-sm font-bold uppercase tracking-widest" style="animation-delay:.05s">
      <span class="h-1.5 w-1.5 rounded-full bg-secondary"></span> Our Programs
    </span>
    <h1 class="animate-in mt-5 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight" style="animation-delay:.15s">Explore Our Programs</h1>
    <p class="animate-in mt-5 text-lg text-slate-200/90 max-w-2xl mx-auto leading-relaxed" style="animation-delay:.25s">Find the perfect course to grow your skills and launch your career.</p>
  </div>

  <!-- Wave divider -->
  <svg class="block w-full text-slate-50 dark:text-dark" viewBox="0 0 1440 80" preserveAspectRatio="none" style="height:60px"><path fill="currentColor" d="M0 80V40c180 30 360 30 540 10S900 10 1080 20s270 25 360 20v40z"/></svg>
</section>

<!-- FILTERS -->
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
  <form method="get" action="<?= url('courses') ?>" class="animate-in rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-sm p-4 grid gap-3 md:grid-cols-12 items-end" style="animation-delay:.35s">
    <div class="md:col-span-6">
      <label class="form-label">Search</label>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Course name, trainer..." class="form-input">
    </div>
    <div class="md:col-span-3">
      <label class="form-label">Category</label>
      <select name="category" class="form-select">
        <option value="">All categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= e($cat['slug']) ?>" <?= $categorySl === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="md:col-span-3 flex gap-2">
      <button class="flex-1 py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold hover:shadow-lg transition">Filter</button>
      <a href="<?= url('courses') ?>" class="grid place-items-center px-4 rounded-xl border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300" title="Reset" aria-label="Reset filters">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M19.5 9A7.5 7.5 0 006 5.5L4 9m0 6l2 3.5A7.5 7.5 0 0019.5 15"/></svg>
      </a>
    </div>
  </form>
</section>

<!-- RESULTS -->
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-20">
  <p class="animate-in text-sm text-slate-500 mb-6" style="animation-delay:.45s"><strong class="text-slate-800 dark:text-slate-200"><?= (int)$p['total'] ?></strong> course<?= $p['total'] == 1 ? '' : 's' ?> found</p>
  <?php if ($courses): ?>
    <div id="coursesGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($courses as $c) { echo render_course_card($c); } ?>
    </div>
    <?= pagination_links($p, $pagBase) ?>
  <?php else: ?>
    <div class="animate-in text-center py-20" style="animation-delay:.45s">
      <div class="mx-auto mb-4 grid place-items-center h-20 w-20 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-400">
        <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/></svg>
      </div>
      <h3 class="text-xl font-bold text-slate-900 dark:text-white">No courses found</h3>
      <p class="mt-2 text-slate-500">Try adjusting your search or filters.</p>
      <a href="<?= url('courses') ?>" class="mt-6 inline-flex px-6 py-3 rounded-xl bg-primary text-white font-semibold">View all courses</a>
    </div>
  <?php endif; ?>
</section>

<script>
(function () {
  // Staggered entrance for course cards (resets delay after so hover stays snappy)
  var grid = document.getElementById('coursesGrid');
  if (!grid) return;
  var cards = grid.querySelectorAll('.reveal');
  var cols = window.matchMedia('(min-width:1024px)').matches ? 3 : (window.matchMedia('(min-width:640px)').matches ? 2 : 1);
  cards.forEach(function (card, i) {
    var delay = (i % cols) * 90 + Math.floor(i / cols) * 60;
    card.style.transitionDelay = delay + 'ms';
    card.addEventListener('transitionend', function () { card.style.transitionDelay = '0s'; }, { once: true });
  });
})();
</script>

<?php require INCLUDES_PATH . '/footer.php'; ?>
