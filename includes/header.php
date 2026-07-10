<?php
if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config/config.php';
}
$pageTitle = $pageTitle ?? APP_NAME;
$metaDesc  = $metaDesc ?? 'Somaliland Innovation Zone - discover technology training programs, bootcamps and innovation opportunities.';
$activeNav = $activeNav ?? '';
$orgName   = setting('org_name', ORG_NAME);
$orgLogo   = setting('org_logo', '');
$navItems  = [
    'home'     => ['label' => 'Home',    'url' => url(''),        'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
    'courses'  => ['label' => 'Courses', 'url' => url('courses'), 'icon' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
    'about'    => ['label' => 'About',   'url' => url('about'),   'icon' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z'],
];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> &middot; <?= e($orgName) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="theme-color" content="#B11314">
    <link rel="icon" href="<?= e(setting('org_favicon') ? UPLOAD_URL . '/media/' . setting('org_favicon') : asset('images/favicon.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= asset('images/logo.png') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:type" content="website">

    <!-- Tailwind CSS (CDN with custom config) -->
    <script>
      // Apply dark mode early to prevent flash
      if (localStorage.getItem('siz-theme') === 'dark' ||
          (!('siz-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              primary: '#B11314',
              secondary: '#DEAE1B',
              accent: '#F9CC21',
              gold: '#F9CC21',
              goldDark: '#DEAE1B',
              goldLight: '#F8DF74',
              cream: '#FDF7DD',
              dark: '#1F2937',
              success: '#10B981',
              danger: '#EF4444',
              warning: '#F59E0B',
            },
            fontFamily: {
              sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
            },
            keyframes: {
              floaty: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-14px)' } },
              fadeUp: { '0%': { opacity: 0, transform: 'translateY(24px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
            },
            animation: {
              floaty: 'floaty 6s ease-in-out infinite',
              fadeUp: 'fadeUp .7s ease forwards',
            },
          },
        },
      };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <script>window.SIZ = { base: '<?= BASE_URL ?>', csrf: '<?= e(csrf_token()) ?>' };</script>
</head>
<body class="font-sans bg-[#F8FAFC] text-slate-800 dark:bg-dark dark:text-slate-200 antialiased">

<!-- ===================== NAVBAR ===================== -->
<header id="siteHeader" class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
  <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div id="navInner" class="mt-3 flex items-center justify-between rounded-2xl border border-white/20 bg-white/70 dark:bg-dark/70 backdrop-blur-xl shadow-lg shadow-slate-900/5 px-4 sm:px-6 py-3 transition-all">
      <a href="<?= url('') ?>" class="flex items-center gap-2.5 group">
        <img src="<?= asset('images/navlogo.png') ?>" alt="Somaliland Innovation Zone" class="h-11 w-11 rounded-xl object-contain bg-white p-1 shadow-md group-hover:scale-105 transition">
        <span class="leading-tight">
          <span class="block font-extrabold text-slate-900 dark:text-white text-base sm:text-lg tracking-tight leading-snug max-w-[200px]">Somaliland Innovation Zone</span>
          <span class="block text-[10px] uppercase tracking-widest text-primary dark:text-secondary font-semibold">Student Registration</span>
        </span>
      </a>

      <div class="hidden lg:flex items-center gap-1.5">
        <?php foreach ($navItems as $key => $item): ?>
          <a href="<?= e($item['url']) ?>" title="<?= e($item['label']) ?>" aria-label="<?= e($item['label']) ?>"
             class="group relative grid place-items-center h-11 w-11 rounded-xl transition <?= $activeNav === $key ? 'bg-primary/10 text-primary dark:text-secondary' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-secondary hover:bg-slate-100 dark:hover:bg-white/5' ?>">
            <svg class="h-5.5 w-5.5" style="height:1.4rem;width:1.4rem" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $item['icon'] ?>"/></svg>
            <span class="pointer-events-none absolute top-full mt-2 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-dark text-white text-xs font-semibold px-2.5 py-1 opacity-0 group-hover:opacity-100 transition shadow-lg"><?= e($item['label']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="flex items-center gap-2">
        <a href="<?= url('courses') ?>" class="hidden sm:inline-flex items-center px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 transition">
          Apply Now
        </a>
        <button id="mobileMenuBtn" aria-label="Open menu" class="lg:hidden grid place-items-center h-10 w-10 rounded-xl border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobileMenu" class="lg:hidden hidden mt-2 rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-dark shadow-xl p-3">
      <?php foreach ($navItems as $key => $item): ?>
        <a href="<?= e($item['url']) ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold <?= $activeNav === $key ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5' ?>">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $item['icon'] ?>"/></svg>
          <?= e($item['label']) ?>
        </a>
      <?php endforeach; ?>
      <a href="<?= url('courses') ?>" class="mt-2 block text-center px-4 py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold">Apply Now</a>
    </div>
  </nav>
</header>

<main class="pt-24">
<?php if ($flashes = render_flashes()): ?>
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-2"><?= $flashes ?></div>
<?php endif; ?>
