<?php
/**
 * SIZSR Admin - layout header (sidebar + topbar)
 * Pages should set $adminPage and $pageTitle before including this.
 */
if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/includes/bootstrap.php';
}
require_admin();

$adminPage = $adminPage ?? 'dashboard';
$pageTitle = $pageTitle ?? 'Dashboard';
$admin     = current_admin();

$pendingApps     = admin_count("SELECT COUNT(*) FROM applications WHERE status='pending'");
$newMessages     = admin_count("SELECT COUNT(*) FROM contacts WHERE status='new'");

$navGroups = [
    'Main' => [
        ['dashboard',    'Dashboard',    'index.php',        'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ],
    'Content' => [
        ['courses',      'Courses',      'courses.php',      'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['categories',   'Categories',   'categories.php',   'M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z'],
    ],
    'People' => [
        ['applications', 'Applications', 'applications.php', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
    ],
    'System' => [
        ['reports',      'Reports',      'reports.php',      'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['logs',         'Activity Logs','logs.php',         'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> &middot; SIZSR Admin</title>
  <link rel="icon" href="<?= e(setting('org_favicon') ? UPLOAD_URL . '/media/' . setting('org_favicon') : asset('images/favicon.png')) ?>" type="image/png">
  <script>
    if (localStorage.getItem('siz-admin-theme') === 'dark') document.documentElement.classList.add('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { colors: {
        primary: '#B11314', secondary: '#DEAE1B', accent: '#F9CC21',
        gold: '#F9CC21', goldDark: '#DEAE1B', goldLight: '#F8DF74', cream: '#FDF7DD',
        dark: '#1F2937', success: '#10B981', danger: '#EF4444', warning: '#F59E0B',
      }, fontFamily: { sans: ['Plus Jakarta Sans','Inter','system-ui','sans-serif'] } } },
    };
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <script>window.SIZ = { base: '<?= BASE_URL ?>', csrf: '<?= e(csrf_token()) ?>' };</script>
</head>
<body class="font-sans bg-[#F1F5F9] text-slate-800 dark:bg-dark dark:text-slate-200 antialiased">
<div class="flex min-h-screen">

  <!-- SIDEBAR -->
  <aside id="adminSidebar" class="fixed lg:sticky top-0 z-40 h-screen w-64 shrink-0 -translate-x-full lg:translate-x-0 transition-transform bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-white/5 flex flex-col">
    <div class="h-16 flex items-center gap-2.5 px-5 border-b border-slate-200 dark:border-white/5">
      <img src="<?= asset('images/logo.png') ?>" alt="SIZSR" class="h-9 w-9 rounded-xl object-cover">
      <div class="leading-tight">
        <p class="font-extrabold text-slate-900 dark:text-white">SIZSR</p>
        <p class="text-[10px] uppercase tracking-widest text-primary dark:text-secondary font-semibold">Admin Panel</p>
      </div>
    </div>
    <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 py-4 space-y-5">
      <?php foreach ($navGroups as $group => $items): ?>
        <div>
          <p class="px-3 mb-1.5 text-[10px] uppercase tracking-widest text-slate-400 font-bold"><?= e($group) ?></p>
          <div class="space-y-0.5">
            <?php foreach ($items as [$key, $label, $file, $icon]): $isActive = ($key === $adminPage); ?>
              <a href="<?= e(admin_url($file)) ?>" class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 <?= nav_active($key, $adminPage) ?>">
                <span class="absolute left-0 top-1/2 -translate-y-1/2 h-6 w-1 rounded-r-full bg-gradient-to-b from-primary to-secondary transition-all duration-200 <?= $isActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-50' ?>"></span>
                <svg class="h-5 w-5 shrink-0 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/></svg>
                <span class="flex-1"><?= e($label) ?></span>
                <?php if ($key === 'applications' && $pendingApps): ?><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-400/15 dark:text-amber-300"><?= $pendingApps ?></span><?php endif; ?>
                <?php if ($key === 'contacts' && $newMessages): ?><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-400/15 dark:text-sky-300"><?= $newMessages ?></span><?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </nav>
    <div class="p-3 border-t border-slate-200 dark:border-white/5 space-y-1">
      <a href="<?= url('') ?>" target="_blank" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition">
        <svg class="h-5 w-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        View Website
      </a>

      <!-- Profile card -->
      <div class="mt-1 flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 p-2.5">
        <span class="h-10 w-10 shrink-0 rounded-xl bg-gradient-to-br from-primary to-secondary text-white grid place-items-center font-bold shadow-md shadow-primary/20"><?= e(mb_strtoupper(mb_substr($admin['name'] ?? 'A', 0, 1))) ?></span>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-bold text-slate-800 dark:text-white truncate"><?= e($admin['name'] ?? 'Admin') ?></p>
          <p class="text-[11px] text-slate-400 capitalize truncate"><?= e($admin['role'] ?? 'admin') ?></p>
        </div>
        <a href="<?= e(admin_url('settings.php')) ?>" title="Settings" class="grid place-items-center h-8 w-8 rounded-lg text-slate-400 hover:text-primary dark:hover:text-secondary hover:bg-white dark:hover:bg-white/10 transition">
          <svg class="h-4.5 w-4.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </a>
        <a href="<?= e(admin_url('logout.php')) ?>" title="Sign out" class="grid place-items-center h-8 w-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/></svg>
        </a>
      </div>
    </div>
  </aside>
  <div id="sidebarBackdrop" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden"></div>

  <!-- MAIN -->
  <div class="flex-1 min-w-0 flex flex-col">
    <header class="sticky top-0 z-20 h-16 flex items-center gap-3 px-4 sm:px-6 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-b border-slate-200 dark:border-white/5">
      <button id="sidebarToggle" class="lg:hidden grid place-items-center h-10 w-10 rounded-xl border border-slate-200 dark:border-white/10">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
      <div class="ml-auto flex items-center gap-2">
        <button id="adminThemeToggle" aria-label="Toggle dark mode" class="grid place-items-center h-10 w-10 rounded-xl border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 transition">
          <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
          <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
        </button>
      </div>
    </header>

    <main class="flex-1 p-4 sm:p-6">
      <?php if ($flashes = render_flashes()): ?>
        <div class="mb-4"><?= $flashes ?></div>
      <?php endif; ?>
