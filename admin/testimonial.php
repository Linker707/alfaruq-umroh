<?php
require_once 'includes/auth.php';

// Hanya admin yang bisa akses (master_admin dan admin)
if ($_SESSION['admin_role'] !== 'master_admin' && $_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Default action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$message = '';
$error = '';

// Filter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Handle Export
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    exportTestimonialsToExcel();
    exit;
} elseif (isset($_GET['export']) && $_GET['export'] == 'csv') {
    exportTestimonialsToCSV();
    exit;
}

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'update':
            $process = processUpdateTestimonial($id);
            if ($process['success']) {
                $_SESSION['success_message'] = $process['message'];
                header('Location: testimonial.php');
                exit;
            }
            $error = $process['message'];
            break;
    }
}

// Process GET actions
if ($action == 'approve' && $id > 0) {
    $process = processApproveTestimonial($id);
    if ($process['success']) {
        $_SESSION['success_message'] = $process['message'];
        header('Location: testimonial.php');
        exit;
    }
    $error = $process['message'];
} elseif ($action == 'delete' && $id > 0) {
    $process = processDeleteTestimonial($id);
    if ($process['success']) {
        $_SESSION['success_message'] = $process['message'];
        header('Location: testimonial.php');
        exit;
    }
    $error = $process['message'];
}

// Get message from session
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Get testimonial data for edit/view
$testimonial = null;
if ($id > 0 && ($action == 'edit' || $action == 'view')) {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $testimonial = $stmt->fetch();
    
    if (!$testimonial) {
        header('Location: testimonial.php');
        exit;
    }
}

// Build query based on filters
$where = [];
$params = [];

// Filter by status
if ($status == 'approved') {
    $where[] = "is_approved = 1";
} elseif ($status == 'pending') {
    $where[] = "is_approved = 0";
} elseif ($status == 'featured') {
    $where[] = "rating = 5 AND is_approved = 1";
}

// Filter by start date
if (!empty($start_date)) {
    $where[] = "DATE(created_at) >= ?";
    $params[] = $start_date;
}

// Filter by end date
if (!empty($end_date)) {
    $where[] = "DATE(created_at) <= ?";
    $params[] = $end_date;
}

// Build WHERE clause
$where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Get all testimonials for list with filters
$sql = "SELECT * FROM testimonials $where_clause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$testimonials = $stmt->fetchAll();

// Get statistics with filters
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN rating = 5 AND is_approved = 1 THEN 1 ELSE 0 END) as featured,
    AVG(CASE WHEN is_approved = 1 THEN rating ELSE NULL END) as avg_rating
    FROM testimonials";

if (!empty($where_clause)) {
    $stats_sql .= " $where_clause";
}

$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute($params);
$stats = $stats_stmt->fetch();

// Function: Export to Excel
function exportTestimonialsToExcel() {
    global $pdo, $start_date, $end_date, $status;
    
    // Build query with filters
    $where = [];
    $params = [];
    
    if ($status == 'approved') {
        $where[] = "is_approved = 1";
    } elseif ($status == 'pending') {
        $where[] = "is_approved = 0";
    } elseif ($status == 'featured') {
        $where[] = "rating = 5 AND is_approved = 1";
    }
    
    if (!empty($start_date)) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $start_date;
    }
    
    if (!empty($end_date)) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $end_date;
    }
    
    $where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get all data
    $sql = "SELECT * FROM testimonials $where_clause ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $testimonials = $stmt->fetchAll();
    
    // Set headers for Excel file
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="testimonials_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Start Excel content
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Nama</th>";
    echo "<th>Email</th>";
    echo "<th>Telepon</th>";
    echo "<th>Rating</th>";
    echo "<th>Testimoni</th>";
    echo "<th>Status</th>";
    echo "<th>Tanggal Dibuat</th>";
    echo "<th>Tanggal Keberangkatan</th>";
    echo "<th>Q1 (Nama Lengkap)</th>";
    echo "<th>Q2 (Telepon)</th>";
    echo "<th>Q3 (Berangkat dengan)</th>";
    echo "<th>Q4 (Info dari)</th>";
    echo "<th>Q5 (Alasan pilih)</th>";
    echo "<th>Q6 (Kepuasan penjelasan admin)</th>";
    echo "<th>Q7 (Alasan rating admin)</th>";
    echo "<th>Q8 (Kepuasan manasik)</th>";
    echo "<th>Q9 (Alasan rating manasik)</th>";
    echo "<th>Q10 (Kepuasan tour)</th>";
    echo "<th>Q11 (Alasan rating tour)</th>";
    echo "<th>Q12 (Kepuasan makanan)</th>";
    echo "<th>Q13 (Alasan rating makanan)</th>";
    echo "<th>Q14 (Kepuasan pembimbing ibadah)</th>";
    echo "<th>Q15 (Alasan rating pembimbing)</th>";
    echo "<th>Q16 (Kepuasan muthawif)</th>";
    echo "<th>Q17 (Alasan rating muthawif)</th>";
    echo "<th>Q18 (Kepuasan itinerary)</th>";
    echo "<th>Q19 (Alasan rating itinerary)</th>";
    echo "<th>Q20 (Rekomendasi ke orang lain)</th>";
    echo "<th>Q21 (Alasan rekomendasi)</th>";
    echo "<th>Q22 (Kesan pesan)</th>";
    echo "<th>Q23 (Saran perbaikan)</th>";
    echo "</tr>";
    
    foreach ($testimonials as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['id']) . "</td>";
        echo "<td>" . htmlspecialchars($item['name']) . "</td>";
        echo "<td>" . htmlspecialchars($item['email']) . "</td>";
        echo "<td>" . htmlspecialchars($item['q2'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['rating']) . "/5</td>";
        echo "<td>" . htmlspecialchars($item['message']) . "</td>";
        echo "<td>" . ($item['is_approved'] ? 'Disetujui' : 'Menunggu') . "</td>";
        echo "<td>" . date('d-m-Y H:i', strtotime($item['created_at'])) . "</td>";
        echo "<td>" . ($item['departure_date'] ? date('d-m-Y', strtotime($item['departure_date'])) : '') . "</td>";
        
        // Q1-Q23
        for ($i = 1; $i <= 23; $i++) {
            echo "<td>" . htmlspecialchars($item['q' . $i] ?? '') . "</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    exit;
}

// Function: Export to CSV
function exportTestimonialsToCSV() {
    global $pdo, $start_date, $end_date, $status;
    
    // Build query with filters
    $where = [];
    $params = [];
    
    if ($status == 'approved') {
        $where[] = "is_approved = 1";
    } elseif ($status == 'pending') {
        $where[] = "is_approved = 0";
    } elseif ($status == 'featured') {
        $where[] = "rating = 5 AND is_approved = 1";
    }
    
    if (!empty($start_date)) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $start_date;
    }
    
    if (!empty($end_date)) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $end_date;
    }
    
    $where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get all data
    $sql = "SELECT * FROM testimonials $where_clause ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $testimonials = $stmt->fetchAll();
    
    // Set headers for CSV file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="testimonials_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Headers
    $headers = [
        'ID', 'Nama', 'Email', 'Telepon', 'Rating', 'Testimoni', 'Status',
        'Tanggal Dibuat', 'Tanggal Keberangkatan', 'Nama Lengkap', 'Telepon',
        'Berangkat dengan', 'Info dari', 'Alasan pilih', 'Kepuasan penjelasan admin',
        'Alasan rating admin', 'Kepuasan manasik', 'Alasan rating manasik',
        'Kepuasan tour', 'Alasan rating tour', 'Kepuasan makanan',
        'Alasan rating makanan', 'Kepuasan pembimbing ibadah',
        'Alasan rating pembimbing', 'Kepuasan muthawif', 'Alasan rating muthawif',
        'Kepuasan itinerary', 'Alasan rating itinerary', 'Rekomendasi ke orang lain',
        'Alasan rekomendasi', 'Kesan pesan', 'Saran perbaikan'
    ];
    
    fputcsv($output, $headers);
    
    // Data rows
    foreach ($testimonials as $item) {
        $row = [
            $item['id'],
            $item['name'],
            $item['email'],
            $item['q2'] ?? '',
            $item['rating'] . '/5',
            $item['message'],
            $item['is_approved'] ? 'Disetujui' : 'Menunggu',
            date('d-m-Y H:i', strtotime($item['created_at'])),
            $item['departure_date'] ? date('d-m-Y', strtotime($item['departure_date'])) : '',
        ];
        
        // Add Q1-Q23
        for ($i = 1; $i <= 23; $i++) {
            $row[] = $item['q' . $i] ?? '';
        }
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Function: Update Testimonial
function processUpdateTestimonial($id) {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($message_text)) {
        return ['success' => false, 'message' => 'Nama dan testimoni harus diisi'];
    }
    
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Rating harus antara 1-5'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update testimonial
        $stmt = $pdo->prepare("UPDATE testimonials SET name = ?, email = ?, message = ?, rating = ?, is_approved = ? WHERE id = ?");
        $stmt->execute([$name, $email, $message_text, $rating, $is_approved, $id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_TESTIMONIAL', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Memperbarui testimoni dari: $name"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Testimoni berhasil diperbarui'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Approve Testimonial
function processApproveTestimonial($id) {
    global $pdo;
    
    // Get testimonial info for log
    $stmt = $pdo->prepare("SELECT name FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $testimonial = $stmt->fetch();
    
    if (!$testimonial) {
        return ['success' => false, 'message' => 'Testimoni tidak ditemukan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Approve testimonial
        $stmt = $pdo->prepare("UPDATE testimonials SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'APPROVE_TESTIMONIAL', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menyetujui testimoni dari: {$testimonial['name']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Testimoni berhasil disetujui'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Delete Testimonial
function processDeleteTestimonial($id) {
    global $pdo;
    
    // Get testimonial info for log
    $stmt = $pdo->prepare("SELECT name FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $testimonial = $stmt->fetch();
    
    if (!$testimonial) {
        return ['success' => false, 'message' => 'Testimoni tidak ditemukan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete testimonial
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'DELETE_TESTIMONIAL', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menghapus testimoni dari: {$testimonial['name']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Testimoni berhasil dihapus'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Testimoni - Admin ALFARUQ TEAM</title>
    <style>
        .testimonial-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f9f9f9;
            transition: all 0.3s;
        }
        
        .testimonial-card:hover {
            border-color: #4CAF50;
            background: #f0f9f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .testimonial-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .user-details h6 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .user-details small {
            color: #666;
        }
        
        .stars {
            color: #FFD700;
            font-size: 1.2rem;
        }
        
        .testimonial-body {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .testimonial-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        
        .testimonial-meta {
            color: #888;
            font-size: 0.9rem;
        }
        
        .qa-section {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #e0e0e0;
        }
        
        .qa-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e0e0e0;
        }
        
        .qa-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .qa-question {
            font-weight: 600;
            color: #2E7D32;
            margin-bottom: 5px;
        }
        
        .qa-answer {
            color: #555;
        }
        
        .rating-badge {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #333;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Filter section */
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 8px 20px;
            border-radius: 20px;
            background: white;
            border: 2px solid #e0e0e0;
            color: #666;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .filter-tab:hover,
        .filter-tab.active {
            background: #4CAF50;
            border-color: #4CAF50;
            color: white;
        }
        
        .filter-tab .badge {
            margin-left: 5px;
            font-size: 0.7rem;
        }
        
        /* Stats cards */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }
        
        .btn-export-excel {
            background: linear-gradient(135deg, #217346 0%, #185A37 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-export-excel:hover {
            background: linear-gradient(135deg, #185A37 0%, #0F4527 100%);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-export-csv {
            background: linear-gradient(135deg, #007BFF 0%, #0056B3 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-export-csv:hover {
            background: linear-gradient(135deg, #0056B3 0%, #004085 100%);
            color: white;
            transform: translateY(-2px);
        }
        
        .date-filter-info {
            background: #E8F5E9;
            border: 1px solid #C8E6C9;
            border-radius: 8px;
            padding: 10px 15px;
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid mt-4">
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-light-green mx-auto">
                        <i class="fas fa-comments text-primary-green"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['total']; ?></div>
                    <div class="stats-label">Total Testimoni</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-success mx-auto">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['approved']; ?></div>
                    <div class="stats-label">Disetujui</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-warning mx-auto">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['pending']; ?></div>
                    <div class="stats-label">Menunggu</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-warning mx-auto">
                        <i class="fas fa-star text-white"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['featured']; ?></div>
                    <div class="stats-label">Ulasan Bintang 5</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-info mx-auto">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></div>
                    <div class="stats-label">Rata-rata Rating</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-light-green mx-auto">
                        <i class="fas fa-percentage text-primary-green"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['total'] > 0 ? round(($stats['approved'] / $stats['total']) * 100) : 0; ?>%</div>
                    <div class="stats-label">Persetujuan</div>
                </div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="admin-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Testimoni</h5>
                    <p class="text-white mb-0 opacity-75">Filter berdasarkan status dan tanggal</p>
                </div>
                <div class="export-buttons">
                    <a href="?export=excel&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                       class="btn-export-excel">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </a>
                    <a href="?export=csv&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                       class="btn-export-csv">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Tabs -->
                <div class="mb-4">
                    <h6 class="mb-3">Filter Status</h6>
                    <div class="filter-tabs">
                        <a href="?status=all<?php echo !empty($start_date) ? '&start_date=' . $start_date : ''; echo !empty($end_date) ? '&end_date=' . $end_date : ''; ?>" 
                           class="filter-tab <?php echo $status == 'all' ? 'active' : ''; ?>">
                            Semua
                            <span class="badge bg-light-green"><?php echo $stats['total']; ?></span>
                        </a>
                        <a href="?status=approved<?php echo !empty($start_date) ? '&start_date=' . $start_date : ''; echo !empty($end_date) ? '&end_date=' . $end_date : ''; ?>" 
                           class="filter-tab <?php echo $status == 'approved' ? 'active' : ''; ?>">
                            Disetujui
                            <span class="badge bg-success"><?php echo $stats['approved']; ?></span>
                        </a>
                        <a href="?status=pending<?php echo !empty($start_date) ? '&start_date=' . $start_date : ''; echo !empty($end_date) ? '&end_date=' . $end_date : ''; ?>" 
                           class="filter-tab <?php echo $status == 'pending' ? 'active' : ''; ?>">
                            Menunggu
                            <span class="badge bg-warning"><?php echo $stats['pending']; ?></span>
                        </a>
                        <a href="?status=featured<?php echo !empty($start_date) ? '&start_date=' . $start_date : ''; echo !empty($end_date) ? '&end_date=' . $end_date : ''; ?>" 
                           class="filter-tab <?php echo $status == 'featured' ? 'active' : ''; ?>">
                            Bintang 5
                            <span class="badge bg-warning"><?php echo $stats['featured']; ?></span>
                        </a>
                    </div>
                </div>
                
                <!-- Date Filter -->
                <form method="GET" action="" class="filter-section">
                    <input type="hidden" name="status" value="<?php echo $status; ?>">
                    
                    <h6 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Filter Tanggal</h6>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="filter-group">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn-admin-primary">
                            <i class="fas fa-search me-2"></i>Terapkan Filter
                        </button>
                        <a href="?status=<?php echo $status; ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Reset Filter
                        </a>
                    </div>
                    
                    <?php if (!empty($start_date) || !empty($end_date)): ?>
                    <div class="date-filter-info">
                        <div>
                            <i class="fas fa-info-circle me-2 text-success"></i>
                            <strong>Filter aktif:</strong>
                            <?php if (!empty($start_date) && !empty($end_date)): ?>
                                <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?>
                            <?php elseif (!empty($start_date)): ?>
                                Dari tanggal <?php echo date('d M Y', strtotime($start_date)); ?>
                            <?php elseif (!empty($end_date)): ?>
                                Sampai tanggal <?php echo date('d M Y', strtotime($end_date)); ?>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted"><?php echo count($testimonials); ?> data ditemukan</small>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <?php if ($action == 'list'): ?>
        <!-- LIST TESTIMONIALS -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>
                        <?php 
                        $titles = [
                            'all' => 'Semua Testimoni',
                            'approved' => 'Testimoni Disetujui',
                            'pending' => 'Testimoni Menunggu',
                            'featured' => 'Testimoni Bintang 5'
                        ];
                        echo $titles[$status];
                        ?>
                    </h5>
                    <p class="text-white mb-0 opacity-75">Menampilkan <?php echo count($testimonials); ?> testimoni</p>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($testimonials)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-4x text-muted mb-4"></i>
                        <h5 class="text-muted">Belum ada testimoni</h5>
                        <p class="text-muted">Testimoni dari pelanggan akan muncul di sini</p>
                        <?php if (!empty($start_date) || !empty($end_date)): ?>
                        <a href="?status=<?php echo $status; ?>" class="btn-admin-primary mt-3">
                            <i class="fas fa-times me-2"></i>Reset Filter
                        </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($testimonials as $item): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <div class="user-info">
                                        <?php if ($item['image']): ?>
                                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="user-avatar">
                                        <?php else: ?>
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($item['name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="user-details">
                                            <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <small><?php echo htmlspecialchars($item['email']); ?></small>
                                        </div>
                                    </div>
                                    <div class="rating-badge">
                                        <i class="fas fa-star"></i>
                                        <?php echo $item['rating']; ?>/5
                                    </div>
                                </div>
                                
                                <div class="testimonial-body">
                                    <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                                </div>
                                
                                <?php if ($item['q1'] || $item['q2'] || $item['q3']): ?>
                                <div class="qa-section">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-question-circle me-2"></i>Detail Survey</h6>
                                    <?php if ($item['q1']): ?>
                                    <div class="qa-item">
                                        <div class="qa-question">Nama:</div>
                                        <div class="qa-answer"><?php echo htmlspecialchars($item['q1']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['q3']): ?>
                                    <div class="qa-item">
                                        <div class="qa-question">Tanggal Keberangkatan:</div>
                                        <div class="qa-answer">
                                            <?php echo $item['departure_date'] ? date('d M Y', strtotime($item['departure_date'])) : htmlspecialchars($item['q3']); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['q22']): ?>
                                    <div class="qa-item">
                                        <div class="qa-question">Kesan:</div>
                                        <div class="qa-answer"><?php echo htmlspecialchars($item['q22']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="testimonial-footer">
                                    <div class="testimonial-meta">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                        
                                        <?php if ($item['departure_date']): ?>
                                        <span class="ms-3">
                                            <i class="fas fa-plane me-1"></i>
                                            <?php echo date('M Y', strtotime($item['departure_date'])); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <?php if (!$item['is_approved']): ?>
                                        <a href="?action=approve&id=<?php echo $item['id']; ?>&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                                           class="btn btn-success btn-sm" 
                                           onclick="return confirm('Setujui testimoni ini?')">
                                            <i class="fas fa-check me-1"></i>Setujui
                                        </a>
                                        <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Disetujui
                                        </span>
                                        <?php endif; ?>
                                        
                                        <a href="?action=view&id=<?php echo $item['id']; ?>&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </a>
                                        
                                        <button type="button" 
                                                onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>', '<?php echo $status; ?>', '<?php echo $start_date; ?>', '<?php echo $end_date; ?>')"
                                                class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php elseif ($action == 'view' || $action == 'edit'): ?>
        <!-- VIEW/EDIT TESTIMONIAL -->
        <div class="row">
            <div class="col-lg-8">
                <div class="admin-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-<?php echo $action == 'view' ? 'eye' : 'edit'; ?> me-2"></i>
                                <?php echo $action == 'view' ? 'Detail Testimoni' : 'Edit Testimoni'; ?>: <?php echo htmlspecialchars($testimonial['name']); ?>
                            </h5>
                        </div>
                        <div>
                            <?php if ($action == 'view'): ?>
                            <a href="?action=edit&id=<?php echo $id; ?>&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn-admin-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($action == 'edit'): ?>
                        <form method="POST" action="?action=update&id=<?php echo $id; ?>&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                        <?php endif; ?>
                        
                        <!-- Basic Info -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama *</label>
                                <?php if ($action == 'view'): ?>
                                    <div class="form-control bg-light"><?php echo htmlspecialchars($testimonial['name']); ?></div>
                                <?php else: ?>
                                    <input type="text" class="form-control" name="name" 
                                           value="<?php echo htmlspecialchars($testimonial['name']); ?>" required>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <?php if ($action == 'view'): ?>
                                    <div class="form-control bg-light"><?php echo htmlspecialchars($testimonial['email']); ?></div>
                                <?php else: ?>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($testimonial['email']); ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Rating -->
                        <div class="mb-4">
                            <label class="form-label">Rating</label>
                            <?php if ($action == 'view'): ?>
                                <div class="d-flex align-items-center">
                                    <div class="stars me-3">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-badge"><?php echo $testimonial['rating']; ?>/5</span>
                                </div>
                            <?php else: ?>
                                <div class="star-rating">
                                    <input type="hidden" name="rating" id="rating-value" value="<?php echo $testimonial['rating']; ?>">
                                    <div class="stars" id="star-selector">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : '-o'; ?>" 
                                               data-value="<?php echo $i; ?>"
                                               style="cursor: pointer; font-size: 1.5rem; color: #FFD700; margin-right: 5px;"></i>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label">Status</label>
                            <?php if ($action == 'view'): ?>
                                <div class="form-control bg-light">
                                    <?php if ($testimonial['is_approved']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Menunggu</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" 
                                           value="1" <?php echo $testimonial['is_approved'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_approved">
                                        Setujui testimoni ini
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Testimonial Message -->
                        <div class="mb-4">
                            <label class="form-label">Testimoni *</label>
                            <?php if ($action == 'view'): ?>
                                <div class="form-control bg-light" style="min-height: 100px;">
                                    <?php echo nl2br(htmlspecialchars($testimonial['message'])); ?>
                                </div>
                            <?php else: ?>
                                <textarea class="form-control" name="message" rows="5" required><?php echo htmlspecialchars($testimonial['message']); ?></textarea>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Survey Questions -->
                        <?php if ($testimonial['q1']): ?>
                        <div class="admin-card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Data Survey</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php for ($i = 1; $i <= 23; $i++): 
                                        $question = $testimonial['q' . $i];
                                        if (!empty($question)):
                                    ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Q<?php echo $i; ?>:</label>
                                        <div class="form-control bg-light">
                                            <?php echo htmlspecialchars($question); ?>
                                        </div>
                                    </div>
                                    <?php endif; endfor; ?>
                                    
                                    <?php if ($testimonial['departure_date']): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Keberangkatan:</label>
                                        <div class="form-control bg-light">
                                            <?php echo date('d M Y', strtotime($testimonial['departure_date'])); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Meta Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Dibuat Pada:</label>
                                <div class="form-control bg-light">
                                    <?php echo date('d M Y H:i', strtotime($testimonial['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($action == 'edit'): ?>
                        <div class="d-flex justify-content-between">
                            <a href="?action=view&id=<?php echo $id; ?>&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn-admin-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                        </form>
                        <?php else: ?>
                        <div class="d-flex justify-content-between">
                            <a href="testimonial.php?status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-secondary">Kembali</a>
                            <div>
                                <?php if (!$testimonial['is_approved']): ?>
                                <a href="?action=approve&id=<?php echo $id; ?>&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                                   class="btn btn-success" 
                                   onclick="return confirm('Setujui testimoni ini?')">
                                    <i class="fas fa-check me-2"></i>Setujui
                                </a>
                                <?php endif; ?>
                                
                                <button type="button" 
                                        onclick="confirmDelete(<?php echo $id; ?>, '<?php echo addslashes($testimonial['name']); ?>', '<?php echo $status; ?>', '<?php echo $start_date; ?>', '<?php echo $end_date; ?>')"
                                        class="btn btn-danger">
                                    <i class="fas fa-trash me-2"></i>Hapus
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="admin-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (!$testimonial['is_approved']): ?>
                            <a href="?action=approve&id=<?php echo $id; ?>&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn btn-success" 
                               onclick="return confirm('Setujui testimoni ini?')">
                                <i class="fas fa-check-circle me-2"></i>Setujui Testimoni
                            </a>
                            <?php else: ?>
                            <a href="?action=edit&id=<?php echo $id; ?>&status=<?php echo $status; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn-admin-primary">
                                <i class="fas fa-edit me-2"></i>Edit Testimoni
                            </a>
                            <?php endif; ?>
                            
                            <a href="mailto:<?php echo htmlspecialchars($testimonial['email']); ?>" 
                               class="btn btn-outline-info">
                                <i class="fas fa-envelope me-2"></i>Kirim Email
                            </a>
                            
                            <button type="button" 
                                    onclick="confirmDelete(<?php echo $id; ?>, '<?php echo addslashes($testimonial['name']); ?>', '<?php echo $status; ?>', '<?php echo $start_date; ?>', '<?php echo $end_date; ?>')"
                                    class="btn btn-outline-danger">
                                <i class="fas fa-trash me-2"></i>Hapus Testimoni
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Rating</span>
                                <span><?php echo $testimonial['rating']; ?>/5</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" 
                                     style="width: <?php echo ($testimonial['rating'] / 5) * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Status</span>
                                <span>
                                    <?php if ($testimonial['is_approved']): ?>
                                        <span class="badge bg-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Menunggu</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Dibuat</span>
                                <span><?php echo date('d M Y', strtotime($testimonial['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($testimonial['departure_date']): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Keberangkatan</span>
                                <span><?php echo date('M Y', strtotime($testimonial['departure_date'])); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Confirm delete function with filters
        function confirmDelete(testimonialId, name, status, startDate, endDate) {
            Swal.fire({
                title: 'Hapus Testimoni?',
                html: `Apakah Anda yakin ingin menghapus testimoni dari <strong>${name}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = `?action=delete&id=${testimonialId}`;
                    if (status) url += `&status=${status}`;
                    if (startDate) url += `&start_date=${startDate}`;
                    if (endDate) url += `&end_date=${endDate}`;
                    window.location.href = url;
                }
            });
        }
        
        // Star rating selector for edit form
        document.addEventListener('DOMContentLoaded', function() {
            const starSelector = document.getElementById('star-selector');
            const ratingValue = document.getElementById('rating-value');
            
            if (starSelector && ratingValue) {
                const stars = starSelector.querySelectorAll('.fa-star');
                
                stars.forEach(star => {
                    star.addEventListener('mouseover', function() {
                        const value = this.getAttribute('data-value');
                        updateStars(value);
                    });
                    
                    star.addEventListener('click', function() {
                        const value = this.getAttribute('data-value');
                        ratingValue.value = value;
                    });
                });
                
                starSelector.addEventListener('mouseleave', function() {
                    const currentValue = ratingValue.value;
                    updateStars(currentValue);
                });
                
                function updateStars(value) {
                    stars.forEach(star => {
                        const starValue = star.getAttribute('data-value');
                        if (starValue <= value) {
                            star.classList.remove('fa-star-o');
                            star.classList.add('fa-star');
                        } else {
                            star.classList.remove('fa-star');
                            star.classList.add('fa-star-o');
                        }
                    });
                }
            }
            
            // Set end date min based on start date
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');
            
            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', function() {
                    endDateInput.min = this.value;
                    if (endDateInput.value && endDateInput.value < this.value) {
                        endDateInput.value = this.value;
                    }
                });
            }
            
            // Export confirmation
            document.querySelectorAll('a[href*="export="]').forEach(link => {
                link.addEventListener('click', function(e) {
                    const exportType = this.href.includes('export=excel') ? 'Excel' : 'CSV';
                    const count = <?php echo count($testimonials); ?>;
                    
                    if (count > 100) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Export Data?',
                            html: `Anda akan mengekspor <strong>${count}</strong> data testimoni ke format ${exportType}.<br><small>Proses ini mungkin memerlukan waktu beberapa saat.</small>`,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonColor: '#4CAF50',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, Export!',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = this.href;
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>