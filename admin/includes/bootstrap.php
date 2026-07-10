<?php
/**
 * SIZSR Admin - bootstrap (loads config + admin helpers)
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';

/** Build an admin URL. */
function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

/** Active class helper for sidebar links. */
function nav_active(string $page, string $current): string
{
    return $page === $current
        ? 'bg-gradient-to-r from-primary/10 to-secondary/5 text-primary dark:text-secondary font-semibold'
        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5';
}

/** Read a numeric count safely. */
function admin_count(string $sql, array $params = []): int
{
    try { return (int)db_scalar($sql, $params); } catch (Throwable $e) { return 0; }
}
