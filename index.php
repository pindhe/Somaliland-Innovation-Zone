<?php
/**
 * SIZSR - Front Controller / Router
 * Provides SEO-friendly routing for the public website.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

// Resolve the requested route (from .htaccess rewrite or ?route=)
$route = trim((string)($_GET['route'] ?? ''), '/');
$route = preg_replace('#[^a-zA-Z0-9/_\-]#', '', $route);

// Split route into segments
$segments = $route === '' ? [] : explode('/', $route);
$page = $segments[0] ?? 'home';

// Route map -> page file
$routes = [
    ''               => 'home',
    'home'           => 'home',
    'about'          => 'about',
    'courses'        => 'courses',
    'course'         => 'course-details', // /course/{slug}
    'apply'          => 'apply',          // /apply or /apply/{slug}
    'success'        => 'success',
];

$pageKey = $routes[$page] ?? null;

if ($pageKey === null) {
    http_response_code(404);
    $pageFile = PAGES_PATH . '/404.php';
} else {
    $pageFile = PAGES_PATH . '/' . $pageKey . '.php';
}

if (!is_file($pageFile)) {
    http_response_code(404);
    $pageFile = PAGES_PATH . '/404.php';
}

// Expose route params to page
$routeParam = $segments[1] ?? null;

require $pageFile;
