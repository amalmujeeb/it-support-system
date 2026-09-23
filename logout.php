<?php
require_once __DIR__ . '/includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    http_response_code(405);
    exit('Invalid logout request.');
}
logout_user();
header('Location: /it-support-system/login.php');
exit;
