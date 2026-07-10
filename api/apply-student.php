<?php
/**
 * POST /api/apply-student.php
 * Lightweight student application endpoint.
 * Accepts: full_name, phone, email, course_id (+ optional whatsapp)
 *
 * The full multi-step application (with documents) lives at /pages/apply.php;
 * this endpoint is the minimal programmatic version described in the API spec.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

if (!is_post()) {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$fullName = clean((string)input('full_name'));
$phone    = clean((string)input('phone'));
$whatsapp = clean((string)input('whatsapp')) ?: $phone;
$email    = filter_var((string)input('email'), FILTER_VALIDATE_EMAIL) ?: '';
$courseId = (int)input('course_id', 0);

$errors = [];
if ($fullName === '')             $errors[] = 'Full name is required.';
if ($phone === '')                $errors[] = 'Phone number is required.';
if ($email === '')               $errors[] = 'A valid email is required.';
if ($courseId <= 0)              $errors[] = 'A course must be selected.';

if (!$errors) {
    $course = db_one("SELECT id FROM courses WHERE id = ? AND status = 'published'", [$courseId]);
    if (!$course) $errors[] = 'Selected course is not available.';
}

if ($errors) {
    json_response(['success' => false, 'message' => implode(' ', $errors)], 422);
}

$reference = 'SIZ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

try {
    $id = db_insert(
        "INSERT INTO applications (reference, course_id, full_name, phone, whatsapp, email, status, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)",
        [$reference, $courseId, $fullName, $phone, $whatsapp, $email, $_SERVER['REMOTE_ADDR'] ?? null]
    );
    log_activity('application_received', "New application {$reference} from {$fullName}", null);

    json_response([
        'success'   => true,
        'message'   => 'Application submitted successfully.',
        'id'        => $id,
        'reference' => $reference,
        'status'    => 'pending',
    ], 201);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => 'Could not submit application. Please try again.'], 500);
}
