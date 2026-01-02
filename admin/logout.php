<?php
session_start();
require_once '../config/database.php';

// Log aktivitas jika user logged in
if (isset($_SESSION['admin_id'])) {
    $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, ip_address, user_agent) VALUES (?, 'LOGOUT', ?, ?)");
    $log_stmt->execute([$_SESSION['admin_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
}

session_destroy();
header('Location: login.php');
exit;
?>