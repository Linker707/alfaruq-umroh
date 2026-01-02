<?php
require_once '../../includes/auth.php';
checkAccess('admin');

// Validate CSRF token
$token = $_GET['token'] ?? '';
if (!validateCsrfToken($token)) {
    die('Invalid CSRF token');
}

// Get schedule ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    // Delete schedule
    $query = "DELETE FROM schedules WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    
    // Log activity
    logActivity('DELETE_SCHEDULE', "Menghapus jadwal ID: $id");
    
    $_SESSION['success_message'] = 'Jadwal berhasil dihapus!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Gagal menghapus jadwal: ' . $e->getMessage();
}

header('Location: index.php');
exit;
?>