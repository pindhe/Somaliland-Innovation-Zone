<?php
/**
 * SIZSR - WhatsApp Cloud API integration.
 *
 * Sends text messages through Meta's official WhatsApp Cloud (Graph) API.
 * Credentials are read from environment variables first (see .env.example),
 * falling back to values saved in the admin Settings page.
 *
 * IMPORTANT: This only sends the course group *invite link* as a text message.
 * It never attempts to auto-add the student to a WhatsApp group.
 */

declare(strict_types=1);

/**
 * Resolve a WhatsApp config value: ENV var wins, then settings table, then default.
 */
function wa_config(string $envKey, string $settingKey, string $default = ''): string
{
    $env = getenv($envKey);
    if ($env !== false && $env !== '') {
        return (string)$env;
    }
    $val = function_exists('setting') ? setting($settingKey, '') : '';
    return $val !== '' ? $val : $default;
}

/**
 * Full set of credentials/config used by the sender.
 */
function wa_credentials(): array
{
    return [
        'enabled'      => filter_var(wa_config('WHATSAPP_ENABLED', 'whatsapp_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
        'token'        => wa_config('WHATSAPP_TOKEN', 'whatsapp_token'),
        'phone_id'     => wa_config('WHATSAPP_PHONE_NUMBER_ID', 'whatsapp_phone_number_id'),
        'version'      => wa_config('WHATSAPP_API_VERSION', 'whatsapp_api_version', 'v21.0'),
        'default_cc'   => preg_replace('/\D/', '', wa_config('WHATSAPP_DEFAULT_COUNTRY_CODE', 'whatsapp_default_country_code', '252')),
    ];
}

/**
 * Is the integration configured and switched on?
 */
function wa_enabled(): bool
{
    $c = wa_credentials();
    return $c['enabled'] && $c['token'] !== '' && $c['phone_id'] !== '';
}

/**
 * Normalise a phone number to WhatsApp's expected E.164 digits (no +).
 * - Strips spaces, dashes, parentheses and a leading +.
 * - Converts a leading 00 (international prefix) to nothing.
 * - Converts a leading 0 (local trunk) to the default country code.
 * - Prepends the default country code when the number is clearly local.
 */
function wa_normalize_phone(string $phone, string $defaultCc = '252'): string
{
    $digits = preg_replace('/\D/', '', $phone);
    if ($digits === '') {
        return '';
    }
    if (str_starts_with($digits, '00')) {
        $digits = substr($digits, 2);
    } elseif (str_starts_with($digits, '0')) {
        $digits = $defaultCc . substr($digits, 1);
    } elseif ($defaultCc !== '' && !str_starts_with($digits, $defaultCc) && strlen($digits) <= 9) {
        $digits = $defaultCc . $digits;
    }
    return $digits;
}

/**
 * Build the approval notification message for a student.
 */
function wa_build_approval_message(string $studentName, string $courseName, string $groupLink): string
{
    return "Congratulations {$studentName}\n\n"
        . "Waxad ka mid noqotay {$courseName} ardaydii la qaatay.\n\n"
        . "join Group Class:\n"
        . "{$groupLink}\n\n";
}

/**
 * Build a click-to-send wa.me link that opens WhatsApp (web/app) with the
 * message pre-filled. Works without any API — the admin taps "send" themselves.
 * Returns '' when there is no usable phone number.
 */
function wa_click_link(string $phone, string $message, string $defaultCc = '252'): string
{
    $normalized = wa_normalize_phone($phone, $defaultCc);
    if ($normalized === '') {
        return '';
    }
    return 'https://wa.me/' . $normalized . '?text=' . rawurlencode($message);
}

/**
 * Convenience: build the wa.me link for an application's approval message.
 * Returns ['url' => string, 'reason' => ?string].
 */
function wa_click_link_for_application(array $app): array
{
    $link = trim((string)($app['whatsapp_group_link'] ?? ''));
    if ($link === '') {
        return ['url' => '', 'reason' => 'No WhatsApp group link is set for this course.'];
    }
    $phone = trim((string)($app['whatsapp'] ?? '')) ?: trim((string)($app['phone'] ?? ''));
    if ($phone === '') {
        return ['url' => '', 'reason' => 'Student has no phone/WhatsApp number.'];
    }
    $cc = preg_replace('/\D/', '', wa_config('WHATSAPP_DEFAULT_COUNTRY_CODE', 'whatsapp_default_country_code', '252'));
    $message = wa_build_approval_message(
        (string)($app['full_name'] ?? ''),
        (string)($app['course_title'] ?? 'your course'),
        $link
    );
    return ['url' => wa_click_link($phone, $message, $cc), 'reason' => null];
}

/**
 * Persist an entry in the whatsapp_logs audit table (best effort).
 */
function wa_log(?int $applicationId, string $phone, string $message, string $status, ?string $providerId = null, ?string $error = null): void
{
    try {
        db_exec(
            "INSERT INTO whatsapp_logs (application_id, phone, message, status, provider_message_id, error)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$applicationId, $phone, $message, $status, $providerId, $error]
        );
    } catch (Throwable $e) {
        // Logging must never break the request.
    }
}

/**
 * Send a plain-text WhatsApp message via the Cloud API.
 *
 * @return array{success:bool, message_id:?string, error:?string, to:string}
 */
function wa_send_text(string $to, string $message, ?int $applicationId = null): array
{
    $cred = wa_credentials();
    $normalized = wa_normalize_phone($to, $cred['default_cc']);

    if (!$cred['enabled'] || $cred['token'] === '' || $cred['phone_id'] === '') {
        $err = 'WhatsApp API is not configured or disabled.';
        wa_log($applicationId, $normalized, $message, 'failed', null, $err);
        return ['success' => false, 'message_id' => null, 'error' => $err, 'to' => $normalized];
    }
    if ($normalized === '') {
        $err = 'No valid phone number to send to.';
        wa_log($applicationId, $to, $message, 'failed', null, $err);
        return ['success' => false, 'message_id' => null, 'error' => $err, 'to' => $to];
    }
    if (!function_exists('curl_init')) {
        $err = 'PHP cURL extension is not enabled on this server.';
        wa_log($applicationId, $normalized, $message, 'failed', null, $err);
        return ['success' => false, 'message_id' => null, 'error' => $err, 'to' => $normalized];
    }

    $url = sprintf('https://graph.facebook.com/%s/%s/messages', $cred['version'], $cred['phone_id']);
    $payload = json_encode([
        'messaging_product' => 'whatsapp',
        'recipient_type'    => 'individual',
        'to'                => $normalized,
        'type'              => 'text',
        'text'              => ['preview_url' => true, 'body' => $message],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $cred['token'],
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        $err = 'Network error: ' . $curlErr;
        wa_log($applicationId, $normalized, $message, 'failed', null, $err);
        return ['success' => false, 'message_id' => null, 'error' => $err, 'to' => $normalized];
    }

    $data = json_decode((string)$response, true);

    if ($httpCode >= 200 && $httpCode < 300 && isset($data['messages'][0]['id'])) {
        $messageId = (string)$data['messages'][0]['id'];
        wa_log($applicationId, $normalized, $message, 'sent', $messageId, null);
        return ['success' => true, 'message_id' => $messageId, 'error' => null, 'to' => $normalized];
    }

    $err = $data['error']['message'] ?? ('Unexpected API response (HTTP ' . $httpCode . ').');
    wa_log($applicationId, $normalized, $message, 'failed', null, $err);
    return ['success' => false, 'message_id' => null, 'error' => $err, 'to' => $normalized];
}

/**
 * Approve-time notification: look up the application + its course group link,
 * build the standard message and send it.
 *
 * @return array{success:bool, error:?string, skipped:bool, to:?string}
 */
function notify_application_approved(int $applicationId): array
{
    $app = db_one(
        "SELECT a.id, a.full_name, a.phone, a.whatsapp, c.title AS course_title, c.whatsapp_group_link
         FROM applications a LEFT JOIN courses c ON c.id = a.course_id
         WHERE a.id = ?",
        [$applicationId]
    );

    if (!$app) {
        return ['success' => false, 'error' => 'Application not found.', 'skipped' => false, 'to' => null];
    }

    $link = trim((string)($app['whatsapp_group_link'] ?? ''));
    if ($link === '') {
        return ['success' => false, 'error' => 'No WhatsApp group link is set for this course.', 'skipped' => true, 'to' => null];
    }

    $phone = trim((string)($app['whatsapp'] ?? '')) ?: trim((string)($app['phone'] ?? ''));
    if ($phone === '') {
        return ['success' => false, 'error' => 'Student has no phone/WhatsApp number.', 'skipped' => true, 'to' => null];
    }

    $message = wa_build_approval_message(
        (string)$app['full_name'],
        (string)($app['course_title'] ?? 'your course'),
        $link
    );

    $res = wa_send_text($phone, $message, $applicationId);
    return ['success' => $res['success'], 'error' => $res['error'], 'skipped' => false, 'to' => $res['to']];
}
