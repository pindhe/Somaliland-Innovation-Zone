<?php
/**
 * SIZSR - Core helper functions
 * Security, sanitization, auth, uploads, settings, db helpers.
 */

declare(strict_types=1);

// ===========================================================================
// OUTPUT / SANITIZATION
// ===========================================================================

/** Escape a value for safe HTML output (XSS protection). */
function e(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Trim + strip tags from raw input. */
function clean(?string $value): string
{
    return trim(strip_tags((string)($value ?? '')));
}

/** Build a full URL relative to the app base. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Build an asset URL. */
function asset(string $path = ''): string
{
    return ASSET_URL . '/' . ltrim($path, '/');
}

/** Redirect helper. */
function redirect(string $path): void
{
    $location = (str_starts_with($path, 'http')) ? $path : url($path);
    header('Location: ' . $location);
    exit;
}

/** Create an SEO-friendly slug. */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text !== '' ? $text : 'item-' . substr(md5((string)microtime(true)), 0, 6);
}

// ===========================================================================
// CSRF PROTECTION
// ===========================================================================

function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    return !empty($token)
        && !empty($_SESSION[CSRF_TOKEN_NAME])
        && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/** Abort the request when CSRF validation fails (use on POST). */
function csrf_check(): void
{
    $token = $_POST[CSRF_TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!csrf_verify($token)) {
        http_response_code(419);
        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Security token expired. Please refresh and try again.'], 419);
        }
        die('Invalid security token. Please go back and try again.');
    }
}

// ===========================================================================
// REQUEST HELPERS
// ===========================================================================

function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, $default = null)
{
    return $_REQUEST[$key] ?? $default;
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ===========================================================================
// FLASH MESSAGES
// ===========================================================================

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function render_flashes(): string
{
    $map = [
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
        'error'   => 'bg-red-50 text-red-800 border-red-300',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-300',
        'info'    => 'bg-sky-50 text-sky-800 border-sky-300',
    ];
    $html = '';
    foreach (get_flashes() as $f) {
        $cls = $map[$f['type']] ?? $map['info'];
        $html .= '<div class="flash-msg border rounded-lg px-4 py-3 mb-3 text-sm font-medium ' . $cls . '">' . e($f['message']) . '</div>';
    }
    return $html;
}

// ===========================================================================
// AUTHENTICATION (Admin)
// ===========================================================================

function admin_login(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'    => (int)$admin['id'],
        'name'  => $admin['name'],
        'email' => $admin['email'],
        'role'  => $admin['role'] ?? 'admin',
    ];
    $_SESSION['admin_login_time'] = time();
}

function admin_logout(): void
{
    unset($_SESSION['admin'], $_SESSION['admin_login_time']);
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin']['id']);
}

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        flash('warning', 'Please log in to continue.');
        redirect('admin/login.php');
    }
}

// ===========================================================================
// PASSWORD HELPERS
// ===========================================================================

function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

// ===========================================================================
// DATABASE QUICK HELPERS
// ===========================================================================

function db_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function db_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_scalar(string $sql, array $params = [])
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function db_exec(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

function db_insert(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int)db()->lastInsertId();
}

// ===========================================================================
// SETTINGS (cached)
// ===========================================================================

function settings(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    try {
        foreach (db_all('SELECT setting_key, setting_value FROM settings') as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) {
        $cache = [];
    }
    return $cache;
}

function setting(string $key, $default = ''): string
{
    $all = settings();
    return $all[$key] ?? (string)$default;
}

function setting_save(string $key, string $value): void
{
    db_exec(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$key, $value]
    );
}

// ===========================================================================
// CMS CONTENT BLOCKS (stored in settings with cms_ prefix)
// ===========================================================================

function cms(string $key, string $default = ''): string
{
    return setting('cms_' . $key, $default);
}

// ===========================================================================
// VISITOR / JOIN COUNTER
// ===========================================================================

/**
 * Track unique visitors with REAL data. Counting starts at 0; every new device
 * (one without the cookie) increments the live count by exactly 1, so the figure
 * grows organically 1, 2, 3 ... as people actually visit. Returns the live count.
 */
function track_visitor(): int
{
    try {
        if (empty($_COOKIE['siz_visitor'])) {
            db_exec(
                "INSERT INTO settings (setting_key, setting_value) VALUES ('visitor_count', '1')
                 ON DUPLICATE KEY UPDATE setting_value = CAST(setting_value AS UNSIGNED) + 1"
            );
            if (!headers_sent()) {
                setcookie('siz_visitor', '1', [
                    'expires'  => time() + 60 * 60 * 24 * 365,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            $_COOKIE['siz_visitor'] = '1';
        }
        return (int)db_scalar("SELECT setting_value FROM settings WHERE setting_key = 'visitor_count'");
    } catch (Throwable $e) {
        return 0;
    }
}

// ===========================================================================
// ACTIVITY LOG
// ===========================================================================

function log_activity(string $action, string $description = '', ?int $adminId = null): void
{
    try {
        $adminId = $adminId ?? (current_admin()['id'] ?? null);
        db_exec(
            'INSERT INTO activity_logs (admin_id, action, description, ip_address, created_at)
             VALUES (?, ?, ?, ?, NOW())',
            [$adminId, $action, $description, $_SERVER['REMOTE_ADDR'] ?? null]
        );
    } catch (Throwable $e) {
        // Never let logging break the request.
    }
}

// ===========================================================================
// FILE UPLOADS (secure)
// ===========================================================================

/**
 * Securely handle a single uploaded file.
 *
 * @return array{success:bool, filename?:string, path?:string, error?:string}
 */
function handle_upload(array $file, string $subdir, array $allowedExt = ALLOWED_DOC_TYPES, int $maxSize = MAX_UPLOAD_SIZE): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid upload.'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'error' => 'No file was uploaded.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'error' => 'File is too large.'];
        default:
            return ['success' => false, 'error' => 'Upload failed.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File exceeds the maximum allowed size of ' . round($maxSize / 1048576, 1) . ' MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['success' => false, 'error' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExt)];
    }

    // Validate real MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/gif',
    ];
    if (!in_array($mime, $allowedMimes, true)) {
        return ['success' => false, 'error' => 'Invalid file content.'];
    }

    $targetDir = UPLOAD_PATH . DIRECTORY_SEPARATOR . $subdir;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $safeName = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Could not save uploaded file.'];
    }

    return [
        'success'  => true,
        'filename' => $safeName,
        'path'     => $subdir . '/' . $safeName,
    ];
}

// ===========================================================================
// FORMATTING / MISC
// ===========================================================================

function format_date(?string $date, string $format = 'M d, Y'): string
{
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    try {
        return (new DateTime($date))->format($format);
    } catch (Throwable $e) {
        return '-';
    }
}

function time_ago(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M d, Y', $time);
}

function excerpt(string $text, int $length = 120): string
{
    $text = trim(strip_tags($text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '…';
}

/** Status badge classes for applications/courses. */
function status_badge(string $status): string
{
    $map = [
        'pending'   => 'bg-amber-100 text-amber-800',
        'approved'  => 'bg-emerald-100 text-emerald-800',
        'rejected'  => 'bg-red-100 text-red-800',
        'waitlist'  => 'bg-sky-100 text-sky-800',
        'published' => 'bg-emerald-100 text-emerald-800',
        'draft'     => 'bg-slate-100 text-slate-700',
        'archived'  => 'bg-slate-200 text-slate-600',
        'new'       => 'bg-sky-100 text-sky-800',
        'read'      => 'bg-slate-100 text-slate-700',
        'replied'   => 'bg-emerald-100 text-emerald-800',
    ];
    return $map[$status] ?? 'bg-slate-100 text-slate-700';
}

/**
 * Evaluate an application's completeness against admission criteria.
 *
 * @param array      $app              Application row.
 * @param array      $uploadedDocTypes doc_type values uploaded for this application.
 * @param array|null $course           Course row (uses required_documents).
 * @return array{status:string,label:string,bg:string,text:string,percent:int,missing:array,done:int,total:int}
 */
function evaluate_application(array $app, array $uploadedDocTypes = [], ?array $course = null): array
{
    $has = fn($k) => isset($app[$k]) && trim((string)$app[$k]) !== '';

    $missing = [];
    $total = 0;
    $done = 0;
    $check = function (string $label, bool $ok) use (&$missing, &$total, &$done) {
        $total++;
        if ($ok) { $done++; } else { $missing[] = $label; }
    };

    // Personal information
    $check('Full Name', $has('full_name'));
    $check('Gender', $has('gender'));
    $check('Date of Birth', $has('date_of_birth'));
    $check('National ID', $has('national_id'));
    $check('Phone Number', $has('phone'));
    $check('Email', $has('email'));
    // Education
    $check('Education Level', $has('education_level'));
    $check('School / University', $has('school') || $has('university'));
    $check('Graduation Year', $has('graduation_year'));
    // Address
    $check('Region', $has('region'));
    $check('District', $has('district'));
    $check('Address', $has('address'));
    // Motivation
    $check('Motivation: Why Join', $has('why_join'));
    $check('Motivation: Goals', $has('goals'));
    $check('Motivation: Expectations', $has('expectations'));

    // Required documents (only those the course requires)
    $docMap = [
        'National ID'             => 'National ID',
        'Passport Photo'          => 'Passport Photo',
        'CV / Resume'             => 'CV',
        'High School Certificate' => 'Certificate',
        'Diploma Certificate'     => 'Certificate',
        'University Degree'       => 'Certificate',
        'Recommendation Letter'   => 'Certificate',
        'Birth Certificate'       => 'Certificate',
    ];
    $requiredDocs = array_filter(array_map('trim', explode(',', (string)($course['required_documents'] ?? ''))));
    $docsRequired = 0;
    $docsSatisfied = 0;
    foreach ($requiredDocs as $rd) {
        $docsRequired++;
        $needType = $docMap[$rd] ?? $rd;
        $ok = in_array($needType, $uploadedDocTypes, true);
        if ($ok) { $docsSatisfied++; }
        $check($rd, $ok);
    }

    $percent = $total > 0 ? (int)round($done / $total * 100) : 0;

    if (empty($missing)) {
        $status = 'qualified';
    } else {
        $noDocs = $docsRequired > 0 && $docsSatisfied === 0;
        $status = ($percent < 40 || $noDocs) ? 'not_qualified' : 'incomplete';
    }

    $styles = [
        'qualified'     => ['Qualified',     '#16A34A', '#FFFFFF'],
        'incomplete'    => ['Incomplete',    '#FACC15', '#000000'],
        'not_qualified' => ['Not Qualified', '#DC2626', '#FFFFFF'],
    ];
    [$label, $bg, $text] = $styles[$status];

    return compact('status', 'label', 'bg', 'text', 'percent', 'missing', 'done', 'total');
}

/**
 * Registration deadline helpers. The deadline is a DATE, so registration stays
 * open through the END of that day (23:59:59).
 */
function deadline_end_ts(?string $deadline): ?int
{
    if (empty($deadline)) return null;
    $ts = strtotime($deadline . ' 23:59:59');
    return $ts ?: null;
}

function deadline_passed(?string $deadline): bool
{
    $end = deadline_end_ts($deadline);
    return $end !== null && time() > $end;
}

/**
 * Per-device "already applied" tracking via cookie. Prevents the same device
 * from applying to the same course twice (hides the Apply button).
 */
function applied_course_ids(): array
{
    $raw = (string)($_COOKIE['siz_applied'] ?? '');
    return array_values(array_filter(array_map('intval', explode(',', $raw))));
}

function has_applied(int $courseId): bool
{
    return $courseId > 0 && in_array($courseId, applied_course_ids(), true);
}

function mark_applied(int $courseId): void
{
    if ($courseId <= 0) return;
    $ids = applied_course_ids();
    if (!in_array($courseId, $ids, true)) {
        $ids[] = $courseId;
    }
    $val = implode(',', $ids);
    if (!headers_sent()) {
        setcookie('siz_applied', $val, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    $_COOKIE['siz_applied'] = $val;
}

/** Human label of time left, e.g. "3 days left", "5h left", "12m left". */
function deadline_label(?string $deadline): string
{
    $end = deadline_end_ts($deadline);
    if ($end === null) return '';
    $left = $end - time();
    if ($left <= 0) return '';
    $days  = (int)floor($left / 86400);
    $hours = (int)floor(($left % 86400) / 3600);
    $mins  = (int)floor(($left % 3600) / 60);
    if ($days >= 1)  return $days . ' day' . ($days > 1 ? 's' : '') . ' left';
    if ($hours >= 1) return $hours . 'h left';
    return max(1, $mins) . 'm left';
}

/** Pagination helper -> returns [items, total, pages, page]. */
function paginate(string $baseSql, string $countSql, array $params, int $page, int $perPage = 12): array
{
    $page = max(1, $page);
    $total = (int)db_scalar($countSql, $params);
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $pages);
    $offset = ($page - 1) * $perPage;
    $sql = $baseSql . " LIMIT $perPage OFFSET $offset";
    $items = db_all($sql, $params);
    return ['items' => $items, 'total' => $total, 'pages' => $pages, 'page' => $page, 'perPage' => $perPage];
}

/** Render pagination links. */
function pagination_links(array $p, string $baseUrl): string
{
    if ($p['pages'] <= 1) {
        return '';
    }
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav class="flex items-center justify-center gap-1 mt-8">';
    $prevDisabled = $p['page'] <= 1 ? 'pointer-events-none opacity-40' : '';
    $html .= '<a href="' . e($baseUrl . $sep . 'page=' . ($p['page'] - 1)) . '" class="px-3 py-2 rounded-lg border text-sm ' . $prevDisabled . '">Prev</a>';
    for ($i = 1; $i <= $p['pages']; $i++) {
        if ($i == 1 || $i == $p['pages'] || abs($i - $p['page']) <= 2) {
            $active = $i == $p['page'] ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700';
            $html .= '<a href="' . e($baseUrl . $sep . 'page=' . $i) . '" class="px-3.5 py-2 rounded-lg border text-sm ' . $active . '">' . $i . '</a>';
        } elseif (abs($i - $p['page']) === 3) {
            $html .= '<span class="px-2 text-slate-400">…</span>';
        }
    }
    $nextDisabled = $p['page'] >= $p['pages'] ? 'pointer-events-none opacity-40' : '';
    $html .= '<a href="' . e($baseUrl . $sep . 'page=' . ($p['page'] + 1)) . '" class="px-3 py-2 rounded-lg border text-sm ' . $nextDisabled . '">Next</a>';
    $html .= '</nav>';
    return $html;
}

/** Course thumbnail URL with fallback. */
function course_image(?array $course): string
{
    if (!empty($course['image'])) {
        return UPLOAD_URL . '/courses/' . $course['image'];
    }
    // Deterministic gradient placeholder based on title.
    return '';
}

/** Render a single course card (used on home + courses pages). */
function render_course_card(array $c): string
{
    $img       = course_image($c);
    $title     = e($c['title']);
    $cat       = e($c['category_name'] ?? 'General');
    $color     = e($c['category_color'] ?? '#B11314');
    $trainer   = e($c['trainer'] ?? 'SIZ Trainer');
    $trainerLtr= e(mb_strtoupper(mb_substr((string)($c['trainer'] ?? 'S'), 0, 1)));
    $duration  = e($c['duration'] ?? 'Flexible');
    $location  = e($c['location'] ?? '');
    $seats     = (int)($c['seats_available'] ?? 0);
    $startDate = !empty($c['start_date']) ? e(format_date($c['start_date'], 'M d, Y')) : '';
    $detailUrl = e(url('course/' . $c['slug']));
    $applyUrl  = e(url('apply/' . $c['slug']));
    $initial   = e(mb_strtoupper(mb_substr($c['title'], 0, 1)));

    $thumb = $img
        ? '<img src="' . e($img) . '" alt="' . $title . '" loading="lazy" class="h-full w-full object-cover group-hover:scale-110 transition duration-700 ease-out">'
        : '<div class="h-full w-full grid place-items-center bg-gradient-to-br from-primary via-secondary to-primary text-white text-5xl font-extrabold group-hover:scale-110 transition duration-700 ease-out">' . $initial . '</div>';

    // Meta line (location + start date)
    $metaItems = '';
    if ($location !== '') {
        $metaItems .= '<span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-primary dark:text-secondary" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/></svg>' . $location . '</span>';
    }
    if ($startDate !== '') {
        $metaItems .= '<span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-primary dark:text-secondary" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' . $startDate . '</span>';
    }

    // Registration deadline — countdown badge + auto-close when passed.
    $deadlineRaw = $c['registration_deadline'] ?? null;
    $isClosed    = deadline_passed($deadlineRaw);
    $deadlineEnd = deadline_end_ts($deadlineRaw);
    $timeLeft    = (!$isClosed) ? deadline_label($deadlineRaw) : '';

    // Countdown / closed badge shown on the image (top-right).
    $clockSvg = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>';
    if ($deadlineEnd && !$isClosed) {
        $deadlineBadge = '<span class="deadline-badge absolute top-3 right-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-white shadow-lg backdrop-blur-sm bg-red-600/90" data-deadline="' . $deadlineEnd . '">' . $clockSvg . '<span class="deadline-text">' . e($timeLeft) . '</span></span>';
    } elseif ($isClosed) {
        $deadlineBadge = '<span class="deadline-badge absolute top-3 right-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-white shadow-lg backdrop-blur-sm bg-slate-700/90"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M15 9l-6 6M9 9l6 6"/></svg><span class="deadline-text">Closed</span></span>';
    } else {
        $deadlineBadge = '';
    }

    $cardDeadlineAttr = ($deadlineEnd && !$isClosed) ? ' data-card-deadline="' . $deadlineEnd . '"' : '';

    $hasApplied = has_applied((int)($c['id'] ?? 0));

    if ($isClosed) {
        $seatsBadge = '<span class="card-status inline-flex items-center gap-1.5 text-xs font-semibold text-red-500"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M15 9l-6 6M9 9l6 6"/></svg>Registration closed</span>';
    } elseif ($hasApplied) {
        $seatsBadge = '<span class="card-status inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>You have applied</span>';
    } else {
        $seatsBadge = $seats > 0
            ? '<span class="card-status inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>' . $seats . ' seats open</span>'
            : '<span class="card-status inline-flex items-center gap-1 text-xs font-semibold text-slate-400"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>Limited seats</span>';
    }

    // Action buttons: Details only when closed or already applied; otherwise Details + Apply.
    if ($isClosed || $hasApplied) {
        $actions = '<a href="' . $detailUrl . '" class="js-details w-full text-center px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition">View Details</a>';
    } else {
        $actions = '<a href="' . $detailUrl . '" class="js-details flex-1 text-center px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition">Details</a>'
            . '<a href="' . $applyUrl . '" class="js-apply flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold hover:shadow-lg hover:shadow-primary/30 transition group/btn">Apply <svg class="h-4 w-4 group-hover/btn:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg></a>';
    }

    return <<<HTML
<article class="reveal group relative flex flex-col rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-sm hover:shadow-2xl hover:shadow-primary/10 hover:-translate-y-1.5 hover:border-primary/20 transition-all duration-300 overflow-hidden"$cardDeadlineAttr>
  <a href="$detailUrl" class="relative block h-48 overflow-hidden">
    $thumb
    <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/5 to-transparent"></div>
    <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold text-white shadow-lg backdrop-blur-sm" style="background:$color">$cat</span>
    $deadlineBadge
    <span class="absolute bottom-3 left-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/40 text-white backdrop-blur-sm">
      <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>$duration
    </span>
  </a>
  <div class="flex flex-col flex-1 p-5">
    <h3 class="font-extrabold text-slate-900 dark:text-white text-lg leading-snug line-clamp-2 min-h-[3.5rem]">
      <a href="$detailUrl" class="hover:text-primary dark:hover:text-secondary transition">$title</a>
    </h3>

    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400">
      $metaItems
    </div>

    <div class="mt-4 flex items-center gap-2.5">
      <span class="h-9 w-9 shrink-0 rounded-full bg-gradient-to-br from-primary to-secondary text-white grid place-items-center text-sm font-bold">$trainerLtr</span>
      <div class="min-w-0">
        <p class="text-[11px] text-slate-400 leading-none">Trainer</p>
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">$trainer</p>
      </div>
    </div>

    <div class="mt-auto pt-5 mt-5 border-t border-slate-100 dark:border-white/5">
      <div class="mb-3">$seatsBadge</div>
      <div class="flex items-center gap-2">
        $actions
      </div>
    </div>
  </div>
</article>
HTML;
}

/** Old input value helper for forms. */
function old(string $key, $default = ''): string
{
    return e((string)($_SESSION['_old'][$key] ?? $default));
}

function set_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}
