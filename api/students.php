<?php
/**
 * GET /api/students.php
 * Admin-only list of student applications. Optional filters: ?status=&course_id=
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

if (!is_admin_logged_in()) {
    json_response(['success' => false, 'message' => 'Unauthorized.'], 401);
}

$where  = [];
$params = [];

$status = (string)input('status', '');
if (in_array($status, ['pending', 'approved', 'rejected', 'waitlist'], true)) {
    $where[]  = 'a.status = ?';
    $params[] = $status;
}
$courseId = (int)input('course_id', 0);
if ($courseId > 0) {
    $where[]  = 'a.course_id = ?';
    $params[] = $courseId;
}

$sql = "SELECT a.id, a.reference, a.full_name, a.phone, a.whatsapp, a.email,
               a.course_id, c.title AS course_name, a.status, a.created_at
        FROM applications a LEFT JOIN courses c ON c.id = a.course_id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY a.created_at DESC';

$rows = db_all($sql, $params);

json_response(['success' => true, 'count' => count($rows), 'data' => $rows]);
