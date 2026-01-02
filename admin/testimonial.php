<?php
require_once 'includes/auth.php';

// Hanya admin yang bisa akses (master_admin dan admin)
if ($_SESSION['admin_role'] !== 'master_admin' && $_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Default tab and action
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'testimonials';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Sorting parameters
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Filter parameters
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'none'; // none, range, month
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$filter_month = isset($_GET['filter_month']) ? (int)$_GET['filter_month'] : date('n');
$filter_year = isset($_GET['filter_year']) ? (int)$_GET['filter_year'] : date('Y');

// Validate sort parameters
$allowed_sorts = ['created_at', 'departure_date', 'name', 'rating'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'created_at';
}
if (!in_array($sort_order, ['asc', 'desc'])) {
    $sort_order = 'desc';
}

// Validate filter month and year
if ($filter_month < 1 || $filter_month > 12) {
    $filter_month = date('n');
}
if ($filter_year < 2000 || $filter_year > 2100) {
    $filter_year = date('Y');
}

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'approve':
            $process = processApproveTestimonial($id);
            if ($process['success']) {
                $_SESSION['success_message'] = $process['message'];
                header('Location: testimonial.php');
                exit;
            } else {
                $error = $process['message'];
            }
            break;
            
        case 'update':
            $process = processUpdateTestimonial($id);
            if ($process['success']) {
                $_SESSION['success_message'] = $process['message'];
                header('Location: testimonial.php');
                exit;
            } else {
                $error = $process['message'];
            }
            break;
    }
}

// Process GET actions
if ($action == 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $process = processDeleteTestimonial($id);
    if ($process['success']) {
        $_SESSION['success_message'] = $process['message'];
        header('Location: testimonial.php');
        exit;
    } else {
        $error = $process['message'];
    }
}

// Process export action
if (isset($_GET['export']) && ($_GET['export'] == 'csv')) {
    exportTestimonials($_GET['export']);
    exit;
}

// Get message from session
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Get testimonial data for edit
$testimonial = null;
if ($id > 0 && $action == 'edit') {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $testimonial = $stmt->fetch();
    
    if (!$testimonial) {
        header('Location: testimonial.php');
        exit;
    }
}

// Build query with filters
$query = "SELECT * FROM testimonials WHERE 1=1";
$params = [];

// Apply filters
if ($filter_type === 'range' && !empty($date_from) && !empty($date_to)) {
    $query .= " AND DATE(created_at) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
} elseif ($filter_type === 'month') {
    $query .= " AND YEAR(created_at) = ? AND MONTH(created_at) = ?";
    $params[] = $filter_year;
    $params[] = $filter_month;
}

// Add sorting
$order_by = $sort_by . ' ' . strtoupper($sort_order);
$query .= " ORDER BY $order_by";

// Get all testimonials for list
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$testimonials = $stmt->fetchAll();

// Get count by filter type for stats
$count_all = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();

// Get monthly stats for dropdown
$monthly_stats = $pdo->query("
    SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count 
    FROM testimonials 
    GROUP BY YEAR(created_at), MONTH(created_at) 
    ORDER BY year DESC, month DESC
")->fetchAll();

// Function: Approve Testimonial
function processApproveTestimonial($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE testimonials SET is_approved = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    // Log activity
    $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'APPROVE_TESTIMONIAL', ?)");
    $log_stmt->execute([$_SESSION['admin_id'], "Menyetujui testimonial ID: $id"]);
    
    return ['success' => true, 'message' => 'Testimonial telah disetujui'];
}

// Function: Update Testimonial
function processUpdateTestimonial($id) {
    global $pdo;
    
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($message)) {
        return ['success' => false, 'message' => 'Nama dan pesan harus diisi'];
    }
    
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Rating harus antara 1-5'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update testimonial
        $stmt = $pdo->prepare("UPDATE testimonials SET name = ?, message = ?, rating = ?, is_approved = ? WHERE id = ?");
        $stmt->execute([$name, $message, $rating, $is_approved, $id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_TESTIMONIAL', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Memperbarui testimonial: $name"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Testimonial berhasil diperbarui'];
        
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
        return ['success' => false, 'message' => 'Testimonial tidak ditemukan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete testimonial
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete image file if exists
        if ($testimonial['image'] && file_exists(dirname(dirname(__FILE__)) . '/' . $testimonial['image'])) {
            @unlink(dirname(dirname(__FILE__)) . '/' . $testimonial['image']);
        }
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'DELETE_TESTIMONIAL', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menghapus testimonial: {$testimonial['name']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Testimonial berhasil dihapus'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Export Testimonials
function exportTestimonials($format) {
    global $pdo;
    
    // Rebuild query with current filters for export
    $query = "SELECT * FROM testimonials WHERE 1=1";
    $params = [];
    
    $filter_type = $_GET['filter_type'] ?? 'none';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $filter_month = $_GET['filter_month'] ?? date('n');
    $filter_year = $_GET['filter_year'] ?? date('Y');
    
    if ($filter_type === 'range' && !empty($date_from) && !empty($date_to)) {
        $query .= " AND DATE(created_at) BETWEEN ? AND ?";
        $params[] = $date_from;
        $params[] = $date_to;
    } elseif ($filter_type === 'month') {
        $query .= " AND YEAR(created_at) = ? AND MONTH(created_at) = ?";
        $params[] = $filter_year;
        $params[] = $filter_month;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $testimonials = $stmt->fetchAll();
    
    if ($format == 'csv') {
        // Export to CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=testimonials_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // CSV headers
        $headers = [
            'ID', 'Nama', 'Email', 'Pesan', 'Rating', 'Foto',
            'Status Disetujui', 'Tanggal Dibuat', 'Tanggal Keberangkatan',
            'Q1 (Nama)', 'Q2 (Telepon)', 'Q3 (Berangkat Dengan)', 
            'Q4 (Tahu dari)', 'Q5 (Alasan Memilih)', 'Q6 (Kepuasan Admin)',
            'Q7 (Komentar Admin)', 'Q8 (Kepuasan Manasik)', 'Q9 (Komentar Manasik)',
            'Q10 (Kepuasan Tour)', 'Q11 (Komentar Tour)', 'Q12 (Kepuasan Makanan)',
            'Q13 (Komentar Makanan)', 'Q14 (Kepuasan Tour Leader)', 'Q15 (Komentar Tour Leader)',
            'Q16 (Kepuasan Muthawif)', 'Q17 (Komentar Muthawif)', 'Q18 (Kepuasan Itinerary)',
            'Q19 (Komentar Itinerary)', 'Q20 (Rekomendasi)', 'Q21 (Alasan Rekomendasi)',
            'Q22 (Saran/Kritik)', 'Q23 (Kesan/Pesan)'
        ];
        
        fputcsv($output, $headers);
        
        foreach ($testimonials as $item) {
            $row = [
                $item['id'],
                $item['name'],
                $item['email'],
                $item['message'],
                $item['rating'],
                $item['image'],
                $item['is_approved'] ? 'Disetujui' : 'Belum Disetujui',
                date('d/m/Y H:i', strtotime($item['created_at'])),
                $item['departure_date'] ? date('d/m/Y', strtotime($item['departure_date'])) : '',
                $item['q1'] ?? '',
                $item['q2'] ?? '',
                $item['q3'] ?? '',
                $item['q4'] ?? '',
                $item['q5'] ?? '',
                $item['q6'] ?? '',
                $item['q7'] ?? '',
                $item['q8'] ?? '',
                $item['q9'] ?? '',
                $item['q10'] ?? '',
                $item['q11'] ?? '',
                $item['q12'] ?? '',
                $item['q13'] ?? '',
                $item['q14'] ?? '',
                $item['q15'] ?? '',
                $item['q16'] ?? '',
                $item['q17'] ?? '',
                $item['q18'] ?? '',
                $item['q19'] ?? '',
                $item['q20'] ?? '',
                $item['q21'] ?? '',
                $item['q22'] ?? '',
                $item['q23'] ?? ''
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
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
        .sort-arrow {
            margin-left: 5px;
            font-size: 0.8em;
        }
        
        .sortable {
            cursor: pointer;
        }
        
        .sortable:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn-export-csv {
            background: linear-gradient(135deg, #2196F3 0%, #0D47A1 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-export-csv:hover {
            background: linear-gradient(135deg, #0D47A1 0%, #2196F3 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
        }
        
        .sort-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }
        
        .filter-section {
            flex: 1;
        }
        
        .sort-select, .filter-select, .filter-input {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            font-weight: 500;
            color: #333;
            min-width: 200px;
        }
        
        .filter-input {
            min-width: 150px;
        }
        
        .sort-select:focus, .filter-select:focus, .filter-input:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        .btn-apply-sort, .btn-apply-filter {
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-apply-sort:hover, .btn-apply-filter:hover {
            background: #2E7D32;
            transform: translateY(-2px);
        }
        
        .btn-reset-filter {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-reset-filter:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .filter-type-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .filter-type-btn {
            padding: 8px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            color: #666;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-type-btn.active {
            border-color: #4CAF50;
            background: #E8F5E9;
            color: #2E7D32;
        }
        
        .filter-type-btn:hover:not(.active) {
            border-color: #bdbdbd;
            background: #f8f9fa;
        }
        
        .filter-content {
            display: none;
            animation: fadeIn 0.3s;
        }
        
        .filter-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .date-range-inputs {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .month-year-inputs {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .stats-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .stat-badge {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            background: #E8F5E9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2E7D32;
            font-size: 1.2rem;
        }
        
        .stat-info h6 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        
        .stat-info small {
            color: #666;
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .testimonial-item {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background: white;
        }
        
        .testimonial-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .testimonial-header {
            padding: 15px;
            background: linear-gradient(135deg, #f0f9f0 0%, #e8f5e9 100%);
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .testimonial-body {
            padding: 15px;
        }
        
        .rating-stars {
            color: #FFD700;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .testimonial-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.85rem;
            color: #666;
        }
        
        .qa-section {
            margin-top: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .qa-item {
            margin-bottom: 8px;
        }
        
        .qa-label {
            font-weight: 600;
            color: #2E7D32;
        }
        
        .qa-value {
            color: #333;
        }
        
        .full-view-btn {
            background: transparent;
            color: #4CAF50;
            border: none;
            padding: 5px 10px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: underline;
        }
        
        .full-view-btn:hover {
            color: #2E7D32;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #e0e0e0;
        }
        
        .no-results i {
            font-size: 3rem;
            color: #bdbdbd;
            margin-bottom: 15px;
        }
        
        .monthly-stats-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px;
            background: white;
        }
        
        .month-stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .month-stat-item:last-child {
            border-bottom: none;
        }
        
        .month-stat-item:hover {
            background: #f8f9fa;
        }
        
        .month-name {
            font-weight: 500;
        }
        
        .month-count {
            background: #E8F5E9;
            color: #2E7D32;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
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
        
        <?php if ($action == 'list'): ?>
        <!-- LIST TESTIMONIALS -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Daftar Testimoni Jamaah</h5>
                    <p class="text-white mb-0 opacity-75">Total <?php echo count($testimonials); ?> testimoni ditemukan</p>
                </div>
                <div class="export-buttons">
                    <a href="?export=csv<?php 
                        echo $filter_type !== 'none' ? '&filter_type=' . $filter_type : '';
                        echo $date_from ? '&date_from=' . $date_from : '';
                        echo $date_to ? '&date_to=' . $date_to : '';
                        echo ($filter_type === 'month') ? '&filter_month=' . $filter_month . '&filter_year=' . $filter_year : '';
                    ?>" class="btn-export-csv">
                        <i class="fas fa-file-csv me-1"></i> Export CSV
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Stats Badges -->
                <div class="stats-badges">
                    <div class="stat-badge">
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-info">
                            <h6><?php echo $count_all; ?></h6>
                            <small>Total Testimoni</small>
                        </div>
                    </div>
                    
                    <?php if ($filter_type === 'range' && $date_from && $date_to): ?>
                    <div class="stat-badge">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h6><?php echo count($testimonials); ?></h6>
                            <small>Filter: <?php echo date('d M Y', strtotime($date_from)); ?> - <?php echo date('d M Y', strtotime($date_to)); ?></small>
                        </div>
                    </div>
                    <?php elseif ($filter_type === 'month'): ?>
                    <div class="stat-badge">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h6><?php echo count($testimonials); ?></h6>
                            <small>Filter: <?php echo date('F Y', mktime(0, 0, 0, $filter_month, 1, $filter_year)); ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    $approved_count = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_approved = 1")->fetchColumn();
                    $pending_count = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_approved = 0")->fetchColumn();
                    ?>
                    <div class="stat-badge">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h6><?php echo $approved_count; ?></h6>
                            <small>Disetujui</small>
                        </div>
                    </div>
                    
                    <div class="stat-badge">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h6><?php echo $pending_count; ?></h6>
                            <small>Menunggu</small>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="filter-section">
                        <label class="form-label mb-2 d-block">Filter Berdasarkan Tanggal:</label>
                        
                        <div class="filter-type-buttons">
                            <button type="button" class="filter-type-btn <?php echo $filter_type === 'none' ? 'active' : ''; ?>" data-type="none">
                                <i class="fas fa-times me-1"></i> Tanpa Filter
                            </button>
                            <button type="button" class="filter-type-btn <?php echo $filter_type === 'range' ? 'active' : ''; ?>" data-type="range">
                                <i class="fas fa-calendar-range me-1"></i> Rentang Tanggal
                            </button>
                            <button type="button" class="filter-type-btn <?php echo $filter_type === 'month' ? 'active' : ''; ?>" data-type="month">
                                <i class="fas fa-calendar-month me-1"></i> Bulan & Tahun
                            </button>
                        </div>
                        
                        <!-- Range Date Filter -->
                        <div id="filter-range" class="filter-content <?php echo $filter_type === 'range' ? 'active' : ''; ?>">
                            <form method="GET" action="" class="d-flex gap-2 align-items-end">
                                <input type="hidden" name="filter_type" value="range">
                                <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
                                <input type="hidden" name="order" value="<?php echo $sort_order; ?>">
                                
                                <div>
                                    <label class="form-label mb-1">Dari Tanggal:</label>
                                    <input type="date" name="date_from" class="filter-input" value="<?php echo $date_from; ?>">
                                </div>
                                
                                <div>
                                    <label class="form-label mb-1">Sampai Tanggal:</label>
                                    <input type="date" name="date_to" class="filter-input" value="<?php echo $date_to; ?>">
                                </div>
                                
                                <button type="submit" class="btn-apply-filter">
                                    <i class="fas fa-filter me-1"></i> Terapkan Filter
                                </button>
                            </form>
                        </div>
                        
                        <!-- Month-Year Filter -->
                        <div id="filter-month" class="filter-content <?php echo $filter_type === 'month' ? 'active' : ''; ?>">
                            <form method="GET" action="" class="d-flex gap-2 align-items-end">
                                <input type="hidden" name="filter_type" value="month">
                                <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
                                <input type="hidden" name="order" value="<?php echo $sort_order; ?>">
                                
                                <div>
                                    <label class="form-label mb-1">Bulan:</label>
                                    <select name="filter_month" class="filter-select">
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $filter_month == $i ? 'selected' : ''; ?>>
                                                <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="form-label mb-1">Tahun:</label>
                                    <select name="filter_year" class="filter-select">
                                        <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $filter_year == $i ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn-apply-filter">
                                    <i class="fas fa-filter me-1"></i> Terapkan Filter
                                </button>
                            </form>
                        </div>
                        
                        <!-- No Filter -->
                        <div id="filter-none" class="filter-content <?php echo $filter_type === 'none' ? 'active' : ''; ?>">
                            <p class="text-muted mb-0">Menampilkan semua testimoni tanpa filter tanggal.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Sorting Controls -->
                <div class="sort-controls">
                    <div>
                        <label class="form-label mb-1">Urutkan Berdasarkan:</label>
                        <form method="GET" action="" class="d-flex gap-2 align-items-center">
                            <input type="hidden" name="filter_type" value="<?php echo $filter_type; ?>">
                            <input type="hidden" name="date_from" value="<?php echo $date_from; ?>">
                            <input type="hidden" name="date_to" value="<?php echo $date_to; ?>">
                            <input type="hidden" name="filter_month" value="<?php echo $filter_month; ?>">
                            <input type="hidden" name="filter_year" value="<?php echo $filter_year; ?>">
                            
                            <select name="sort" class="sort-select">
                                <option value="created_at" <?php echo $sort_by == 'created_at' ? 'selected' : ''; ?>>Tanggal Dibuat</option>
                                <option value="departure_date" <?php echo $sort_by == 'departure_date' ? 'selected' : ''; ?>>Tanggal Keberangkatan</option>
                                <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Nama Jamaah</option>
                                <option value="rating" <?php echo $sort_by == 'rating' ? 'selected' : ''; ?>>Rating</option>
                            </select>
                            
                            <select name="order" class="sort-select">
                                <option value="desc" <?php echo $sort_order == 'desc' ? 'selected' : ''; ?>>Terbaru ke Terlama</option>
                                <option value="asc" <?php echo $sort_order == 'asc' ? 'selected' : ''; ?>>Terlama ke Terbaru</option>
                            </select>
                            
                            <button type="submit" class="btn-apply-sort">
                                <i class="fas fa-sort me-1"></i> Terapkan
                            </button>
                            
                            <a href="testimonial.php" class="btn-reset-filter">
                                <i class="fas fa-redo me-1"></i> Reset Semua
                            </a>
                        </form>
                    </div>
                </div>
                
                <?php if (empty($testimonials)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h5 class="text-muted">Tidak ada testimoni ditemukan</h5>
                        <p class="text-muted mb-3">
                            <?php if ($filter_type !== 'none'): ?>
                                Tidak ada testimoni yang sesuai dengan filter yang diterapkan.
                            <?php else: ?>
                                Belum ada testimoni di database.
                            <?php endif; ?>
                        </p>
                        <a href="testimonial.php" class="btn-admin-primary">
                            <i class="fas fa-redo me-2"></i>Reset Filter
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Monthly Stats Sidebar -->
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="testimonial-grid">
                                <?php foreach ($testimonials as $item): 
                                    $stars = str_repeat('★', $item['rating']) . str_repeat('☆', 5 - $item['rating']);
                                ?>
                                <div class="testimonial-item">
                                    <div class="testimonial-header">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <div class="rating-stars"><?php echo $stars; ?></div>
                                        </div>
                                        <span class="status-badge <?php echo $item['is_approved'] ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fas fa-<?php echo $item['is_approved'] ? 'check-circle' : 'clock'; ?> me-1"></i>
                                            <?php echo $item['is_approved'] ? 'Disetujui' : 'Menunggu'; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="testimonial-body">
                                        <?php if ($item['image']): ?>
                                        <div class="text-center mb-3">
                                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="Foto <?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 150px;">
                                        </div>
                                        <?php endif; ?>
                                        
                                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($item['message'])); ?></p>
                                        
                                        <!-- Q&A Section -->
                                        <div class="qa-section">
                                            <?php 
                                            $qa_pairs = [
                                                'q1' => 'Nama',
                                                'q2' => 'Telepon',
                                                'q3' => 'Berangkat Dengan',
                                                'q4' => 'Tahu dari',
                                                'q5' => 'Alasan Memilih',
                                                'q6' => 'Kepuasan Admin',
                                                'q7' => 'Komentar Admin',
                                                'q8' => 'Kepuasan Manasik',
                                                'q9' => 'Komentar Manasik',
                                                'q10' => 'Kepuasan Tour',
                                                'q11' => 'Komentar Tour',
                                                'q12' => 'Kepuasan Makanan',
                                                'q13' => 'Komentar Makanan',
                                                'q14' => 'Kepuasan Tour Leader',
                                                'q15' => 'Komentar Tour Leader',
                                                'q16' => 'Kepuasan Muthawif',
                                                'q17' => 'Komentar Muthawif',
                                                'q18' => 'Kepuasan Itinerary',
                                                'q19' => 'Komentar Itinerary',
                                                'q20' => 'Rekomendasi',
                                                'q21' => 'Alasan Rekomendasi',
                                                'q22' => 'Saran/Kritik',
                                                'q23' => 'Kesan/Pesan'
                                            ];
                                            
                                            foreach ($qa_pairs as $key => $label):
                                                if (!empty($item[$key])):
                                            ?>
                                            <div class="qa-item">
                                                <span class="qa-label"><?php echo $label; ?>:</span>
                                                <span class="qa-value"><?php echo htmlspecialchars($item[$key]); ?></span>
                                            </div>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        </div>
                                        
                                        <div class="testimonial-meta">
                                            <div>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                                <?php if ($item['departure_date']): ?>
                                                <br>
                                                <i class="fas fa-plane-departure me-1"></i>
                                                <?php echo date('d M Y', strtotime($item['departure_date'])); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php echo htmlspecialchars($item['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-light d-flex justify-content-between">
                                        <?php if (!$item['is_approved']): ?>
                                        <a href="?action=approve&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Setujui testimoni ini?')">
                                            <i class="fas fa-check me-1"></i> Setujui
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Disetujui
                                        </span>
                                        <?php endif; ?>
                                        
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $item['id']; ?>" 
                                               class="btn btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>')"
                                                    class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="col-lg-3">
                            <!-- Monthly Stats -->
                            <div class="admin-card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik per Bulan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="monthly-stats-list">
                                        <?php if (empty($monthly_stats)): ?>
                                            <p class="text-muted text-center mb-0">Belum ada data</p>
                                        <?php else: ?>
                                            <?php foreach ($monthly_stats as $stat): 
                                                $month_name = date('F Y', mktime(0, 0, 0, $stat['month'], 1, $stat['year']));
                                                $is_active = ($filter_type === 'month' && $filter_month == $stat['month'] && $filter_year == $stat['year']);
                                            ?>
                                            <a href="?filter_type=month&filter_month=<?php echo $stat['month']; ?>&filter_year=<?php echo $stat['year']; ?>" 
                                               class="month-stat-item <?php echo $is_active ? 'active' : ''; ?>"
                                               style="text-decoration: none; color: inherit;">
                                                <span class="month-name"><?php echo $month_name; ?></span>
                                                <span class="month-count"><?php echo $stat['count']; ?></span>
                                            </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="admin-card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="?filter_type=month&filter_month=<?php echo date('n'); ?>&filter_year=<?php echo date('Y'); ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-calendar-week me-1"></i> Bulan Ini
                                        </a>
                                        <a href="?filter_type=range&date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-t'); ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-calendar-alt me-1"></i> Bulan Berjalan
                                        </a>
                                        <a href="?filter_type=none" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-eye me-1"></i> Tampilkan Semua
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php elseif ($action == 'edit'): ?>
        <!-- EDIT TESTIMONIAL FORM -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Edit Testimoni: <?php echo htmlspecialchars($testimonial['name']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=update&id=<?php echo $id; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nama Jamaah *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($testimonial['name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="rating" class="form-label">Rating (1-5) *</label>
                                    <select class="form-control" id="rating" name="rating" required>
                                        <option value="5" <?php echo $testimonial['rating'] == 5 ? 'selected' : ''; ?>>★★★★★ (5)</option>
                                        <option value="4" <?php echo $testimonial['rating'] == 4 ? 'selected' : ''; ?>>★★★★☆ (4)</option>
                                        <option value="3" <?php echo $testimonial['rating'] == 3 ? 'selected' : ''; ?>>★★★☆☆ (3)</option>
                                        <option value="2" <?php echo $testimonial['rating'] == 2 ? 'selected' : ''; ?>>★★☆☆☆ (2)</option>
                                        <option value="1" <?php echo $testimonial['rating'] == 1 ? 'selected' : ''; ?>>★☆☆☆☆ (1)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Pesan Testimoni *</label>
                                <textarea class="form-control" id="message" name="message" 
                                          rows="4" required><?php echo htmlspecialchars($testimonial['message']); ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" 
                                           value="1" <?php echo $testimonial['is_approved'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_approved">
                                        Setujui untuk ditampilkan di website
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Display Q&A Data (Read-only) -->
                            <div class="admin-card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-list-check me-2"></i>Data Formulir Jamaah</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php 
                                        $qa_fields = [
                                            'q1' => 'Nama Lengkap',
                                            'q2' => 'Nomor Telepon',
                                            'q3' => 'Berangkat Dengan',
                                            'q4' => 'Mengetahui ALFARUQ TEAM Dari',
                                            'q5' => 'Alasan Memilih ALFARUQ TEAM',
                                            'q6' => 'Kepuasan Pelayanan Admin',
                                            'q7' => 'Komentar Pelayanan Admin',
                                            'q8' => 'Kepuasan Pelaksanaan Manasik',
                                            'q9' => 'Komentar Pelaksanaan Manasik',
                                            'q10' => 'Kepuasan Pelaksanaan Tour',
                                            'q11' => 'Komentar Pelaksanaan Tour',
                                            'q12' => 'Kepuasan Kualitas Makanan',
                                            'q13' => 'Komentar Kualitas Makanan',
                                            'q14' => 'Kepuasan Pelayanan Tour Leader',
                                            'q15' => 'Komentar Pelayanan Tour Leader',
                                            'q16' => 'Kepuasan Pelayanan Muthawif',
                                            'q17' => 'Komentar Pelayanan Muthawif',
                                            'q18' => 'Kepuasan Pelaksanaan Itinerary',
                                            'q19' => 'Komentar Pelaksanaan Itinerary',
                                            'q20' => 'Apakah Anda Merekomendasikan?',
                                            'q21' => 'Alasan Merekomendasikan',
                                            'q22' => 'Saran/Kritik untuk ALFARUQ TEAM',
                                            'q23' => 'Kesan & Pesan untuk Perjalanan Ini'
                                        ];
                                        
                                        foreach ($qa_fields as $key => $label):
                                            if (!empty($testimonial[$key])):
                                        ?>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><?php echo $label; ?></label>
                                            <div class="form-control bg-light">
                                                <?php echo htmlspecialchars($testimonial[$key]); ?>
                                            </div>
                                        </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                        
                                        <?php if ($testimonial['departure_date']): ?>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tanggal Keberangkatan</label>
                                            <div class="form-control bg-light">
                                                <?php echo date('d M Y', strtotime($testimonial['departure_date'])); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($testimonial['email']): ?>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <div class="form-control bg-light">
                                                <?php echo htmlspecialchars($testimonial['email']); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="testimonial.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn-admin-primary">
                                    <i class="fas fa-save me-2"></i>Update Testimoni
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Confirm delete function
        function confirmDelete(testimonialId, testimonialName) {
            Swal.fire({
                title: 'Hapus Testimoni?',
                html: `Apakah Anda yakin ingin menghapus testimoni dari <strong>${testimonialName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?action=delete&id=' + testimonialId;
                }
            });
        }
        
        // Confirm approve function
        function confirmApprove(testimonialId, testimonialName) {
            Swal.fire({
                title: 'Setujui Testimoni?',
                html: `Apakah Anda yakin ingin menyetujui testimoni dari <strong>${testimonialName}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?action=approve&id=' + testimonialId;
                }
            });
        }
        
        // Filter type switching
        document.addEventListener('DOMContentLoaded', function() {
            const filterTypeBtns = document.querySelectorAll('.filter-type-btn');
            const filterContents = document.querySelectorAll('.filter-content');
            
            filterTypeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    
                    // Update active button
                    filterTypeBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding content
                    filterContents.forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    document.getElementById(`filter-${type}`).classList.add('active');
                });
            });
            
            // Set max date for date_to to today
            const dateFromInput = document.querySelector('input[name="date_from"]');
            const dateToInput = document.querySelector('input[name="date_to"]');
            
            if (dateFromInput) {
                dateFromInput.max = new Date().toISOString().split('T')[0];
                
                dateFromInput.addEventListener('change', function() {
                    if (dateToInput) {
                        dateToInput.min = this.value;
                        if (dateToInput.value && dateToInput.value < this.value) {
                            dateToInput.value = this.value;
                        }
                    }
                });
            }
            
            if (dateToInput) {
                dateToInput.max = new Date().toISOString().split('T')[0];
            }
            
            // Export confirmation
            const csvBtn = document.querySelector('.btn-export-csv');
            
            if (csvBtn) {
                csvBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    let filterText = 'Semua data testimoni';
                    const filterType = '<?php echo $filter_type; ?>';
                    
                    if (filterType === 'range') {
                        const dateFrom = '<?php echo $date_from; ?>';
                        const dateTo = '<?php echo $date_to; ?>';
                        if (dateFrom && dateTo) {
                            filterText = `Data dari ${dateFrom} sampai ${dateTo}`;
                        }
                    } else if (filterType === 'month') {
                        const month = '<?php echo date("F", mktime(0, 0, 0, $filter_month, 1)); ?>';
                        const year = '<?php echo $filter_year; ?>';
                        filterText = `Data bulan ${month} ${year}`;
                    }
                    
                    Swal.fire({
                        title: 'Export ke CSV?',
                        html: `${filterText} akan diexport dalam format CSV (.csv)`,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#2196F3',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Export',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = this.href;
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>