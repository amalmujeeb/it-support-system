<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    header('Location: ' . dashboard_url(current_user()['role']));
} else {
    header('Location: /it-support-system/login.php');
}
exit;
