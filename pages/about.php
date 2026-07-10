<?php
if (!defined('ROOT_PATH')) { require_once dirname(__DIR__) . '/config/config.php'; }

$pageTitle = 'About Us';
$activeNav = 'about';
$metaDesc  = 'Learn about Somaliland Innovation Zone - our history, mission, vision and the team empowering the next generation of innovators.';

$objectives = array_filter(array_map('trim', explode("\n", cms('about_objectives', ''))));

require INCLUDES_PATH . '/header.php';
?>

<!-- HERO -->
<section class="relative -mt-24 overflow-hidden">
  <!-- Background image -->
  <div class="absolute inset-0 -z-10">
    <img src="<?= asset('images/aboutbg.jpg') ?>" alt="Somaliland Innovation Zone building" aria-hidden="true"
         class="h-full w-full object-cover object-center scale-105 animate-[heroZoom_22s_ease-in-out_infinite_alternate]">
    <div class="absolute inset-0 bg-gradient-to-b from-dark/85 via-dark/75 to-dark/90"></div>
    <div class="absolute inset-0 bg-dark/30"></div>
    <div class="absolute -top-24 right-1/4 h-96 w-96 rounded-full bg-secondary/20 blur-3xl"></div>
    <div class="absolute bottom-0 left-1/4 h-80 w-80 rounded-full bg-primary/20 blur-3xl"></div>
  </div>

  <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pt-40 pb-24 text-center text-white">
    <span class="animate-in inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 backdrop-blur text-secondary text-sm font-semibold" style="animation-delay:.05s">
      <span class="h-1.5 w-1.5 rounded-full bg-secondary"></span> About SIZ
    </span>
    <h1 class="animate-in mt-5 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight" style="animation-delay:.14s">Building the future of innovation in Somaliland</h1>
    <p class="animate-in mt-6 text-lg text-slate-200/90 max-w-2xl mx-auto leading-relaxed" style="animation-delay:.24s"><?= e(cms('about_history', 'Somaliland Innovation Zone empowers young innovators with practical skills.')) ?></p>

    <!-- Quick highlights -->
    <div class="animate-in mt-9 flex flex-wrap justify-center gap-3" style="animation-delay:.34s">
      <?php
      $highlights = [
          ['M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'Practical Skills'],
          ['M12 6.5C10.5 5.5 8.5 5 6.5 5S3 5.5 3 5.5v13s2-.5 3.5-.5 3.5.5 5.5 1.5m0-13c1.5-1 3.5-1.5 5.5-1.5S21 5.5 21 5.5v13s-2-.5-3.5-.5-4 .5-5.5 1.5m0-13v13', 'Expert Trainers'],
          ['M17 20h5v-1a4 4 0 00-4-4h-1m-6 5H2v-1a4 4 0 014-4h4a4 4 0 014 4v1zm-1-9a3 3 0 11-6 0 3 3 0 016 0z', 'Community Driven'],
      ];
      foreach ($highlights as [$path, $label]): ?>
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/15 backdrop-blur text-white text-sm font-medium">
          <svg class="h-4 w-4 text-secondary" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/></svg>
          <?= $label ?>
        </span>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Wave divider -->
  <svg class="block w-full text-slate-50 dark:text-dark" viewBox="0 0 1440 80" preserveAspectRatio="none" style="height:60px"><path fill="currentColor" d="M0 80V40c180 30 360 30 540 10S900 10 1080 20s270 25 360 20v40z"/></svg>
</section>

<!-- MISSION / VISION -->
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 grid md:grid-cols-2 gap-6">
  <div class="reveal rounded-3xl bg-gradient-to-br from-primary to-secondary p-8 lg:p-10 text-white shadow-xl">
    <div class="mb-5 grid place-items-center h-14 w-14 rounded-2xl bg-white/15 backdrop-blur">
      <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/></svg>
    </div>
    <h2 class="text-2xl font-extrabold">Our Mission</h2>
    <p class="mt-3 text-white/90 leading-relaxed"><?= e(cms('about_mission', '')) ?></p>
  </div>
  <div class="reveal rounded-3xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-8 lg:p-10 shadow-sm">
    <div class="mb-5 grid place-items-center h-14 w-14 rounded-2xl bg-primary/10 text-primary dark:text-secondary">
      <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S5.5 5.5 12 5.5 21.5 12 21.5 12 18.5 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
    </div>
    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Our Vision</h2>
    <p class="mt-3 text-slate-600 dark:text-slate-300 leading-relaxed"><?= e(cms('about_vision', '')) ?></p>
  </div>
</section>

<!-- OBJECTIVES -->
<?php if ($objectives): ?>
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="reveal text-center max-w-2xl mx-auto mb-10">
    <span class="text-sm font-bold uppercase tracking-widest text-primary dark:text-secondary">What we do</span>
    <h2 class="mt-2 text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white">Our Objectives</h2>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <?php foreach ($objectives as $i => $obj): ?>
      <div class="reveal rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
        <div class="h-11 w-11 rounded-xl bg-primary/10 text-primary dark:text-secondary grid place-items-center font-extrabold text-lg mb-4"><?= $i + 1 ?></div>
        <p class="font-semibold text-slate-800 dark:text-slate-200 leading-relaxed"><?= e($obj) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- LOCATION / MAP -->
<?php
$addr   = setting('org_address') ?: 'Somaliland Innovation Zone, Hargeisa, Somaliland';
$phone  = setting('org_phone');
$email  = setting('org_email');
$lat    = '9.5654126';
$lng    = '44.0487098';
?>
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
  <div class="reveal text-center max-w-2xl mx-auto mb-12">
    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/10 text-primary dark:text-secondary text-sm font-bold uppercase tracking-widest">
      <span class="h-1.5 w-1.5 rounded-full bg-secondary"></span> Find Us
    </span>
    <h2 class="mt-4 text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white">Visit Our Campus</h2>
    <p class="mt-4 text-lg text-slate-600 dark:text-slate-300">Come see where innovation happens. We'd love to welcome you.</p>
  </div>

  <div class="grid lg:grid-cols-3 gap-6 items-stretch">
    <!-- Contact info -->
    <div class="reveal space-y-4">
      <div class="flex items-start gap-4 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm">
        <span class="grid place-items-center h-11 w-11 rounded-xl bg-primary/10 text-primary dark:text-secondary shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/></svg>
        </span>
        <div>
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Address</p>
          <p class="mt-0.5 font-semibold text-slate-800 dark:text-slate-200"><?= e($addr) ?></p>
        </div>
      </div>
      <?php if ($phone): ?>
      <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>" class="flex items-start gap-4 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm hover:border-primary/30 transition">
        <span class="grid place-items-center h-11 w-11 rounded-xl bg-primary/10 text-primary dark:text-secondary shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
        </span>
        <div>
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Phone</p>
          <p class="mt-0.5 font-semibold text-slate-800 dark:text-slate-200"><?= e($phone) ?></p>
        </div>
      </a>
      <?php endif; ?>
      <?php if ($email): ?>
      <a href="mailto:<?= e($email) ?>" class="flex items-start gap-4 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-5 shadow-sm hover:border-primary/30 transition">
        <span class="grid place-items-center h-11 w-11 rounded-xl bg-primary/10 text-primary dark:text-secondary shrink-0">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 17.25V6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 7l8.5 6 8.5-6"/></svg>
        </span>
        <div>
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Email</p>
          <p class="mt-0.5 font-semibold text-slate-800 dark:text-slate-200 break-all"><?= e($email) ?></p>
        </div>
      </a>
      <?php endif; ?>
      <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $lat ?>,<?= $lng ?>" target="_blank" rel="noopener"
         class="flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-primary to-secondary text-white font-semibold p-4 shadow-lg shadow-primary/25 hover:-translate-y-0.5 transition">
        Get Directions
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
      </a>
    </div>

    <!-- Map -->
    <div class="reveal lg:col-span-2 rounded-3xl overflow-hidden border border-slate-100 dark:border-white/5 shadow-lg min-h-[380px]">
      <iframe
        title="Somaliland Innovation Zone location"
        src="https://www.google.com/maps?q=<?= $lat ?>,<?= $lng ?>&z=17&output=embed"
        width="100%" height="100%" style="border:0;min-height:380px" allowfullscreen=""
        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
</section>

<?php require INCLUDES_PATH . '/footer.php'; ?>
