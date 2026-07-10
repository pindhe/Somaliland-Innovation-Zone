<?php
if (!defined('ROOT_PATH')) { require_once dirname(__DIR__) . '/config/config.php'; }

$pageTitle = 'Home';
$activeNav = 'home';

// Latest courses the admin creates show first (last in, first out).
$featured = db_all(
    "SELECT c.*, cat.name AS category_name, cat.color AS category_color
     FROM courses c LEFT JOIN categories cat ON cat.id = c.category_id
     WHERE c.status = 'published'
     ORDER BY c.created_at DESC, c.id DESC LIMIT 4"
);

$upcoming = db_all(
    "SELECT c.*, cat.name AS category_name, cat.color AS category_color
     FROM courses c LEFT JOIN categories cat ON cat.id = c.category_id
     WHERE c.status = 'published' AND c.start_date >= CURDATE()
     ORDER BY c.start_date ASC LIMIT 4"
);
$testimonials = db_all("SELECT * FROM testimonials WHERE status='active' ORDER BY created_at DESC LIMIT 6");
$faqs = db_all("SELECT * FROM faqs WHERE status='active' ORDER BY sort_order ASC LIMIT 6");

// Dynamic join counter — every new device that enters counts as "1 person joining".
$joined = track_visitor();

require INCLUDES_PATH . '/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="relative -mt-24 min-h-[100svh] flex items-center justify-center overflow-hidden">
  <!-- Background image -->
  <div class="absolute inset-0 -z-10">
    <img src="<?= asset('images/hero-bg.png') ?>" alt="" aria-hidden="true"
         class="h-full w-full object-cover object-center scale-105 animate-[heroZoom_20s_ease-in-out_infinite_alternate]">
    <!-- Readability overlays -->
    <div class="absolute inset-0 bg-gradient-to-b from-dark/80 via-dark/60 to-dark/90"></div>
    <div class="absolute inset-0 bg-dark/40"></div>
    <!-- Glow accents -->
    <div class="absolute -top-24 left-1/4 h-96 w-96 rounded-full bg-secondary/20 blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 h-80 w-80 rounded-full bg-accent/10 blur-3xl"></div>
  </div>

  <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pt-28 pb-20 text-center">
    <span class="reveal is-visible inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-white text-sm font-semibold">
      <span class="h-2 w-2 rounded-full bg-secondary animate-pulse"></span>
      Somaliland Innovation Zone
    </span>

    <h1 class="reveal is-visible mt-6 text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white leading-[1.05]">
      Somaliland <span class="bg-gradient-to-r from-secondary to-accent bg-clip-text text-transparent">Innovation Zone</span>
    </h1>

    <p class="reveal is-visible mt-6 text-lg sm:text-xl text-slate-200/90 max-w-2xl mx-auto leading-relaxed">
      <?= e(cms('hero_subtitle', 'Discover world-class training programs, bootcamps and internships. Learn the skills that shape the future.')) ?>
    </p>

    <div class="reveal is-visible mt-9 flex justify-center">
      <a href="<?= url('courses') ?>" class="group inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-gradient-to-r from-primary to-secondary text-white text-lg font-semibold shadow-2xl shadow-primary/30 hover:-translate-y-0.5 hover:shadow-primary/50 transition">
        Explore
        <svg class="h-5 w-5 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>

    <!-- Dynamic join counter (real live data) -->
    <div class="reveal is-visible mt-12 inline-flex items-center gap-3 rounded-full border border-white/15 bg-white/10 backdrop-blur px-5 py-2.5 mx-auto text-sm text-slate-100">
      <span class="relative flex h-2.5 w-2.5">
        <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
      </span>
      <div class="flex -space-x-2">
        <?php foreach (['primary','secondary','accent','success'] as $cName): ?>
          <span class="h-7 w-7 rounded-full ring-2 ring-dark bg-<?= $cName ?>"></span>
        <?php endforeach; ?>
      </div>
      <p>Join <strong class="text-white font-extrabold counter" data-target="<?= (int)$joined ?>"><?= number_format((int)$joined) ?></strong><span class="text-white font-extrabold">+</span> learners building their future</p>
    </div>
  </div>

  <!-- Scroll cue -->
  <a href="#featured" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 hover:text-white transition animate-bounce" aria-label="Scroll down">
    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
  </a>
</section>

<!-- ===================== FEATURED COURSES ===================== -->
<section id="featured" class="relative scroll-mt-24 py-20 lg:py-24 overflow-hidden">
  <div class="absolute inset-0 -z-10">
    <div class="absolute top-10 left-1/4 h-72 w-72 rounded-full bg-primary/5 blur-3xl"></div>
    <div class="absolute bottom-10 right-1/4 h-72 w-72 rounded-full bg-accent/5 blur-3xl"></div>
  </div>

  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="reveal text-center max-w-2xl mx-auto mb-14">
      <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/10 text-primary dark:text-secondary text-sm font-bold uppercase tracking-widest">
        <span class="h-1.5 w-1.5 rounded-full bg-secondary animate-pulse"></span> Newest Programs
      </span>
      <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">Latest Courses</h2>
      <p class="mt-4 text-lg text-slate-600 dark:text-slate-300">Our most recently added programs &mdash; fresh opportunities to launch your career in technology and innovation.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($featured as $c) { echo render_course_card($c); } ?>
      <?php if (!$featured): ?>
        <div class="col-span-full text-center py-12">
          <div class="mx-auto mb-3 grid place-items-center h-16 w-16 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-400">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5C10.5 5.5 8.5 5 6.5 5S3 5.5 3 5.5v13s2-.5 3.5-.5 3.5.5 5.5 1.5m0-13c1.5-1 3.5-1.5 5.5-1.5S21 5.5 21 5.5v13s-2-.5-3.5-.5-4 .5-5.5 1.5m0-13v13"/></svg>
          </div>
          <p class="text-slate-500">No courses available yet. Check back soon!</p>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($featured): ?>
    <div class="reveal mt-14 text-center">
      <a href="<?= url('courses') ?>" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-white dark:bg-slate-800 border-2 border-primary/20 dark:border-white/10 text-primary dark:text-secondary font-bold hover:bg-primary hover:text-white hover:border-primary shadow-sm hover:shadow-lg hover:shadow-primary/20 transition-all">
        More Courses
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===================== WHY CHOOSE US ===================== -->
<section class="bg-white dark:bg-slate-900/40 py-20 lg:py-24 border-y border-slate-100 dark:border-white/5">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="reveal text-center max-w-2xl mx-auto mb-14">
      <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/10 text-primary dark:text-secondary text-sm font-bold uppercase tracking-widest">
        <span class="h-1.5 w-1.5 rounded-full bg-secondary"></span> Why SIZ
      </span>
      <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">Why learn with us</h2>
      <p class="mt-4 text-lg text-slate-600 dark:text-slate-300">Everything you need to gain real, job-ready skills and build your future.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $features = [
          ['from-sky-500 to-blue-600',     'Practical Training',  'Hands-on, project-based learning that mirrors real industry work.',   'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
          ['from-emerald-500 to-teal-600', 'Expert Trainers',     'Learn directly from experienced mentors and industry professionals.', 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42M12 14v7m0 0a6 6 0 01-6-6m6 6a6 6 0 006-6'],
          ['from-primary to-secondary',    'Recognized Certificate','Earn a certificate that validates your skills to employers.',       'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
          ['from-amber-500 to-orange-600', 'Career Support',      'Get guidance, internships and opportunities after you graduate.',     'M17 20h5v-1a4 4 0 00-4-4h-1m-6 5H2v-1a4 4 0 014-4h4a4 4 0 014 4v1zm-1-9a3 3 0 11-6 0 3 3 0 016 0zm7-2a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
      ];
      foreach ($features as [$grad, $title, $desc, $icon]): ?>
        <div class="reveal group rounded-2xl bg-[#F8FAFC] dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-7 hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
          <div class="grid place-items-center h-14 w-14 rounded-2xl bg-gradient-to-br <?= $grad ?> text-white shadow-lg group-hover:scale-110 transition">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/></svg>
          </div>
          <h3 class="mt-5 font-bold text-lg text-slate-900 dark:text-white"><?= $title ?></h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?= $desc ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== UPCOMING ===================== -->
<?php if ($upcoming): ?>
<section class="bg-white dark:bg-slate-900/40 py-20 border-y border-slate-100 dark:border-white/5">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="reveal text-center max-w-2xl mx-auto mb-12">
      <span class="text-sm font-bold uppercase tracking-widest text-primary dark:text-secondary">Don't miss out</span>
      <h2 class="mt-2 text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white">Upcoming Trainings</h2>
    </div>
    <div class="space-y-4">
      <?php foreach ($upcoming as $c): ?>
        <a href="<?= e(url('course/' . $c['slug'])) ?>" class="reveal group flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl bg-[#F8FAFC] dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 hover:shadow-lg transition">
          <div class="shrink-0 grid place-items-center h-16 w-16 rounded-2xl bg-primary/10 text-primary dark:text-secondary">
            <div class="text-center leading-none">
              <div class="text-xl font-extrabold"><?= e(format_date($c['start_date'],'d')) ?></div>
              <div class="text-[10px] uppercase font-bold"><?= e(format_date($c['start_date'],'M')) ?></div>
            </div>
          </div>
          <div class="flex-1">
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-white" style="background:<?= e($c['category_color'] ?? '#B11314') ?>"><?= e($c['category_name'] ?? 'General') ?></span>
            <h3 class="mt-1.5 font-bold text-slate-900 dark:text-white group-hover:text-primary transition"><?= e($c['title']) ?></h3>
            <p class="text-sm text-slate-500"><?= e($c['location'] ?: 'Flexible') ?> &middot; <?= e($c['duration']) ?></p>
          </div>
          <span class="inline-flex items-center gap-1 text-primary dark:text-secondary font-semibold text-sm">Learn more &rarr;</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== FAQ ===================== -->
<?php if ($faqs): ?>
<section class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-20">
  <div class="reveal text-center mb-10">
    <span class="text-sm font-bold uppercase tracking-widest text-primary dark:text-secondary">FAQ</span>
    <h2 class="mt-2 text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white">Frequently Asked Questions</h2>
  </div>
  <div class="space-y-3">
    <?php foreach ($faqs as $f): ?>
      <div class="faq-item rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 overflow-hidden">
        <button type="button" class="faq-q w-full flex items-center justify-between gap-4 p-5 text-left font-semibold text-slate-900 dark:text-white">
          <span><?= e($f['question']) ?></span>
          <svg class="faq-icon h-5 w-5 shrink-0 text-primary transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="faq-a hidden px-5 pb-5 text-slate-600 dark:text-slate-400 text-sm leading-relaxed"><?= e($f['answer']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ===================== HOW TO APPLY ===================== -->
<section class="bg-white dark:bg-slate-900/40 py-20 lg:py-24 border-y border-slate-100 dark:border-white/5">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="reveal text-center max-w-2xl mx-auto mb-14">
      <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/10 text-primary dark:text-secondary text-sm font-bold uppercase tracking-widest">
        <span class="h-1.5 w-1.5 rounded-full bg-secondary"></span> Get Started
      </span>
      <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">How to Apply</h2>
      <p class="mt-4 text-lg text-slate-600 dark:text-slate-300">Join a program in three simple steps &mdash; no account required.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6 relative">
      <?php
      $steps = [
          ['01', 'Choose a Course',   'Browse our programs and pick the one that matches your goals.', 'M21 21l-4.3-4.3M11 18a7 7 0 100-14 7 7 0 000 14z'],
          ['02', 'Fill the Form',     'Complete a quick multi-step application with your details.',     'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-9.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z'],
          ['03', 'Get Accepted',      'Our team reviews your application and gets back to you fast.',   'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
      ];
      foreach ($steps as $i => [$num, $title, $desc, $icon]): ?>
        <div class="reveal relative rounded-2xl bg-[#F8FAFC] dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-7 text-center">
          <span class="absolute top-5 right-6 text-5xl font-extrabold text-slate-100 dark:text-white/5 select-none"><?= $num ?></span>
          <div class="relative mx-auto grid place-items-center h-16 w-16 rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/></svg>
          </div>
          <h3 class="relative mt-5 font-bold text-lg text-slate-900 dark:text-white"><?= $title ?></h3>
          <p class="relative mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?= $desc ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== FINAL CTA ===================== -->
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
  <div class="reveal relative overflow-hidden rounded-3xl bg-dark text-white p-10 lg:p-16 text-center">
    <img src="<?= asset('images/hero-bg.png') ?>" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full object-cover opacity-20">
    <div class="absolute -top-20 -left-16 h-72 w-72 rounded-full bg-primary/40 blur-3xl"></div>
    <div class="absolute -bottom-20 -right-16 h-72 w-72 rounded-full bg-secondary/30 blur-3xl"></div>
    <div class="relative">
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">Ready to start your journey?</h2>
      <p class="mt-4 text-lg text-slate-300 max-w-xl mx-auto">Browse our programs and apply today &mdash; build the skills that shape your future.</p>
      <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
        <a href="<?= url('courses') ?>" class="group inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-gradient-to-r from-primary to-secondary text-white text-lg font-semibold shadow-2xl shadow-primary/30 hover:-translate-y-0.5 transition">
          Explore Courses
          <svg class="h-5 w-5 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <a href="<?= url('about') ?>" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur text-white text-lg font-semibold hover:bg-white/20 transition">
          Learn More
        </a>
      </div>
    </div>
  </div>
</section>

<?php require INCLUDES_PATH . '/footer.php'; ?>
