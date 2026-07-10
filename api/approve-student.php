<?php
/**
 * POST /api/approve-student.php
 * Admin-only: approve a student application and auto-send the WhatsApp
 * group invite link via the WhatsApp Cloud API.
 * Accepts: id
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

if (!is_post()) {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}
if (!is_admin_logged_in()) {
    json_response(['success' => false, 'message' => 'Unauthorized.'], 401);
}

$id = (int)input('id', 0);
$app = db_one("SELECT id, reference, status FROM applications WHERE id = ?", [$id]);
if (!$app) {
    json_response(['success' => false, 'message' => 'Application not found.'], 404);
}

$wasApproved = ($app['status'] === 'approved');
db_exec("UPDATE applications SET status = 'approved' WHERE id = ?", [$id]);
log_activity('application_status', "Set {$app['reference']} to approved (API)");

$whatsapp = ['attempted' => false];
if (!$wasApproved) {
    $wa = notify_application_approved($id);
    $whatsapp = [
        'attempted' => true,
        'sent'      => $wa['success'],
        'to'        => $wa['to'],
        'error'     => $wa['error'],
    ];
}

json_response([
    'success'  => true,
    'message'  => 'Application approved.',
    'id'       => $id,
    'status'   => 'approved',
    'whatsapp' => $whatsapp,
]);
