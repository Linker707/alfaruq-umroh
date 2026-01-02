<?php
require_once 'includes/auth.php';

// Hanya master_admin yang bisa akses (master_admin dan admin)
if ($_SESSION['admin_role'] !== 'master_admin' && $_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Default action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Define upload path - FIXED TO ABSOLUTE PATH
define('UPLOAD_DIR', dirname(dirname(__FILE__)) . '/assets/img/');
define('WEB_UPLOAD_DIR', 'assets/img/');

// Handle file upload
function handleImageUpload($old_image = null) {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        return $old_image;
    }
    
    $upload_dir = UPLOAD_DIR; // Use absolute path
    $web_upload_dir = WEB_UPLOAD_DIR; // Web path for database
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $file = $_FILES['image'];
    
    // Check error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Terjadi kesalahan saat upload gambar: ' . $file['error']);
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP');
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        throw new Exception('Ukuran file maksimal 5MB');
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'package_' . time() . '_' . uniqid() . '.' . $ext;
    $target_path = $upload_dir . $filename;
    
    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        throw new Exception('Gagal menyimpan file');
    }
    
    // Delete old image if exists
    if ($old_image) {
        $old_path = dirname(dirname(__FILE__)) . '/' . $old_image;
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
    }
    
    return $web_upload_dir . $filename; // Return web path
}

// Helper function to validate prices
function validatePrices($prices_data) {
    if (!isset($prices_data['type']) || !is_array($prices_data['type'])) {
        return ['success' => false, 'message' => 'Data harga tidak valid'];
    }
    
    $has_valid_price = false;
    $processed_types = [];
    
    foreach ($prices_data['type'] as $index => $type) {
        $price_value = isset($prices_data['price'][$index]) ? str_replace(['.', ','], '', $prices_data['price'][$index]) : '';
        
        // Check if this price row is filled
        if (!empty($price_value) && is_numeric($price_value) && $price_value > 0) {
            $has_valid_price = true;
            
            // Check for duplicate type
            if (in_array($type, $processed_types)) {
                $type_name = $type === '' ? 'Reguler' : ucfirst($type);
                return ['success' => false, 'message' => "Tipe harga '{$type_name}' duplikat"];
            }
            $processed_types[] = $type;
        }
    }
    
    if (!$has_valid_price) {
        return ['success' => false, 'message' => 'Minimal satu harga yang valid harus diisi'];
    }
    
    return ['success' => true];
}

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'create':
            $process = processCreatePackage();
            if ($process['success']) {
                $_SESSION['success_message'] = $process['message'];
                header('Location: package.php');
                exit;
            } else {
                $error = $process['message'];
            }
            break;
            
        case 'update':
            $process = processUpdatePackage($id);
            if ($process['success']) {
                $_SESSION['success_message'] = $process['message'];
                header('Location: package.php');
                exit;
            } else {
                $error = $process['message'];
            }
            break;
    }
}

// Get message from session
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Process GET actions (delete)
if ($action == 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $process = processDeletePackage($id);
    if ($process['success']) {
        $_SESSION['success_message'] = $process['message'];
        header('Location: package.php');
        exit;
    } else {
        $error = $process['message'];
    }
}

// Get package data for edit
$package = null;
$package_prices = [];
if ($id > 0 && $action == 'edit') {
    // Get package info
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$id]);
    $package = $stmt->fetch();
    
    if (!$package) {
        header('Location: package.php');
        exit;
    }
    
    // Get package prices
    $stmt = $pdo->prepare("SELECT * FROM package_prices WHERE package_id = ? ORDER BY 
                          CASE type 
                              WHEN '' THEN 0 
                              WHEN 'bronze' THEN 1
                              WHEN 'silver' THEN 2
                              WHEN 'gold' THEN 3
                              WHEN 'platinum' THEN 4
                              ELSE 5
                          END");
    $stmt->execute([$id]);
    $package_prices = $stmt->fetchAll();
}

// Get all packages for list
$stmt = $pdo->query("SELECT p.*, 
                    (SELECT COUNT(*) FROM package_prices WHERE package_id = p.id AND is_active = 1) as price_count,
                    (SELECT COUNT(*) FROM schedules WHERE package_name = p.name) as schedule_count
                    FROM packages p 
                    ORDER BY p.created_at DESC");
$packages = $stmt->fetchAll();

// Price types for price management
$price_types = [
    '' => 'Default / Reguler',
    'bronze' => 'Bronze',
    'silver' => 'Silver', 
    'gold' => 'Gold',
    'platinum' => 'Platinum'
];

// Function: Create Package
function processCreatePackage() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $facilities = trim($_POST['facilities'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($description)) {
        return ['success' => false, 'message' => 'Nama dan deskripsi paket harus diisi'];
    }
    
    if ($duration <= 0) {
        return ['success' => false, 'message' => 'Durasi harus lebih dari 0 hari'];
    }
    
    // Check duplicate name
    $stmt = $pdo->prepare("SELECT id FROM packages WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Nama paket sudah digunakan'];
    }
    
    // Validate prices
    if (!isset($_POST['prices']) || !is_array($_POST['prices'])) {
        return ['success' => false, 'message' => 'Data harga tidak ditemukan'];
    }
    
    $price_validation = validatePrices($_POST['prices']);
    if (!$price_validation['success']) {
        return $price_validation;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image = handleImageUpload();
        }
        
        // Insert package
        $stmt = $pdo->prepare("INSERT INTO packages (name, description, duration, facilities, image, is_active, is_popular) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $duration, $facilities, $image, $is_active, $is_popular]);
        $package_id = $pdo->lastInsertId();
        
        // Process prices
        if (isset($_POST['prices']['type']) && is_array($_POST['prices']['type'])) {
            foreach ($_POST['prices']['type'] as $index => $type) {
                $price_value = isset($_POST['prices']['price'][$index]) ? str_replace(['.', ','], '', $_POST['prices']['price'][$index]) : '';
                $price_active = isset($_POST['prices']['is_active'][$index]) ? 1 : 0;
                
                if (!empty($price_value) && is_numeric($price_value) && $price_value > 0) {
                    $stmt = $pdo->prepare("INSERT INTO package_prices (package_id, type, price, is_active) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$package_id, $type, $price_value, $price_active]);
                }
            }
        }
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'ADD_PACKAGE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menambahkan paket baru: $name"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Paket berhasil ditambahkan'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Update Package
function processUpdatePackage($id) {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $facilities = trim($_POST['facilities'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($description)) {
        return ['success' => false, 'message' => 'Nama dan deskripsi paket harus diisi'];
    }
    
    if ($duration <= 0) {
        return ['success' => false, 'message' => 'Durasi harus lebih dari 0 hari'];
    }
    
    // Check duplicate name (exclude current)
    $stmt = $pdo->prepare("SELECT id FROM packages WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Nama paket sudah digunakan'];
    }
    
    // Validate prices
    if (!isset($_POST['prices']) || !is_array($_POST['prices'])) {
        return ['success' => false, 'message' => 'Data harga tidak ditemukan'];
    }
    
    $price_validation = validatePrices($_POST['prices']);
    if (!$price_validation['success']) {
        return $price_validation;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get old package data for image deletion
        $stmt = $pdo->prepare("SELECT image FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        $old_package = $stmt->fetch();
        $old_image = $old_package['image'];
        
        // Handle image upload
        $image = $old_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image = handleImageUpload($old_image);
        } elseif (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            // Delete image if checkbox is checked
            if ($old_image && file_exists(dirname(dirname(__FILE__)) . '/' . $old_image)) {
                @unlink(dirname(dirname(__FILE__)) . '/' . $old_image);
            }
            $image = null;
        }
        
        // Update package
        $stmt = $pdo->prepare("UPDATE packages SET name = ?, description = ?, duration = ?, facilities = ?, image = ?, is_active = ?, is_popular = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$name, $description, $duration, $facilities, $image, $is_active, $is_popular, $id]);
        
        // Delete existing prices
        $stmt = $pdo->prepare("DELETE FROM package_prices WHERE package_id = ?");
        $stmt->execute([$id]);
        
        // Insert new prices
        if (isset($_POST['prices']['type']) && is_array($_POST['prices']['type'])) {
            foreach ($_POST['prices']['type'] as $index => $type) {
                $price_value = isset($_POST['prices']['price'][$index]) ? str_replace(['.', ','], '', $_POST['prices']['price'][$index]) : '';
                $price_active = isset($_POST['prices']['is_active'][$index]) ? 1 : 0;
                
                if (!empty($price_value) && is_numeric($price_value) && $price_value > 0) {
                    $stmt = $pdo->prepare("INSERT INTO package_prices (package_id, type, price, is_active) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$id, $type, $price_value, $price_active]);
                }
            }
        }
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_PACKAGE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Memperbarui paket: $name"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Paket berhasil diperbarui'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Delete Package
function processDeletePackage($id) {
    global $pdo;
    
    // Get package info for log
    $stmt = $pdo->prepare("SELECT name, image FROM packages WHERE id = ?");
    $stmt->execute([$id]);
    $package = $stmt->fetch();
    
    if (!$package) {
        return ['success' => false, 'message' => 'Paket tidak ditemukan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete associated prices first
        $stmt = $pdo->prepare("DELETE FROM package_prices WHERE package_id = ?");
        $stmt->execute([$id]);
        
        // Delete package
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete image file
        if ($package['image'] && file_exists(dirname(dirname(__FILE__)) . '/' . $package['image'])) {
            @unlink(dirname(dirname(__FILE__)) . '/' . $package['image']);
        }
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'DELETE_PACKAGE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menghapus paket: {$package['name']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Paket berhasil dihapus'];
        
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
    <title>Kelola Paket - Admin ALFARUQ TEAM</title>
    <style>
        .price-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
            transition: all 0.3s;
        }
        
        .price-item:hover {
            border-color: #4CAF50;
            background: #f0f9f0;
        }
        
        .price-type-badge {
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .price-type-badge.bg-brown {
            background: #8B4513 !important;
            color: white !important;
        }
        
        .price-type-badge.bg-light {
            background: #f8f9fa !important;
            color: #212529 !important;
            border: 1px solid #dee2e6;
        }
        
        .price-type-badge.bg-light-green {
            background: #E8F5E9 !important;
            color: #2E7D32 !important;
            border: 1px solid #C8E6C9;
        }
        
        .remove-price {
            color: #dc3545;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.3s;
        }
        
        .remove-price:hover {
            color: #c82333;
        }
        
        /* Fix for checkbox alignment */
        .price-item .form-check {
            margin-top: 30px;
        }
        
        /* Debug info */
        .debug-info {
            display: none;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
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
        <!-- LIST PACKAGES -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-box-open me-2"></i>Daftar Paket Umroh</h5>
                    <p class="text-white mb-0 opacity-75">Total <?php echo count($packages); ?> paket terdaftar</p>
                </div>
                <a href="?action=create" class="btn-admin-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Paket Baru
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($packages)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                        <h5 class="text-muted">Belum ada paket</h5>
                        <p class="text-muted mb-4">Mulai dengan menambahkan paket baru</p>
                        <a href="?action=create" class="btn-admin-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Paket Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($packages as $item): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="admin-card h-100">
                                <div class="card-body">
                                    <!-- Package Image -->
                                    <div class="text-center mb-3">
                                        <?php if ($item['image']): ?>
                                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 200px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="height: 200px;">
                                                <i class="fas fa-box-open fa-4x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Package Info -->
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-dark mb-1">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </h6>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-light-green text-dark me-2">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo $item['duration']; ?> hari
                                            </span>
                                            <?php if ($item['is_popular']): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-star me-1"></i>Populer
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-muted mb-2 small" style="min-height: 60px;">
                                            <?php echo substr(strip_tags($item['description']), 0, 100); ?>
                                            <?php if (strlen(strip_tags($item['description'])) > 100): ?>...<?php endif; ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Stats -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="text-center">
                                            <div class="fw-bold text-dark"><?php echo $item['price_count']; ?></div>
                                            <small class="text-muted">Harga</small>
                                        </div>
                                        <div class="text-center">
                                            <span class="status-badge <?php echo $item['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <i class="fas fa-<?php echo $item['is_active'] ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                                <?php echo $item['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=edit&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <a href="price.php?package_id=<?php echo $item['id']; ?>" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-tag me-1"></i>Kelola Harga
                                        </a>
                                        <button type="button" 
                                                onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>')"
                                                class="btn btn-outline-danger btn-sm">
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
        
        <?php elseif ($action == 'create' || $action == 'edit'): ?>
        <!-- CREATE/EDIT PACKAGE FORM -->
        <div class="row">
            <div class="col-lg-8">
                <div class="admin-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $action == 'create' ? 'plus' : 'edit'; ?> me-2"></i>
                            <?php echo $action == 'create' ? 'Tambah Paket Baru' : 'Edit Paket: ' . htmlspecialchars($package['name']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=<?php echo $action == 'create' ? 'create' : 'update&id=' . $id; ?>" enctype="multipart/form-data" id="package-form">
                            <!-- Basic Package Information -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nama Paket *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo $action == 'edit' ? htmlspecialchars($package['name']) : ''; ?>" 
                                           placeholder="Contoh: UMROH AWAL RAMADHAN" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="duration" class="form-label">Durasi (hari) *</label>
                                    <input type="number" class="form-control" id="duration" name="duration" 
                                           value="<?php echo $action == 'edit' ? $package['duration'] : '12'; ?>" 
                                           min="1" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi Paket *</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="4" placeholder="Deskripsi lengkap paket..." required><?php echo $action == 'edit' ? htmlspecialchars($package['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="facilities" class="form-label">Fasilitas</label>
                                <textarea class="form-control" id="facilities" name="facilities" 
                                          rows="5" placeholder="Masukkan fasilitas, setiap fasilitas dipisahkan dengan baris baru..."><?php echo $action == 'edit' ? htmlspecialchars($package['facilities']) : ''; ?></textarea>
                                <small class="text-muted">Gunakan format: setiap fasilitas dalam baris baru</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Paket</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" <?php echo ($action == 'edit' && $package['is_active']) || $action == 'create' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Aktif (tampilkan di website)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" 
                                               value="1" <?php echo ($action == 'edit' && $package['is_popular']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_popular">
                                            Tandai sebagai Paket Populer
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="image" class="form-label">Gambar Paket</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="text-muted">Format: JPG, PNG, GIF, WebP. Maks: 5MB</small>
                                    
                                    <?php if ($action == 'edit' && $package['image']): ?>
                                    <div class="mt-2">
                                        <img src="../<?php echo htmlspecialchars($package['image']); ?>" 
                                             alt="Current Image" 
                                             class="img-thumbnail" 
                                             style="max-height: 100px;">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="delete_image" name="delete_image" value="1">
                                            <label class="form-check-label text-danger" for="delete_image">
                                                Hapus gambar saat disimpan
                                            </label>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Package Prices Section -->
                            <div class="admin-card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Harga Paket</h5>
                                    <button type="button" class="btn-admin-primary btn-sm" onclick="addPriceRow()">
                                        <i class="fas fa-plus me-1"></i>Tambah Harga
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="prices-container">
                                        <?php if ($action == 'edit' && !empty($package_prices)): ?>
                                            <?php foreach ($package_prices as $index => $price): 
                                                $type_name = empty($price['type']) ? 'Reguler' : ucfirst($price['type']);
                                                $price_formatted = number_format($price['price'], 0, ',', '.');
                                            ?>
                                            <div class="price-item">
                                                <div class="row">
                                                    <div class="col-md-4 mb-2">
                                                        <label class="form-label">Tipe Harga</label>
                                                        <select class="form-control" name="prices[type][]" required>
                                                            <option value="">-- Pilih Tipe --</option>
                                                            <?php foreach ($price_types as $value => $label): ?>
                                                                <option value="<?php echo $value; ?>" <?php echo $price['type'] == $value ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($label); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Harga (Rp)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <input type="text" class="form-control price-input" 
                                                                   name="prices[price][]" 
                                                                   value="<?php echo $price_formatted; ?>"
                                                                   placeholder="Contoh: 29.500.000" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="prices[is_active][<?php echo $index; ?>]" 
                                                                   value="1" <?php echo $price['is_active'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label">
                                                                Aktif
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <span class="price-type-badge <?php echo getPriceTypeClass($price['type']); ?>">
                                                        <?php echo $type_name; ?>: Rp <?php echo $price_formatted; ?>
                                                    </span>
                                                    <button type="button" class="btn btn-danger btn-sm remove-price-btn">
                                                        <i class="fas fa-times"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <!-- Default price row for new packages -->
                                            <div class="price-item">
                                                <div class="row">
                                                    <div class="col-md-4 mb-2">
                                                        <label class="form-label">Tipe Harga</label>
                                                        <select class="form-control" name="prices[type][]" required>
                                                            <option value="">-- Pilih Tipe --</option>
                                                            <?php foreach ($price_types as $value => $label): ?>
                                                                <option value="<?php echo $value; ?>">
                                                                    <?php echo htmlspecialchars($label); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Harga (Rp)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <input type="text" class="form-control price-input" 
                                                                   name="prices[price][]" 
                                                                   placeholder="Contoh: 29.500.000" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="prices[is_active][]" value="1" checked>
                                                            <label class="form-check-label">
                                                                Aktif
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <span class="price-type-badge bg-light-green">
                                                        Preview
                                                    </span>
                                                    <button type="button" class="btn btn-danger btn-sm remove-price-btn">
                                                        <i class="fas fa-times"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="package.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn-admin-primary" id="submit-btn">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $action == 'create' ? 'Simpan Paket & Harga' : 'Update Paket & Harga'; ?>
                                </button>
                            </div>
                            
                            <!-- Debug info -->
                            <div class="debug-info" id="debug-info">
                                <strong>Debug Info:</strong>
                                <div id="debug-content"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Preview Card -->
                <div class="admin-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview Paket</h5>
                    </div>
                    <div class="card-body">
                        <div id="package-preview">
                            <p class="text-muted">Isi form di samping untuk melihat preview...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Tips Card -->
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Gunakan nama paket yang jelas dan menarik</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Deskripsi harus informatif dan lengkap</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Gambar dengan rasio 4:3 terlihat lebih baik</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Anda bisa menambahkan multiple harga untuk satu paket</small>
                            </li>
                        </ul>
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
        function confirmDelete(packageId, packageName) {
            Swal.fire({
                title: 'Hapus Paket?',
                html: `Apakah Anda yakin ingin menghapus paket <strong>${packageName}</strong>?<br><small class="text-danger">Semua harga terkait juga akan dihapus</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?action=delete&id=' + packageId;
                }
            });
        }
        
        // Helper function to get CSS class for price type
        function getPriceTypeClass(type) {
            switch(type) {
                case 'bronze': return 'bg-brown';
                case 'silver': return 'bg-secondary';
                case 'gold': return 'bg-warning text-dark';
                case 'platinum': return 'bg-light';
                default: return 'bg-light-green';
            }
        }
        
        // Price management functions
        function addPriceRow() {
            const container = document.getElementById('prices-container');
            const priceIndex = container.querySelectorAll('.price-item').length;
            
            const newPriceItem = document.createElement('div');
            newPriceItem.className = 'price-item';
            newPriceItem.innerHTML = `
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Tipe Harga</label>
                        <select class="form-control" name="prices[type][]" required>
                            <option value="">-- Pilih Tipe --</option>
                            <?php foreach ($price_types as $value => $label): ?>
                                <option value="<?php echo $value; ?>">
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Harga (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control price-input" 
                                   name="prices[price][]" 
                                   placeholder="Contoh: 29.500.000" required>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="prices[is_active][${priceIndex}]" value="1" checked>
                            <label class="form-check-label">
                                Aktif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="price-type-badge bg-light-green">
                        Preview
                    </span>
                    <button type="button" class="btn btn-danger btn-sm remove-price-btn">
                        <i class="fas fa-times"></i> Hapus
                    </button>
                </div>
            `;
            
            container.appendChild(newPriceItem);
            
            // Initialize price formatting and remove button
            initPriceFormatting();
            initRemoveButtons();
            attachTypeChangeListener(newPriceItem);
        }
        
        // Initialize price formatting
        function initPriceFormatting() {
            const priceInputs = document.querySelectorAll('.price-input');
            priceInputs.forEach(input => {
                if (!input.hasAttribute('data-formatted')) {
                    input.setAttribute('data-formatted', 'true');
                    input.addEventListener('input', function() {
                        formatPriceInput(this);
                        updatePriceBadge(this);
                    });
                }
            });
        }
        
        // Initialize remove buttons
        function initRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-price-btn');
            removeButtons.forEach(button => {
                if (!button.hasAttribute('data-initialized')) {
                    button.setAttribute('data-initialized', 'true');
                    button.addEventListener('click', function() {
                        const priceItem = this.closest('.price-item');
                        if (priceItem && confirm('Hapus harga ini?')) {
                            priceItem.remove();
                        }
                    });
                }
            });
        }
        
        // Attach type change listener to a price item
        function attachTypeChangeListener(priceItem) {
            const typeSelect = priceItem.querySelector('select[name="prices[type][]"]');
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    const priceInput = this.closest('.price-item').querySelector('.price-input');
                    if (priceInput) {
                        updatePriceBadge(priceInput);
                    }
                });
            }
        }
        
        // Format price input with thousand separators
        function formatPriceInput(input) {
            let value = input.value.replace(/[^\d]/g, '');
            
            if (value) {
                value = parseInt(value, 10).toLocaleString('id-ID');
                input.value = value;
            }
        }
        
        // Update price badge preview
        function updatePriceBadge(input) {
            const priceItem = input.closest('.price-item');
            if (priceItem) {
                const typeSelect = priceItem.querySelector('select[name="prices[type][]"]');
                const badge = priceItem.querySelector('.price-type-badge');
                const priceValue = input.value || '0';
                
                if (typeSelect && badge) {
                    const selectedOption = typeSelect.options[typeSelect.selectedIndex];
                    const typeName = selectedOption.text || 'Reguler';
                    
                    badge.textContent = `${typeName}: Rp ${priceValue}`;
                    
                    // Update badge color based on type
                    const typeValue = typeSelect.value;
                    badge.className = 'price-type-badge ' + getPriceTypeClass(typeValue);
                }
            }
        }
        
        // Live preview for package
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const descriptionInput = document.getElementById('description');
            const durationInput = document.getElementById('duration');
            const previewDiv = document.getElementById('package-preview');
            
            function updatePreview() {
                const name = nameInput?.value || 'Nama Paket';
                const description = descriptionInput?.value || 'Deskripsi paket akan muncul di sini...';
                const duration = durationInput?.value || '12';
                
                let html = `
                    <div class="package-preview-card">
                        <div class="text-center mb-3">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-box-open fa-3x text-muted"></i>
                            </div>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">${name}</h6>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-light-green text-dark me-2">
                                <i class="fas fa-clock me-1"></i>${duration} hari
                            </span>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>Aktif
                            </span>
                        </div>
                        <p class="text-muted small mb-0">${description.substring(0, 150)}${description.length > 150 ? '...' : ''}</p>
                    </div>
                `;
                
                if (previewDiv) {
                    previewDiv.innerHTML = html;
                }
            }
            
            // Update preview on input
            if (nameInput) nameInput.addEventListener('input', updatePreview);
            if (descriptionInput) descriptionInput.addEventListener('input', updatePreview);
            if (durationInput) durationInput.addEventListener('input', updatePreview);
            
            // Initial preview
            updatePreview();
            
            // Image preview
            const imageInput = document.getElementById('image');
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (previewDiv) {
                                const imgPreview = previewDiv.querySelector('.package-preview-card .bg-light i');
                                if (imgPreview) {
                                    imgPreview.parentElement.innerHTML = `
                                        <img src="${e.target.result}" 
                                             class="img-fluid rounded" 
                                             style="height: 150px; object-fit: cover; width: 100%;">
                                    `;
                                }
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // File size validation
            if (imageInput) {
                imageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const maxSize = 5 * 1024 * 1024; // 5MB
                        if (file.size > maxSize) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Terlalu Besar',
                                text: 'Ukuran file maksimal 5MB'
                            });
                            this.value = '';
                        }
                    }
                });
            }
            
            // Initialize price functionality if on edit/create page
            if (window.location.search.includes('action=create') || window.location.search.includes('action=edit')) {
                initPriceFormatting();
                initRemoveButtons();
                
                // Attach type change listeners to existing price items
                document.querySelectorAll('.price-item').forEach(item => {
                    attachTypeChangeListener(item);
                });
            }
            
            // Form submission handling
            const form = document.getElementById('package-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Show loading state
                    const submitBtn = document.getElementById('submit-btn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
                        submitBtn.disabled = true;
                    }
                    
                    // Validate prices
                    const priceInputs = document.querySelectorAll('.price-input');
                    let hasValidPrice = false;
                    
                    priceInputs.forEach(input => {
                        const rawValue = input.value.replace(/\./g, '');
                        if (rawValue && /^\d+$/.test(rawValue) && parseInt(rawValue) > 0) {
                            hasValidPrice = true;
                        }
                    });
                    
                    if (!hasValidPrice) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Harga Belum Diisi',
                            text: 'Harap isi minimal satu harga yang valid untuk paket ini'
                        }).then(() => {
                            if (submitBtn) {
                                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>' + 
                                    (window.location.search.includes('action=create') ? 'Simpan Paket & Harga' : 'Update Paket & Harga');
                                submitBtn.disabled = false;
                            }
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php
// Helper function to get price type color class
function getPriceTypeClass($type) {
    switch($type) {
        case 'bronze': return 'bg-brown';
        case 'silver': return 'bg-secondary';
        case 'gold': return 'bg-warning text-dark';
        case 'platinum': return 'bg-light';
        default: return 'bg-light-green';
    }
}
?>