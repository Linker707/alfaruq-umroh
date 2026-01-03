<?php
require_once 'includes/auth.php';

// Hanya admin yang bisa akses (master_admin dan admin)
if ($_SESSION['admin_role'] !== 'master_admin' && $_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Default tab and action
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'destinations';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Define upload path
define('UPLOAD_DIR', dirname(dirname(__FILE__)) . '/assets/img/');
define('WEB_UPLOAD_DIR', 'assets/img/');

// Handle file upload for gallery
function handleGalleryImageUpload($old_image = null) {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        return $old_image;
    }
    
    $upload_dir = UPLOAD_DIR;
    $web_upload_dir = WEB_UPLOAD_DIR;
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $file = $_FILES['image'];
    
    // Check error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Terjadi kesalahan saat upload gambar');
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
    $filename = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
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
    
    return $web_upload_dir . $filename;
}

// Process actions based on tab
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($tab === 'destinations') {
        switch ($action) {
            case 'create':
                $process = processCreateDestination();
                if ($process['success']) {
                    $_SESSION['success_message'] = $process['message'];
                    header('Location: gallery.php?tab=destinations');
                    exit;
                }
                $error = $process['message'];
                break;
                
            case 'update':
                $process = processUpdateDestination($id);
                if ($process['success']) {
                    $_SESSION['success_message'] = $process['message'];
                    header('Location: gallery.php?tab=destinations');
                    exit;
                }
                $error = $process['message'];
                break;
        }
    } elseif ($tab === 'gallery') {
        switch ($action) {
            case 'create':
                $process = processCreateGallery();
                if ($process['success']) {
                    $_SESSION['success_message'] = $process['message'];
                    header('Location: gallery.php?tab=gallery');
                    exit;
                }
                $error = $process['message'];
                break;
                
            case 'update':
                $process = processUpdateGallery($id);
                if ($process['success']) {
                    $_SESSION['success_message'] = $process['message'];
                    header('Location: gallery.php?tab=gallery');
                    exit;
                }
                $error = $process['message'];
                break;
        }
    }
}

// Process GET actions (delete)
if ($action == 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($tab === 'destinations') {
        $process = processDeleteDestination($id);
        if ($process['success']) {
            $_SESSION['success_message'] = $process['message'];
            header('Location: gallery.php?tab=destinations');
            exit;
        }
        $error = $process['message'];
    } elseif ($tab === 'gallery') {
        $process = processDeleteGallery($id);
        if ($process['success']) {
            $_SESSION['success_message'] = $process['message'];
            header('Location: gallery.php?tab=gallery');
            exit;
        }
        $error = $process['message'];
    }
}

// Get message from session
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Get data for edit
$destination = null;
$gallery_item = null;

if ($id > 0 && $action == 'edit') {
    if ($tab === 'destinations') {
        $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
        $stmt->execute([$id]);
        $destination = $stmt->fetch();
        
        if (!$destination) {
            header('Location: gallery.php?tab=destinations');
            exit;
        }
    } elseif ($tab === 'gallery') {
        $stmt = $pdo->prepare("SELECT g.*, d.name as destination_name FROM galleries g LEFT JOIN destinations d ON g.destination_id = d.id WHERE g.id = ?");
        $stmt->execute([$id]);
        $gallery_item = $stmt->fetch();
        
        if (!$gallery_item) {
            header('Location: gallery.php?tab=gallery');
            exit;
        }
    }
}

// Get all destinations for dropdown in gallery
$destinations = $pdo->query("SELECT id, name FROM destinations WHERE is_active = 1 ORDER BY name")->fetchAll();

// Get all destinations for list
$all_destinations = $pdo->query("SELECT * FROM destinations ORDER BY name")->fetchAll();

// Get all gallery items for list
$stmt = $pdo->query("SELECT g.*, d.name as destination_name FROM galleries g LEFT JOIN destinations d ON g.destination_id = d.id ORDER BY g.created_at DESC");
$gallery_items = $stmt->fetchAll();

// Function: Create Destination
function processCreateDestination() {
    global $pdo;
    
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($slug)) {
        return ['success' => false, 'message' => 'Nama dan slug harus diisi'];
    }
    
    // Check duplicate name
    $stmt = $pdo->prepare("SELECT id FROM destinations WHERE name = ? OR slug = ?");
    $stmt->execute([$name, $slug]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Nama atau slug sudah digunakan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insert destination
        $stmt = $pdo->prepare("INSERT INTO destinations (name, slug, icon, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $icon, $is_active]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'ADD_DESTINATION', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menambahkan destinasi baru: $name"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Destinasi berhasil ditambahkan'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Update Destination
function processUpdateDestination($id) {
    global $pdo;
    
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($slug)) {
        return ['success' => false, 'message' => 'Nama dan slug harus diisi'];
    }
    
    // Check duplicate name (exclude current)
    $stmt = $pdo->prepare("SELECT id FROM destinations WHERE (name = ? OR slug = ?) AND id != ?");
    $stmt->execute([$name, $slug, $id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Nama atau slug sudah digunakan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update destination
        $stmt = $pdo->prepare("UPDATE destinations SET name = ?, slug = ?, icon = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $icon, $is_active, $id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_DESTINATION', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Memperbarui destinasi: $name"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Destinasi berhasil diperbarui'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Delete Destination
function processDeleteDestination($id) {
    global $pdo;
    
    // Get destination info for log
    $stmt = $pdo->prepare("SELECT name FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $destination = $stmt->fetch();
    
    if (!$destination) {
        return ['success' => false, 'message' => 'Destinasi tidak ditemukan'];
    }
    
    // Check if destination has gallery items
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM galleries WHERE destination_id = ?");
    $stmt->execute([$id]);
    $gallery_count = $stmt->fetchColumn();
    
    if ($gallery_count > 0) {
        return ['success' => false, 'message' => 'Tidak dapat menghapus destinasi karena terdapat ' . $gallery_count . ' foto di galeri yang terkait'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete destination
        $stmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'DELETE_DESTINATION', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menghapus destinasi: {$destination['name']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Destinasi berhasil dihapus'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Create Gallery
function processCreateGallery() {
    global $pdo;
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $destination_id = (int)($_POST['destination_id'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($title)) {
        return ['success' => false, 'message' => 'Judul harus diisi'];
    }
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'Gambar harus diupload'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Handle image upload
        $image = handleGalleryImageUpload();
        
        // Get destination name if destination_id is provided
        $destination_name = null;
        if ($destination_id > 0) {
            $stmt = $pdo->prepare("SELECT name FROM destinations WHERE id = ?");
            $stmt->execute([$destination_id]);
            $destination = $stmt->fetch();
            $destination_name = $destination ? $destination['name'] : null;
        }
        
        // Insert gallery
        $stmt = $pdo->prepare("INSERT INTO galleries (title, description, image, destination_id, destination, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $image, $destination_id, $destination_name, $is_active]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'ADD_GALLERY', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menambahkan foto galeri: $title"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Foto galeri berhasil ditambahkan'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Update Gallery
function processUpdateGallery($id) {
    global $pdo;
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $destination_id = (int)($_POST['destination_id'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($title)) {
        return ['success' => false, 'message' => 'Judul harus diisi'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get old gallery data for image deletion
        $stmt = $pdo->prepare("SELECT image FROM galleries WHERE id = ?");
        $stmt->execute([$id]);
        $old_gallery = $stmt->fetch();
        $old_image = $old_gallery['image'];
        
        // Handle image upload
        $image = $old_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image = handleGalleryImageUpload($old_image);
        } elseif (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            // Delete image if checkbox is checked
            if ($old_image && file_exists(dirname(dirname(__FILE__)) . '/' . $old_image)) {
                @unlink(dirname(dirname(__FILE__)) . '/' . $old_image);
            }
            $image = null;
        }
        
        // Get destination name if destination_id is provided
        $destination_name = null;
        if ($destination_id > 0) {
            $stmt = $pdo->prepare("SELECT name FROM destinations WHERE id = ?");
            $stmt->execute([$destination_id]);
            $destination = $stmt->fetch();
            $destination_name = $destination ? $destination['name'] : null;
        }
        
        // Update gallery
        $stmt = $pdo->prepare("UPDATE galleries SET title = ?, description = ?, image = ?, destination_id = ?, destination = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$title, $description, $image, $destination_id, $destination_name, $is_active, $id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_GALLERY', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Memperbarui foto galeri: $title"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Foto galeri berhasil diperbarui'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Delete Gallery
function processDeleteGallery($id) {
    global $pdo;
    
    // Get gallery info for log
    $stmt = $pdo->prepare("SELECT title, image FROM galleries WHERE id = ?");
    $stmt->execute([$id]);
    $gallery = $stmt->fetch();
    
    if (!$gallery) {
        return ['success' => false, 'message' => 'Foto galeri tidak ditemukan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete gallery
        $stmt = $pdo->prepare("DELETE FROM galleries WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete image file
        if ($gallery['image'] && file_exists(dirname(dirname(__FILE__)) . '/' . $gallery['image'])) {
            @unlink(dirname(dirname(__FILE__)) . '/' . $gallery['image']);
        }
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'DELETE_GALLERY', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menghapus foto galeri: {$gallery['title']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Foto galeri berhasil dihapus'];
        
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
    <title>Kelola Destinasi & Galeri - Admin ALFARUQ TEAM</title>
    <style>
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .gallery-item {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .gallery-image {
            height: 200px;
            overflow: hidden;
        }
        
        .gallery-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .gallery-info {
            padding: 15px;
        }
        
        .destination-badge {
            font-size: 0.8rem;
            padding: 3px 10px;
            border-radius: 20px;
            background: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #C8E6C9;
        }
        
        /* Icon selector */
        .icon-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .icon-option {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .icon-option:hover,
        .icon-option.selected {
            border-color: #4CAF50;
            background: #E8F5E9;
            transform: scale(1.1);
        }
        
        .icon-option input[type="radio"] {
            display: none;
        }
        
        /* Tab navigation */
        .tab-nav {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .tab-nav a {
            padding: 12px 25px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab-nav a:hover {
            color: #4CAF50;
            background: #f9f9f9;
        }
        
        .tab-nav a.active {
            color: #4CAF50;
            border-bottom-color: #4CAF50;
            background: #f0f9f0;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 0.9rem;
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
        
        <!-- Tab Navigation -->
        <div class="admin-card mb-4">
            <div class="card-body p-0">
                <div class="tab-nav">
                    <a href="?tab=destinations" class="<?php echo $tab == 'destinations' ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt"></i> Destinasi
                    </a>
                    <a href="?tab=gallery" class="<?php echo $tab == 'gallery' ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i> Galeri
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($all_destinations); ?></div>
                    <div class="stats-label">Total Destinasi</div>
                    <small class="text-muted"><?php echo count(array_filter($all_destinations, fn($d) => $d['is_active'])); ?> aktif</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($gallery_items); ?></div>
                    <div class="stats-label">Total Foto Galeri</div>
                    <small class="text-muted"><?php echo count(array_filter($gallery_items, fn($g) => $g['is_active'])); ?> aktif</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($destinations); ?></div>
                    <div class="stats-label">Destinasi Aktif</div>
                    <small class="text-muted">Untuk dropdown galeri</small>
                </div>
            </div>
        </div>
        
        <!-- Destinations Tab -->
        <div class="tab-content <?php echo $tab == 'destinations' ? 'active' : ''; ?>" id="destinations-tab">
            <?php if ($action == 'list'): ?>
            <!-- LIST DESTINATIONS -->
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Daftar Destinasi</h5>
                        <p class="text-white mb-0 opacity-75">Kelola destinasi perjalanan umroh</p>
                    </div>
                    <a href="?tab=destinations&action=create" class="btn-admin-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Destinasi
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($all_destinations)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-map-marker-alt fa-4x text-muted mb-4"></i>
                            <h5 class="text-muted">Belum ada destinasi</h5>
                            <p class="text-muted mb-4">Mulai dengan menambahkan destinasi baru</p>
                            <a href="?tab=destinations&action=create" class="btn-admin-primary">
                                <i class="fas fa-plus me-2"></i>Tambah Destinasi Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th width="50">Icon</th>
                                        <th>Nama Destinasi</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                        <th>Total Foto</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_destinations as $item): 
                                        // Count gallery items for this destination
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM galleries WHERE destination_id = ?");
                                        $stmt->execute([$item['id']]);
                                        $photo_count = $stmt->fetchColumn();
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fs-4"><?php echo htmlspecialchars($item['icon']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-medium"><?php echo htmlspecialchars($item['name']); ?></div>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($item['slug']); ?></code>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $item['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <i class="fas fa-<?php echo $item['is_active'] ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                                <?php echo $item['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-green text-dark">
                                                <i class="fas fa-image me-1"></i>
                                                <?php echo $photo_count; ?> foto
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?tab=destinations&action=edit&id=<?php echo $item['id']; ?>" 
                                                   class="btn btn-outline-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        onclick="confirmDeleteDestination(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>')"
                                                        class="btn btn-outline-danger" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php elseif ($action == 'create' || $action == 'edit'): ?>
            <!-- CREATE/EDIT DESTINATION FORM -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="admin-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-<?php echo $action == 'create' ? 'plus' : 'edit'; ?> me-2"></i>
                                <?php echo $action == 'create' ? 'Tambah Destinasi Baru' : 'Edit Destinasi: ' . htmlspecialchars($destination['name']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="?tab=destinations&action=<?php echo $action == 'create' ? 'create' : 'update&id=' . $id; ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nama Destinasi *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo $action == 'edit' ? htmlspecialchars($destination['name']) : ''; ?>" 
                                               placeholder="Contoh: Makkah" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="slug" class="form-label">Slug *</label>
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?php echo $action == 'edit' ? htmlspecialchars($destination['slug']) : ''; ?>" 
                                               placeholder="Contoh: makkah" required>
                                        <small class="text-muted">URL-friendly version (huruf kecil, tanpa spasi)</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pilih Icon</label>
                                    <input type="text" class="form-control mb-2" id="icon" name="icon" 
                                           value="<?php echo $action == 'edit' ? htmlspecialchars($destination['icon']) : ''; ?>" 
                                           placeholder="Contoh: 🕋 atau Masukkan emoji/teks">
                                    
                                    <div class="icon-options">
                                        <?php 
                                        $common_icons = ['🕋', '🕌', '🛕', '⛪', '🛐', '🕍', '🗿', '⛰️', '🏔️', '🌋', '🗻', '🏕️', '🏖️', '🏜️', '🏝️', '🏞️', '🌅', '🌄', '🏙️', '🌆', '🌇', '🌃', '🗾', '🏞️', '📍', '🚩', '🎌', '🏴', '🏳️', '✈️', '🛫', '🛬', '🛩️', '💺', '🛰️', '🛸', '🚁', '🚂', '🚃', '🚅', '🚇', '🚌', '🚗', '🚕', '🚙', '🚲', '🏍️', '🛺', '🚠', '🚡', '🚢', '⛵', '🚤', '🛥️', '🛳️', '⚓', '🚧', '🏗️', '🏘️', '🏛️', '🏟️', '🏠', '🏡', '🏢', '🏣', '🏤', '🏥', '🏦', '🏨', '🏩', '🏪', '🏫', '🏬', '🏭', '🏯', '🏰', '💒', '🗼', '🗽', '⛲', '⛺', '🌁', '🌉', '🌊'];
                                        ?>
                                        <?php foreach ($common_icons as $icon_char): ?>
                                        <label class="icon-option <?php echo ($action == 'edit' && $destination['icon'] == $icon_char) ? 'selected' : ''; ?>">
                                            <input type="radio" name="icon_radio" value="<?php echo htmlspecialchars($icon_char); ?>" 
                                                   <?php echo ($action == 'edit' && $destination['icon'] == $icon_char) ? 'checked' : ''; ?>>
                                            <?php echo $icon_char; ?>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" <?php echo ($action == 'edit' && $destination['is_active']) || $action == 'create' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Aktif (tampilkan di website)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informasi:</strong> 
                                    Destinasi yang aktif akan muncul di dropdown saat menambahkan foto galeri.
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="?tab=destinations" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn-admin-primary">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $action == 'create' ? 'Simpan Destinasi' : 'Update Destinasi'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Gallery Tab -->
        <div class="tab-content <?php echo $tab == 'gallery' ? 'active' : ''; ?>" id="gallery-tab">
            <?php if ($action == 'list'): ?>
            <!-- LIST GALLERY -->
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-images me-2"></i>Daftar Foto Galeri</h5>
                        <p class="text-white mb-0 opacity-75">Kelola foto-foto perjalanan umroh</p>
                    </div>
                    <a href="?tab=gallery&action=create" class="btn-admin-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Foto
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($gallery_items)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-4x text-muted mb-4"></i>
                            <h5 class="text-muted">Belum ada foto galeri</h5>
                            <p class="text-muted mb-4">Mulai dengan menambahkan foto baru</p>
                            <a href="?tab=gallery&action=create" class="btn-admin-primary">
                                <i class="fas fa-plus me-2"></i>Tambah Foto Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="gallery-grid">
                            <?php foreach ($gallery_items as $item): ?>
                            <div class="gallery-item admin-card">
                                <div class="gallery-image">
                                    <?php if ($item['image']): ?>
                                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 100%;">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="gallery-info">
                                    <h6 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    
                                    <?php if ($item['description']): ?>
                                    <p class="text-muted small mb-2"><?php echo substr(strip_tags($item['description']), 0, 80); ?>...</p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <?php if ($item['destination_name']): ?>
                                        <span class="destination-badge">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($item['destination_name']); ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Tidak ada destinasi</span>
                                        <?php endif; ?>
                                        
                                        <span class="status-badge <?php echo $item['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fas fa-<?php echo $item['is_active'] ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                            <?php echo $item['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="?tab=gallery&action=edit&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <button type="button" 
                                                onclick="confirmDeleteGallery(<?php echo $item['id']; ?>, '<?php echo addslashes($item['title']); ?>')"
                                                class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php elseif ($action == 'create' || $action == 'edit'): ?>
            <!-- CREATE/EDIT GALLERY FORM -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="admin-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-<?php echo $action == 'create' ? 'plus' : 'edit'; ?> me-2"></i>
                                <?php echo $action == 'create' ? 'Tambah Foto Galeri Baru' : 'Edit Foto: ' . htmlspecialchars($gallery_item['title']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="?tab=gallery&action=<?php echo $action == 'create' ? 'create' : 'update&id=' . $id; ?>" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Foto *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo $action == 'edit' ? htmlspecialchars($gallery_item['title']) : ''; ?>" 
                                           placeholder="Contoh: Sholat di Masjidil Haram" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="3" placeholder="Deskripsi foto (opsional)"><?php echo $action == 'edit' ? htmlspecialchars($gallery_item['description']) : ''; ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="destination_id" class="form-label">Destinasi Terkait</label>
                                        <select class="form-control" id="destination_id" name="destination_id">
                                            <option value="">-- Pilih Destinasi (opsional) --</option>
                                            <?php foreach ($destinations as $dest): ?>
                                                <option value="<?php echo $dest['id']; ?>" 
                                                    <?php echo ($action == 'edit' && $gallery_item['destination_id'] == $dest['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dest['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                   value="1" <?php echo ($action == 'edit' && $gallery_item['is_active']) || $action == 'create' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">
                                                Aktif (tampilkan di website)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="image" class="form-label">Upload Foto *</label>
                                    <?php if ($action == 'create'): ?>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                        <small class="text-muted">Format: JPG, PNG, GIF, WebP. Maks: 5MB. Ukuran disarankan: 800x600px</small>
                                    <?php else: ?>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <small class="text-muted">Kosongkan jika tidak ingin mengganti foto</small>
                                        
                                        <?php if ($gallery_item['image']): ?>
                                        <div class="mt-3">
                                            <p class="mb-2">Foto saat ini:</p>
                                            <img src="../<?php echo htmlspecialchars($gallery_item['image']); ?>" 
                                                 alt="Current Image" 
                                                 class="img-thumbnail" 
                                                 style="max-height: 200px;">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="delete_image" name="delete_image" value="1">
                                                <label class="form-check-label text-danger" for="delete_image">
                                                    Hapus foto saat disimpan
                                                </label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Tips:</strong> 
                                    Pilih destinasi untuk mengelompokkan foto berdasarkan lokasi perjalanan.
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="?tab=gallery" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn-admin-primary">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $action == 'create' ? 'Simpan Foto' : 'Update Foto'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Preview Card -->
                    <div class="admin-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview Foto</h5>
                        </div>
                        <div class="card-body">
                            <div id="gallery-preview">
                                <p class="text-muted">Isi form di samping untuk melihat preview...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik Foto per Destinasi</h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $stmt = $pdo->query("SELECT d.name, COUNT(g.id) as count FROM destinations d LEFT JOIN galleries g ON d.id = g.destination_id GROUP BY d.id ORDER BY count DESC");
                            $stats = $stmt->fetchAll();
                            
                            if ($stats): 
                            ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($stats as $stat): ?>
                                <li class="mb-2 d-flex justify-content-between">
                                    <span><?php echo htmlspecialchars($stat['name']); ?></span>
                                    <span class="badge bg-light-green"><?php echo $stat['count']; ?> foto</span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <p class="text-muted mb-0">Belum ada statistik</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Tab switching
        document.addEventListener('DOMContentLoaded', function() {
            // Set active tab based on URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'destinations';
            
            // Show active tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelector(`#${tab}-tab`).classList.add('active');
            
            // Icon selector
            document.querySelectorAll('.icon-option input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('icon').value = this.value;
                    
                    // Update selected class
                    document.querySelectorAll('.icon-option').forEach(option => {
                        option.classList.remove('selected');
                    });
                    this.closest('.icon-option').classList.add('selected');
                });
            });
            
            // Auto-generate slug from name
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');
            
            if (nameInput && slugInput) {
                nameInput.addEventListener('input', function() {
                    if (!slugInput.dataset.manual) {
                        const slug = this.value
                            .toLowerCase()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/--+/g, '-');
                        slugInput.value = slug;
                    }
                });
                
                slugInput.addEventListener('input', function() {
                    if (this.value) {
                        this.dataset.manual = true;
                    }
                });
            }
            
            // Gallery preview
            const titleInput = document.getElementById('title');
            const descriptionInput = document.getElementById('description');
            const destinationSelect = document.getElementById('destination_id');
            const previewDiv = document.getElementById('gallery-preview');
            
            function updateGalleryPreview() {
                const title = titleInput?.value || 'Judul Foto';
                const description = descriptionInput?.value || 'Deskripsi foto...';
                const destinationId = destinationSelect?.value;
                let destinationName = '';
                
                if (destinationSelect && destinationId) {
                    const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
                    destinationName = selectedOption.text || '';
                }
                
                let html = `
                    <div class="gallery-preview-card">
                        <div class="gallery-image mb-3">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">${title}</h6>
                        <p class="text-muted small mb-2">${description.substring(0, 100)}${description.length > 100 ? '...' : ''}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            ${destinationName ? 
                                `<span class="destination-badge">
                                    <i class="fas fa-map-marker-alt me-1"></i>${destinationName}
                                </span>` : 
                                '<span class="badge bg-secondary">Tidak ada destinasi</span>'
                            }
                            <span class="status-badge status-active">
                                <i class="fas fa-check-circle me-1"></i>Aktif
                            </span>
                        </div>
                    </div>
                `;
                
                if (previewDiv) {
                    previewDiv.innerHTML = html;
                }
            }
            
            // Update preview on input
            if (titleInput) titleInput.addEventListener('input', updateGalleryPreview);
            if (descriptionInput) descriptionInput.addEventListener('input', updateGalleryPreview);
            if (destinationSelect) destinationSelect.addEventListener('change', updateGalleryPreview);
            
            // Initial preview
            updateGalleryPreview();
            
            // Image preview
            const imageInput = document.getElementById('image');
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (previewDiv) {
                                const imgPreview = previewDiv.querySelector('.gallery-preview-card .bg-light i');
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
        });
        
        // Confirm delete functions
        function confirmDeleteDestination(destinationId, destinationName) {
            Swal.fire({
                title: 'Hapus Destinasi?',
                html: `Apakah Anda yakin ingin menghapus destinasi <strong>${destinationName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?tab=destinations&action=delete&id=' + destinationId;
                }
            });
        }
        
        function confirmDeleteGallery(galleryId, galleryTitle) {
            Swal.fire({
                title: 'Hapus Foto?',
                html: `Apakah Anda yakin ingin menghapus foto <strong>${galleryTitle}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?tab=gallery&action=delete&id=' + galleryId;
                }
            });
        }
    </script>
</body>
</html>