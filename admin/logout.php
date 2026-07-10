<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_admin_logged_in()) {
    log_activity('admin_logout', 'Admin logged out');
}
admin_logout();
flash('info', 'You have been signed out.');
redirect('admin/login.php');
