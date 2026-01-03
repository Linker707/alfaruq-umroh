<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

// Ambil statistik utama
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
    'packages' => $pdo->query("SELECT COUNT(*) FROM packages WHERE is_active = 1")->fetchColumn(),
    'packages_popular' => $pdo->query("SELECT COUNT(*) FROM packages WHERE is_popular = 1 AND is_active = 1")->fetchColumn(),
    'testimonials' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_approved = 1")->fetchColumn(),
    'testimonials_pending' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_approved = 0")->fetchColumn(),
    'schedules' => $pdo->query("SELECT COUNT(*) FROM schedules WHERE status = 'available' AND departure_date >= CURDATE()")->fetchColumn(),
    'gallery' => $pdo->query("SELECT COUNT(*) FROM galleries WHERE is_active = 1")->fetchColumn(),
    'destinations' => $pdo->query("SELECT COUNT(*) FROM destinations WHERE is_active = 1")->fetchColumn()
];

// Ambil jadwal terdekat (7 hari ke depan)
$upcoming_schedules = $pdo->query("
    SELECT s.*, 
           (SELECT name FROM packages WHERE name = s.package_name LIMIT 1) as package_name_display
    FROM schedules s 
    WHERE s.departure_date >= CURDATE() 
    AND s.departure_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND s.status = 'available'
    ORDER BY s.departure_date ASC 
    LIMIT 5
")->fetchAll();

// Ambil testimoni terbaru
$latest_testimonials = $pdo->query("
    SELECT t.* 
    FROM testimonials t 
    WHERE t.is_approved = 1 
    ORDER BY t.created_at DESC 
    LIMIT 5
")->fetchAll();

// Ambil paket populer
$popular_packages = $pdo->query("
    SELECT p.*, 
           (SELECT COUNT(*) FROM package_prices WHERE package_id = p.id AND is_active = 1) as price_count
    FROM packages p 
    WHERE p.is_popular = 1 
    AND p.is_active = 1 
    ORDER BY p.updated_at DESC 
    LIMIT 3
")->fetchAll();

// Ambil aktivitas terakhir
$activities = $pdo->query("
    SELECT a.*, u.username, u.full_name 
    FROM activity_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 10
")->fetchAll();

// Get current month stats
$current_month = date('n');
$current_year = date('Y');
$monthly_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN action LIKE '%LOGIN%' THEN 1 ELSE 0 END) as logins,
        SUM(CASE WHEN action LIKE '%ADD%' THEN 1 ELSE 0 END) as additions,
        SUM(CASE WHEN action LIKE '%UPDATE%' THEN 1 ELSE 0 END) as updates,
        SUM(CASE WHEN action LIKE '%DELETE%' THEN 1 ELSE 0 END) as deletions
    FROM activity_logs 
    WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?
")->execute([$current_month, $current_year]);
$month_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN action LIKE '%LOGIN%' THEN 1 ELSE 0 END) as logins,
        SUM(CASE WHEN action LIKE '%ADD%' THEN 1 ELSE 0 END) as additions,
        SUM(CASE WHEN action LIKE '%UPDATE%' THEN 1 ELSE 0 END) as updates,
        SUM(CASE WHEN action LIKE '%DELETE%' THEN 1 ELSE 0 END) as deletions
    FROM activity_logs 
    WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?
")->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin ALFARUQ TEAM</title>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card.warning {
            border-left: 5px solid #FF9800;
        }
        
        .stat-card.success {
            border-left: 5px solid #4CAF50;
        }
        
        .stat-card.info {
            border-left: 5px solid #2196F3;
        }
        
        .stat-card.danger {
            border-left: 5px solid #F44336;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .stat-icon.warning {
            background: rgba(255, 152, 0, 0.1);
            color: #FF9800;
        }
        
        .stat-icon.success {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .stat-icon.info {
            background: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }
        
        .stat-icon.danger {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .stat-subtext {
            font-size: 0.8rem;
            color: #999;
        }
        
        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .quick-link {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border-radius: 10px;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            flex: 1;
            min-width: 200px;
        }
        
        .quick-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: #4CAF50;
        }
        
        .quick-link i {
            font-size: 1.2rem;
            color: #4CAF50;
        }
        
        .quick-link-content h6 {
            margin: 0;
            font-weight: 600;
        }
        
        .quick-link-content small {
            color: #666;
            font-size: 0.8rem;
        }
        
        .dashboard-section {
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .section-header h5 {
            margin: 0;
            color: #2E7D32;
            font-weight: 600;
        }
        
        .see-all {
            color: #4CAF50;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .see-all:hover {
            color: #2E7D32;
        }
        
        .upcoming-schedule {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .upcoming-schedule:hover {
            border-color: #4CAF50;
            box-shadow: 0 2px 10px rgba(76, 175, 80, 0.1);
        }
        
        .schedule-date {
            font-weight: bold;
            color: #2E7D32;
            font-size: 1.1rem;
        }
        
        .schedule-airline {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .schedule-slots {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .schedule-slots.available {
            background: rgba(76, 175, 80, 0.1);
            color: #2E7D32;
        }
        
        .schedule-slots.low {
            background: rgba(255, 152, 0, 0.1);
            color: #FF9800;
        }
        
        .schedule-slots.full {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        
        .package-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .package-card:hover {
            transform: translateY(-5px);
        }
        
        .package-image {
            height: 150px;
            overflow: hidden;
        }
        
        .package-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .package-info {
            padding: 15px;
        }
        
        .package-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }
        
        .package-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #666;
        }
        
        .testimonial-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #4CAF50;
        }
        
        .testimonial-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .testimonial-message {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .testimonial-date {
            font-size: 0.8rem;
            color: #999;
            text-align: right;
        }
        
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-action {
            font-weight: 500;
            color: #333;
        }
        
        .activity-details {
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
        }
        
        .activity-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #999;
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(76, 175, 80, 0.3);
        }
        
        .welcome-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .last-login {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid mt-4">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-title">
                <i class="fas fa-hand-sparkles me-2"></i>Selamat Datang, <?php echo htmlspecialchars($_SESSION['admin_full_name']); ?>!
            </div>
            <div class="welcome-subtitle">
                Ini adalah Admin Panel ALFARUQ TEAM - Travel Umroh Professional
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="last-login">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Login terakhir:</strong> 
                    <?php 
                    $last_login_stmt = $pdo->prepare("SELECT last_login FROM users WHERE id = ?");
                    $last_login_stmt->execute([$_SESSION['admin_id']]);
                    $last_login = $last_login_stmt->fetchColumn();
                    echo $last_login ? date('d M Y H:i', strtotime($last_login)) : 'Pertama kali login';
                    ?>
                </div>
                <div class="text-end">
                    <small>
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?php echo date('l, d F Y'); ?>
                    </small>
                    <br>
                    <small>
                        <i class="fas fa-clock me-1"></i>
                        <?php echo date('H:i'); ?> WIB
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stats-grid row-responsive">
    <!-- Users -->
            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $stats['users']; ?></div>
                <div class="stat-label">Total Users Aktif</div>
                <div class="stat-subtext">
                    <?php 
                    $master_admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'master_admin' AND is_active = 1")->fetchColumn();
                    $admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1")->fetchColumn();
                    ?>
                    <i class="fas fa-crown me-1"></i><?php echo $master_admin_count; ?> Master
                    <i class="fas fa-user ms-3 me-1"></i><?php echo $admin_count; ?> Admin
                </div>
            </div>
            <!-- Users -->
            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $stats['users']; ?></div>
                <div class="stat-label">Total Users Aktif</div>
                <div class="stat-subtext">
                    <?php 
                    $master_admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'master_admin' AND is_active = 1")->fetchColumn();
                    $admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1")->fetchColumn();
                    ?>
                    <i class="fas fa-crown me-1"></i><?php echo $master_admin_count; ?> Master
                    <i class="fas fa-user ms-3 me-1"></i><?php echo $admin_count; ?> Admin
                </div>
            </div>
            
            <!-- Packages -->
            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-number"><?php echo $stats['packages']; ?></div>
                <div class="stat-label">Paket Umroh Aktif</div>
                <div class="stat-subtext">
                    <i class="fas fa-star me-1"></i><?php echo $stats['packages_popular']; ?> Paket Populer
                </div>
            </div>
            
            <!-- Testimonials -->
            <div class="stat-card warning">
                <div class="stat-icon warning">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-number"><?php echo $stats['testimonials']; ?></div>
                <div class="stat-label">Testimoni Disetujui</div>
                <div class="stat-subtext">
                    <i class="fas fa-clock me-1"></i><?php echo $stats['testimonials_pending']; ?> Menunggu Persetujuan
                </div>
            </div>
            
            <!-- Schedules -->
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-number"><?php echo $stats['schedules']; ?></div>
                <div class="stat-label">Jadwal Tersedia</div>
                <div class="stat-subtext">
                    <i class="fas fa-plane-departure me-1"></i><?php echo count($upcoming_schedules); ?> Jadwal Mendatang
                </div>
            </div>
            
            <!-- Gallery -->
            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-number"><?php echo $stats['gallery']; ?></div>
                <div class="stat-label">Foto Galeri</div>
                <div class="stat-subtext">
                    <?php 
                    $destinations_count = $pdo->query("SELECT COUNT(DISTINCT destination_id) FROM galleries WHERE destination_id IS NOT NULL")->fetchColumn();
                    ?>
                    <i class="fas fa-map-marker-alt me-1"></i><?php echo $destinations_count; ?> Destinasi
                </div>
            </div>
            
            <!-- Destinations -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="stat-number"><?php echo $stats['destinations']; ?></div>
                <div class="stat-label">Destinasi Aktif</div>
                <div class="stat-subtext">
                    <?php 
                    $total_gallery = $pdo->query("SELECT COUNT(*) FROM galleries WHERE is_active = 1")->fetchColumn();
                    ?>
                    <i class="fas fa-image me-1"></i><?php echo $total_gallery; ?> Total Foto
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="dashboard-section">
            <div class="section-header">
                <h5><i class="fas fa-bolt me-2"></i>Tautan Cepat</h5>
            </div>
            <div class="quick-links row-responsive">
                <a href="package.php" class="quick-link">
                    <i class="fas fa-box-open"></i>
                    <div class="quick-link-content">
                        <h6>Kelola Paket</h6>
                        <small>Tambahkan atau edit paket umroh</small>
                    </div>
                </a>
                
                <a href="schedule.php" class="quick-link">
                    <i class="fas fa-calendar-plus"></i>
                    <div class="quick-link-content">
                        <h6>Tambah Jadwal</h6>
                        <small>Buat jadwal keberangkatan baru</small>
                    </div>
                </a>
                
                <a href="testimonial.php" class="quick-link">
                    <i class="fas fa-comment-check"></i>
                    <div class="quick-link-content">
                        <h6>Setujui Testimoni</h6>
                        <small><?php echo $stats['testimonials_pending']; ?> testimoni menunggu</small>
                    </div>
                </a>
                
                <a href="gallery.php" class="quick-link">
                    <i class="fas fa-image"></i>
                    <div class="quick-link-content">
                        <h6>Upload Galeri</h6>
                        <small>Tambahkan foto perjalanan</small>
                    </div>
                </a>
                
                <a href="user.php" class="quick-link">
                    <i class="fas fa-user-plus"></i>
                    <div class="quick-link-content">
                        <h6>Tambah User</h6>
                        <small>Buat akun admin baru</small>
                    </div>
                </a>
                
                <a href="profile.php" class="quick-link">
                    <i class="fas fa-building"></i>
                    <div class="quick-link-content">
                        <h6>Profil Perusahaan</h6>
                        <small>Edit informasi perusahaan</small>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="row">
            <div class="col-lg-6">
                <!-- Upcoming Schedules -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h5><i class="fas fa-plane-departure me-2"></i>Jadwal Keberangkatan Mendatang</h5>
                        <a href="schedule.php" class="see-all">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    
                    <?php if (empty($upcoming_schedules)): ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-times"></i>
                            <p>Tidak ada jadwal mendatang</p>
                            <a href="schedule.php" class="btn btn-sm btn-outline-success">Buat Jadwal Baru</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_schedules as $schedule): 
                            $slots_class = $schedule['available_slots'] > 20 ? 'available' : ($schedule['available_slots'] > 0 ? 'low' : 'full');
                        ?>
                        <div class="upcoming-schedule">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="schedule-date">
                                        <?php echo date('d M Y', strtotime($schedule['departure_date'])); ?>
                                    </div>
                                    <div class="schedule-airline">
                                        <i class="fas fa-plane me-1"></i>
                                        <?php echo htmlspecialchars($schedule['airline']); ?>
                                        <?php if ($schedule['package_name_display']): ?>
                                            <span class="ms-2 badge bg-light-green">
                                                <?php echo htmlspecialchars($schedule['package_name_display']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="schedule-slots <?php echo $slots_class; ?>">
                                    <?php echo $schedule['available_slots']; ?> slot
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-route me-1"></i>
                                    <?php echo htmlspecialchars($schedule['route']); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo $schedule['duration_days']; ?> hari
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Latest Testimonials -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h5><i class="fas fa-comment-medical me-2"></i>Testimoni Terbaru</h5>
                        <a href="testimonial.php" class="see-all">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    
                    <?php if (empty($latest_testimonials)): ?>
                        <div class="no-data">
                            <i class="fas fa-comment-slash"></i>
                            <p>Belum ada testimoni</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($latest_testimonials as $testimonial): 
                            $stars = str_repeat('★', $testimonial['rating']) . str_repeat('☆', 5 - $testimonial['rating']);
                        ?>
                        <div class="testimonial-item">
                            <div class="testimonial-name">
                                <?php echo htmlspecialchars($testimonial['name']); ?>
                                <span class="text-warning ms-2"><?php echo $stars; ?></span>
                            </div>
                            <div class="testimonial-message">
                                <?php echo substr(strip_tags($testimonial['message']), 0, 100); ?>...
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php echo date('d M Y', strtotime($testimonial['created_at'])); ?>
                                </small>
                                <a href="testimonial.php?action=edit&id=<?php echo $testimonial['id']; ?>" 
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-6">
                <!-- Popular Packages -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h5><i class="fas fa-star me-2"></i>Paket Populer</h5>
                        <a href="package.php" class="see-all">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    
                    <?php if (empty($popular_packages)): ?>
                        <div class="no-data">
                            <i class="fas fa-box-open"></i>
                            <p>Belum ada paket populer</p>
                            <a href="package.php" class="btn btn-sm btn-outline-success">Buat Paket Baru</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($popular_packages as $package): ?>
                            <div class="col-md-6 mb-3">
                                <div class="package-card">
                                    <?php if ($package['image']): ?>
                                    <div class="package-image">
                                        <img src="../<?php echo htmlspecialchars($package['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($package['name']); ?>">
                                    </div>
                                    <?php endif; ?>
                                    <div class="package-info">
                                        <div class="package-name">
                                            <?php echo htmlspecialchars($package['name']); ?>
                                        </div>
                                        <div class="package-stats">
                                            <span>
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo $package['duration']; ?> hari
                                            </span>
                                            <span>
                                                <i class="fas fa-tag me-1"></i>
                                                <?php echo $package['price_count']; ?> harga
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <a href="package.php?action=edit&id=<?php echo $package['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary w-100">
                                                Kelola Paket
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Activities -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h5><i class="fas fa-history me-2"></i>Aktivitas Terakhir</h5>
                        <a href="javascript:void(0)" onclick="window.location.reload()" class="see-all">
                            <i class="fas fa-redo me-1"></i> Refresh
                        </a>
                    </div>
                    
                    <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                        <?php if (empty($activities)): ?>
                            <div class="no-data">
                                <i class="fas fa-history"></i>
                                <p>Belum ada aktivitas</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($activities as $log): 
                                $action_icon = match(true) {
                                    strpos($log['action'], 'LOGIN') !== false => 'fas fa-sign-in-alt text-success',
                                    strpos($log['action'], 'ADD') !== false => 'fas fa-plus-circle text-success',
                                    strpos($log['action'], 'UPDATE') !== false => 'fas fa-edit text-warning',
                                    strpos($log['action'], 'DELETE') !== false => 'fas fa-trash text-danger',
                                    strpos($log['action'], 'CHANGE') !== false => 'fas fa-exchange-alt text-info',
                                    default => 'fas fa-history text-secondary'
                                };
                            ?>
                            <div class="activity-item">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="me-3">
                                        <i class="<?php echo $action_icon; ?> fa-lg"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="activity-action">
                                            <?php echo htmlspecialchars($log['full_name'] ?? $log['username'] ?? 'System'); ?>
                                            <span class="text-muted">telah</span>
                                            <?php echo htmlspecialchars(strtolower(str_replace('_', ' ', $log['action']))); ?>
                                        </div>
                                        <?php if ($log['details']): ?>
                                        <div class="activity-details">
                                            <?php echo htmlspecialchars($log['details']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="activity-meta">
                                            <span>
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('H:i', strtotime($log['created_at'])); ?>
                                            </span>
                                            <span>
                                                <?php echo date('d M Y', strtotime($log['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Notes -->
        <div class="admin-card mt-4">
            <div class="card-body text-center">
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Admin Panel ALFARUQ TEAM • 
                    Total <?php echo $stats['users']; ?> User • 
                    <?php echo $stats['packages']; ?> Paket • 
                    <?php echo $stats['testimonials']; ?> Testimoni • 
                    <?php echo $stats['gallery']; ?> Foto
                    • Terakhir diupdate: <?php echo date('d M Y H:i'); ?>
                </p>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Auto-refresh page every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000); // 5 minutes
        
        // Tooltip initialization
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Update current time every minute
            function updateCurrentTime() {
                const now = new Date();
                const timeElement = document.querySelector('.welcome-card .text-end small:nth-child(2)');
                if (timeElement) {
                    timeElement.innerHTML = `<i class="fas fa-clock me-1"></i>${now.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})} WIB`;
                }
            }
            
            // Update time immediately and then every minute
            updateCurrentTime();
            setInterval(updateCurrentTime, 60000);
        });
    </script>
</body>
</html>