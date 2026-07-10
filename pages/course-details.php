<?php
if (!defined('ROOT_PATH')) { require_once dirname(__DIR__) . '/config/config.php'; }

$slug = $routeParam ?? (string)input('slug', '');
$slug = preg_replace('#[^a-zA-Z0-9\-_]#', '', (string)$slug);

$course = $slug ? db_one(
    "SELECT c.*, cat.name AS category_name, cat.color AS category_color, cat.slug AS category_slug
     FROM courses c LEFT JOIN categories cat ON cat.id = c.category_id
     WHERE c.slug = ? AND c.status = 'published' LIMIT 1",
    [$slug]
) : null;

if (!$course) {
    http_response_code(404);
    require PAGES_PATH . '/404.php';
    return;
}

// Increment views (best-effort)
try { db_exec('UPDATE courses SET views = views + 1 WHERE id = ?', [$course['id']]); } catch (Throwable $e) {}

$pageTitle = $course['title'];
$activeNav = 'courses';
$metaDesc  = excerpt($course['short_description'] ?: $course['description'] ?: $course['title'], 155);

$listBlock = function (?string $text): array {
    return array_filter(array_map('trim', explode("\n", (string)$text)));
};

$objectives   = $listBlock($course['objectives']);
$requirements = $listBlock($course['requirements']);
$benefits     = $listBlock($course['benefits']);
$outcomes     = $listBlock($course['outcomes']);
$requiredDocs = array_filter(array_map('trim', explode(',', (string)($course['required_documents'] ?? ''))));

$related = db_all(
    "SELECT c.*, cat.name AS category_name, cat.color AS category_color
     FROM courses c LEFT JOIN categories cat ON cat.id = c.category_id
     WHERE c.status='published' AND c.id <> ? AND (c.category_id = ? OR ? IS NULL)
     ORDER BY RAND() LIMIT 3",
    [$course['id'], $course['category_id'], $course['category_id']]
);

require INCLUDES_PATH . '/header.php';

$img = course_image($course);

// Professional line-icon set (SVG inner paths)
$icons = [
    'clock'    => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.8"/>',
    'pin'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/>',
    'device'   => '<rect x="4" y="3" width="16" height="13" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 20h20M9 16v4m6-4v4"/>',
    'calendar' => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3v4M8 3v4M3.5 10h17"/>',
    'flag'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 21V4m0 0h11l-2 4 2 4H4"/>',
    'users'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a4 4 0 00-4-4h-1m-6 5H2v-1a4 4 0 014-4h4a4 4 0 014 4v1zm-1-9a3 3 0 11-6 0 3 3 0 016 0zm7-2a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>',
    'schedule' => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 1.5"/>',
    'award'    => '<circle cx="12" cy="8" r="5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5L7 21l5-2.5L17 21l-1.5-8.5"/>',
    'book'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5C10.5 5.5 8.5 5 6.5 5S3 5.5 3 5.5v13s2-.5 3.5-.5 3.5.5 5.5 1.5m0-13c1.5-1 3.5-1.5 5.5-1.5S21 5.5 21 5.5v13s-2-.5-3.5-.5-4 .5-5.5 1.5m0-13v13"/>',
    'check'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
    'check-c'  => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.5 2.5 4.5-5"/>',
    'target'   => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
    'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.8 4.2L18 9l-4.2 1.8L12 15l-1.8-4.2L6 9l4.2-1.8L12 3zM18 14l.9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9L18 14z"/>',
    'clipboard'=> '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
];
$svg = function (string $name, string $cls = 'h-5 w-5') use ($icons): string {
    $p = $icons[$name] ?? $icons['clock'];
    return '<svg class="' . $cls . '" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">' . $p . '</svg>';
};

// Banner meta chips
$chips = [];
if (!empty($course['duration']))    $chips[] = ['clock', $course['duration']];
if (!empty($course['location']))    $chips[] = ['pin', $course['location']];
if (!empty($course['start_date']))  $chips[] = ['calendar', format_date($course['start_date'])];
?>

<!-- BANNER -->
<section class="relative -mt-24 overflow-hidden">
  <!-- Background image -->
  <div class="absolute inset-0 -z-10">
    <img src="<?= e($img ?: asset('images/hero-bg.png')) ?>" alt="" aria-hidden="true"
         class="h-full w-full object-cover object-center scale-105">
    <div class="absolute inset-0 bg-gradient-to-t from-dark via-dark/85 to-dark/60"></div>
    <div class="absolute inset-0 bg-dark/30"></div>
    <div class="absolute -top-20 right-1/4 h-72 w-72 rounded-full bg-secondary/20 blur-3xl"></div>
  </div>

  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-36 pb-14">
    <!-- Breadcrumb -->
    <nav class="animate-in flex items-center gap-2 text-sm text-white/60 mb-5" style="animation-delay:.05s">
      <a href="<?= url('') ?>" class="hover:text-white transition">Home</a>
      <span>/</span>
      <a href="<?= url('courses') ?>" class="hover:text-white transition">Courses</a>
      <span>/</span>
      <span class="text-white/90 truncate max-w-[200px]"><?= e($course['title']) ?></span>
    </nav>

    <span class="animate-in inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold text-white shadow-lg" style="animation-delay:.1s;background:<?= e($course['category_color'] ?? '#B11314') ?>">
      <?= $svg('book', 'h-3.5 w-3.5') ?><?= e($course['category_name'] ?? 'General') ?>
    </span>

    <h1 class="animate-in mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white max-w-3xl leading-tight" style="animation-delay:.18s"><?= e($course['title']) ?></h1>

    <?php if (!empty($course['short_description'])): ?>
      <p class="animate-in mt-4 text-lg text-white/80 max-w-2xl leading-relaxed" style="animation-delay:.26s"><?= e($course['short_description']) ?></p>
    <?php endif; ?>

    <?php if ($chips): ?>
      <div class="animate-in mt-7 flex flex-wrap gap-2.5" style="animation-delay:.34s">
        <?php foreach ($chips as [$ic, $val]): ?>
          <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 backdrop-blur border border-white/15 text-white text-sm font-medium">
            <span class="text-secondary"><?= $svg($ic, 'h-4 w-4') ?></span><?= e((string)$val) ?>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 grid lg:grid-cols-3 gap-10">
  <!-- MAIN -->
  <div class="lg:col-span-2 space-y-10">
    <?php if ($course['description']): ?>
    <div class="reveal rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 sm:p-8 shadow-sm">
      <div class="flex items-center gap-3 mb-5">
        <span class="grid place-items-center h-11 w-11 rounded-xl bg-primary/10 text-primary dark:text-secondary shrink-0">
          <?= $svg('book', 'h-6 w-6') ?>
        </span>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Course Overview</h2>
      </div>
      <div class="max-w-none text-slate-600 dark:text-slate-300 leading-relaxed text-[15px] whitespace-pre-line"><?= e($course['description']) ?></div>
    </div>
    <?php endif; ?>

    <?php
    $blocks = [
        ['Learning Objectives', $objectives, 'target',    'text-primary dark:text-secondary', 'bg-primary/10', 'text-primary dark:text-secondary'],
        ['What You Will Learn', $outcomes,    'sparkles',  'text-accent',                      'bg-accent/10',  'text-accent'],
        ['Benefits',            $benefits,    'check-c',   'text-emerald-500',                 'bg-emerald-500/10', 'text-emerald-500'],
        ['Requirements',        $requirements,'clipboard', 'text-slate-500',                   'bg-slate-500/10', 'text-slate-500'],
        ['Required Documents',  $requiredDocs,'clipboard', 'text-indigo-500',                  'bg-indigo-500/10', 'text-indigo-500'],
    ];
    foreach ($blocks as [$title, $items, $hicon, $hcolor, $hbg, $bullet]):
        if (!$items) continue; ?>
      <div class="reveal rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
          <span class="grid place-items-center h-11 w-11 rounded-xl <?= $hbg ?> <?= $hcolor ?> shrink-0"><?= $svg($hicon, 'h-6 w-6') ?></span>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white"><?= $title ?></h3>
        </div>
        <ul class="grid sm:grid-cols-2 gap-x-6 gap-y-3">
          <?php foreach ($items as $it): ?>
            <li class="flex items-start gap-2.5 text-slate-600 dark:text-slate-300">
              <span class="<?= $bullet ?> shrink-0 mt-0.5"><?= $svg('check', 'h-5 w-5') ?></span>
              <span class="text-[15px]"><?= e($it) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>

    <?php if (!empty($course['trainer'])): ?>
    <div class="reveal rounded-2xl bg-gradient-to-br from-primary/5 to-secondary/5 dark:from-white/5 dark:to-white/5 border border-slate-100 dark:border-white/5 p-6 sm:p-8">
      <div class="flex items-center gap-3 mb-5">
        <span class="grid place-items-center h-11 w-11 rounded-xl bg-secondary/15 text-secondary shrink-0"><?= $svg('users', 'h-6 w-6') ?></span>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Your Trainer</h3>
      </div>
      <div class="flex items-start gap-4">
        <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-primary to-secondary text-white grid place-items-center text-2xl font-extrabold shrink-0"><?= e(mb_substr($course['trainer'],0,1)) ?></div>
        <div>
          <p class="font-bold text-slate-900 dark:text-white"><?= e($course['trainer']) ?></p>
          <?php if (!empty($course['trainer_bio'])): ?><p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?= e($course['trainer_bio']) ?></p><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- SIDEBAR -->
  <aside class="lg:col-span-1">
    <div class="sticky top-28 space-y-4">
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-primary to-secondary px-6 py-5">
          <h3 class="font-bold text-white text-lg">Course Details</h3>
          <p class="text-white/75 text-xs mt-0.5">Everything you need to know</p>
        </div>
        <div class="p-6">
          <dl class="space-y-1">
            <?php
            $details = [
                ['clock',    'Duration',    $course['duration']],
                ['pin',      'Location',    $course['location']],
                ['calendar', 'Start Date',  format_date($course['start_date'])],
                ['flag',     'End Date',    format_date($course['end_date'])],
                ['users',    'Seats',       (int)$course['seats_available'] > 0 ? $course['seats_available'] . ' available' : 'Limited'],
                ['schedule', 'Schedule',    $course['schedule']],
                ['clock',    'Session',     $course['session_time'] ?? ''],
                ['award',    'Certificate', $course['certificate']],
            ];
            foreach ($details as [$ic, $label, $value]):
                if (empty($value) || $value === '-') continue; ?>
              <div class="flex items-center gap-3 py-3 border-b border-slate-100 dark:border-white/5 last:border-0">
                <span class="grid place-items-center h-9 w-9 rounded-lg bg-primary/10 text-primary dark:text-secondary shrink-0"><?= $svg($ic, 'h-5 w-5') ?></span>
                <div class="min-w-0 flex-1">
                  <dt class="text-xs text-slate-400 leading-none mb-0.5"><?= e($label) ?></dt>
                  <dd class="font-semibold text-slate-800 dark:text-slate-200 text-sm"><?= e((string)$value) ?></dd>
                </div>
              </div>
            <?php endforeach; ?>
          </dl>
          <?php
          $isClosed   = deadline_passed($course['registration_deadline'] ?? null);
          $hasApplied = has_applied((int)$course['id']);
          if ($isClosed): ?>
            <div class="mt-6 flex items-center justify-center gap-2 py-3.5 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-semibold border border-red-100 dark:border-red-500/20">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M15 9l-6 6M9 9l6 6"/></svg>
              Registration Closed
            </div>
            <p class="mt-3 text-xs text-center text-slate-400">The deadline for this course has passed.</p>
          <?php elseif ($hasApplied): ?>
            <div class="mt-6 flex items-center justify-center gap-2 py-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold border border-emerald-100 dark:border-emerald-500/20">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              You have already applied
            </div>
            <p class="mt-3 text-xs text-center text-slate-400">We received your application on this device.</p>
          <?php else: ?>
            <a href="<?= e(url('apply/' . $course['slug'])) ?>" class="mt-6 flex items-center justify-center gap-2 py-3.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold shadow-lg shadow-primary/25 hover:-translate-y-0.5 transition">
              Apply Now
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <p class="mt-3 text-xs text-center text-slate-400">No account required &middot; Free to apply</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </aside>
</section>

<!-- RELATED -->
<?php if ($related): ?>
<section class="bg-white dark:bg-slate-900/40 py-16 border-t border-slate-100 dark:border-white/5">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-8">Related Courses</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($related as $c) { echo render_course_card($c); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require INCLUDES_PATH . '/footer.php'; ?>
