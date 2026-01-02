<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Security check tambahan
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// Cek apakah user masih aktif di database (opsional)
require_once '../config/database.php';
$stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_active']) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}
?>