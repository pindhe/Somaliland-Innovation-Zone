<?php
/**
 * SIZSR Admin - JSON data backup
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$tables = [
    'admins' => "SELECT id, name, email, role, status, created_at FROM admins", // no password hashes
    'categories' => "SELECT * FROM categories",
    'courses' => "SELECT * FROM courses",
    'applications' => "SELECT * FROM applications",
    'application_documents' => "SELECT * FROM application_documents",
    'contacts' => "SELECT * FROM contacts",
    'faqs' => "SELECT * FROM faqs",
    'testimonials' => "SELECT * FROM testimonials",
    'partners' => "SELECT * FROM partners",
    'success_stories' => "SELECT * FROM success_stories",
    'team_members' => "SELECT * FROM team_members",
    'settings' => "SELECT setting_key, setting_value FROM settings",
    'newsletter_subscribers' => "SELECT * FROM newsletter_subscribers",
];

$backup = ['generated_at' => date('c'), 'app' => APP_NAME, 'data' => []];
foreach ($tables as $name => $sql) {
    try { $backup['data'][$name] = db_all($sql); } catch (Throwable $e) { $backup['data'][$name] = []; }
}

log_activity('backup_downloaded', 'Database backup downloaded');

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="sizsr_backup_' . date('Ymd_His') . '.json"');
echo json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
