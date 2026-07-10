<?php
/**
 * GET /api/courses.php
 * Public list of published courses (group invite link is intentionally hidden).
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

$rows = db_all(
    "SELECT id, title AS name, short_description, description, duration, location, course_type, start_date, seats_available
     FROM courses WHERE status = 'published' ORDER BY created_at DESC"
);

json_response(['success' => true, 'count' => count($rows), 'data' => $rows]);
