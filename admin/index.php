<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

// Ambil statistik
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'packages' => $pdo->query("SELECT COUNT(*) FROM packages WHERE is_active = 1")->fetchColumn(),
    'testimonials' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_approved = 1")->fetchColumn(),
    'schedules' => $pdo->query("SELECT COUNT(*) FROM schedules WHERE status = 'available'")->fetchColumn()
];

// Ambil aktivitas terakhir
$activities = $pdo->query("SELECT a.*, u.username FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin ALFARUQ TEAM</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid mt-4">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="admin-card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Users</h6>
                                <h3 class="fw-bold text-dark"><?php echo $stats['users']; ?></h3>
                            </div>
                            <div class="rounded-circle bg-light-green p-3">
                                <i class="fas fa-users fa-2x text-primary-green"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="users/index.php" class="btn-admin-outline btn-sm w-100">
                                Kelola Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="admin-card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Paket Aktif</h6>
                                <h3 class="fw-bold text-dark"><?php echo $stats['packages']; ?></h3>
                            </div>
                            <div class="rounded-circle bg-light-green p-3">
                                <i class="fas fa-box-open fa-2x text-primary-green"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="../packages.php" target="_blank" class="btn-admin-outline btn-sm w-100">
                                Lihat Paket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="admin-card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Testimoni</h6>
                                <h3 class="fw-bold text-dark"><?php echo $stats['testimonials']; ?></h3>
                            </div>
                            <div class="rounded-circle bg-light-green p-3">
                                <i class="fas fa-comments fa-2x text-primary-green"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="../testimonial-qna.php" target="_blank" class="btn-admin-outline btn-sm w-100">
                                Lihat Testimoni
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="admin-card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Jadwal Tersedia</h6>
                                <h3 class="fw-bold text-dark"><?php echo $stats['schedules']; ?></h3>
                            </div>
                            <div class="rounded-circle bg-light-green p-3">
                                <i class="fas fa-calendar-alt fa-2x text-primary-green"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="../package-detail.php" target="_blank" class="btn-admin-outline btn-sm w-100">
                                Lihat Jadwal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Aktivitas Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activities)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada aktivitas</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>User</th>
                                            <th>Aksi</th>
                                            <th>Detail</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activities as $log): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d M Y', strtotime($log['created_at'])); ?>
                                                </small>
                                                <br>
                                                <small><?php echo date('H:i', strtotime($log['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="fw-medium">
                                                    <?php echo htmlspecialchars($log['username'] ?? 'System'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light-green text-dark">
                                                    <?php echo htmlspecialchars($log['action']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($log['details'] ?? '-'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <code class="text-muted">
                                                    <?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?>
                                                </code>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>