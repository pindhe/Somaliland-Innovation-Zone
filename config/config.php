<?php
/**
 * SIZSR - Somaliland Innovation Zone Student Registration System
 * Global Configuration
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Error reporting (set DEBUG to false in production)
// ---------------------------------------------------------------------------
define('SIZSR_DEBUG', filter_var(getenv('SIZSR_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN));

if (SIZSR_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ---------------------------------------------------------------------------
// Paths & URLs
// ---------------------------------------------------------------------------
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');

// ---------------------------------------------------------------------------
// Lightweight .env loader (KEY=VALUE per line). Values are exposed via
// getenv()/$_ENV so secret API keys never need to live in tracked code.
// ---------------------------------------------------------------------------
(function () {
    $envFile = ROOT_PATH . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envFile) || !is_readable($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Strip surrounding quotes if present
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[strlen($value) - 1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
})();
define('INCLUDES_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'includes');
define('PAGES_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'pages');
define('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads');

// Auto-detect base URL (works in XAMPP subfolders e.g. /SIZ)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
// When the front controller lives in an /admin or /api sub-folder we still want the app root.
$scriptDir = preg_replace('#/(admin|api|pages)(/.*)?$#', '', $scriptDir);
$baseUrl = rtrim($scriptDir, '/');
if ($baseUrl === '' || $baseUrl === '.') {
    $baseUrl = '';
}
define('BASE_URL', $baseUrl);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', $scheme . '://' . $host . BASE_URL);
define('UPLOAD_URL', BASE_URL . '/assets/uploads');
define('ASSET_URL', BASE_URL . '/assets');

// ---------------------------------------------------------------------------
// Application defaults (overridable via settings table)
// ---------------------------------------------------------------------------
define('APP_NAME', 'SIZSR');
define('APP_FULL_NAME', 'Somaliland Innovation Zone Student Registration System');
define('ORG_NAME', 'Somaliland Innovation Zone');
define('APP_TIMEZONE', 'Africa/Mogadishu');

date_default_timezone_set(APP_TIMEZONE);

// ---------------------------------------------------------------------------
// Security
// ---------------------------------------------------------------------------
define('CSRF_TOKEN_NAME', 'sizsr_csrf');
define('SESSION_NAME', 'SIZSR_SESSION');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_DOC_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// ---------------------------------------------------------------------------
// Session bootstrap (secure)
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => ($scheme === 'https'),
    ]);
    session_start();
}

// ---------------------------------------------------------------------------
// Load database + core functions
// ---------------------------------------------------------------------------
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/whatsapp.php';
