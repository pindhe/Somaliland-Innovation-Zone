<?php
/**
 * SIZSR - Newsletter subscription AJAX endpoint
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

if (!is_post()) {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

csrf_check();

$email = filter_var((string)input('email'), FILTER_VALIDATE_EMAIL) ?: '';

if ($email === '') {
    json_response(['success' => false, 'message' => 'Please enter a valid email address.']);
}

try {
    db_exec(
        "INSERT INTO newsletter_subscribers (email, status) VALUES (?, 'subscribed')
         ON DUPLICATE KEY UPDATE status = 'subscribed'",
        [$email]
    );
    json_response(['success' => true, 'message' => 'You are subscribed! Thank you.']);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => 'Subscription failed. Please try again.'], 500);
}
