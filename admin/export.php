<?php
/**
 * SIZSR Admin - CSV / Excel export for reports
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$type   = (string)input('type', 'applications');
$format = (string)input('format', 'csv'); // csv | excel

$datasets = [
    'applications' => [
        'sql' => "SELECT a.reference, a.full_name, a.gender, a.email, a.phone, c.title AS course,
                         a.status, a.region, a.district, a.education_level, a.created_at
                  FROM applications a LEFT JOIN courses c ON c.id=a.course_id ORDER BY a.created_at DESC",
        'headers' => ['Reference','Full Name','Gender','Email','Phone','Course','Status','Region','District','Education','Applied'],
    ],
    'students' => [
        'sql' => "SELECT DISTINCT full_name, gender, email, phone, region, education_level FROM applications ORDER BY full_name ASC",
        'headers' => ['Full Name','Gender','Email','Phone','Region','Education'],
    ],
    'courses' => [
        'sql' => "SELECT c.title, cat.name AS category, c.trainer, c.duration, c.status,
                         (SELECT COUNT(*) FROM applications WHERE course_id=c.id) AS applications, c.created_at
                  FROM courses c LEFT JOIN categories cat ON cat.id=c.category_id ORDER BY c.created_at DESC",
        'headers' => ['Title','Category','Trainer','Duration','Status','Applications','Created'],
    ],
];

if (!isset($datasets[$type])) {
    http_response_code(400);
    die('Invalid export type.');
}

$rows = db_all($datasets[$type]['sql']);
$headers = $datasets[$type]['headers'];

$filename = 'sizsr_' . $type . '_' . date('Ymd_His');
log_activity('report_exported', "Exported {$type} as {$format}");

if ($format === 'excel') {
    // Excel-compatible HTML table (.xls)
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    echo "<table border='1'><tr>";
    foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
    echo '</tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($r as $cell) echo '<td>' . htmlspecialchars((string)$cell) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

// CSV (default)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
fputcsv($out, $headers);
foreach ($rows as $r) {
    fputcsv($out, array_values($r));
}
fclose($out);
exit;
