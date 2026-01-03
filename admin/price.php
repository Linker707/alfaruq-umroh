<?php
require_once 'includes/auth.php';

// Hanya master_admin yang bisa akses
if ($_SESSION['admin_role'] !== 'master_admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Default action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Process actions
switch ($action) {
    case 'create':
        $process = processCreatePrice();
        if ($process['success']) {
            header('Location: price.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
        
    case 'update':
        $process = processUpdatePrice($id);
        if ($process['success']) {
            header('Location: price.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
        
    case 'delete':
        $process = processDeletePrice($id);
        if ($process['success']) {
            header('Location: price.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
}

// Get message from URL
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

// Get price data for edit
$price = null;
if ($id > 0 && $action == 'edit') {
    $stmt = $pdo->prepare("SELECT pp.*, p.name as package_name FROM package_prices pp LEFT JOIN packages p ON pp.package_id = p.id WHERE pp.id = ?");
    $stmt->execute([$id]);
    $price = $stmt->fetch();
    
    if (!$price) {
        header('Location: price.php');
        exit;
    }
}

// Get all prices for list
$stmt = $pdo->query("SELECT pp.*, p.name as package_name FROM package_prices pp LEFT JOIN packages p ON pp.package_id = p.id ORDER BY pp.package_id, pp.type");
$prices = $stmt->fetchAll();

// Get active packages for dropdown
$packages = $pdo->query("SELECT id, name FROM packages WHERE is_active = 1 ORDER BY name")->fetchAll();

// Price types
$price_types = [
    '' => 'Default / Reguler',
    'bronze' => 'Bronze',
    'silver' => 'Silver', 
    'gold' => 'Gold',
    'platinum' => 'Platinum'
];

// Function: Create Price
function processCreatePrice() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $package_id = (int)($_POST['package_id'] ?? 0);
    $type = trim($_POST['type'] ?? '');
    $price_value = str_replace(['.', ','], '', $_POST['price'] ?? '');
    
    // Validation
    if ($package_id <= 0) {
        return ['success' => false, 'message' => 'Paket harus dipilih'];
    }
    
    if (!is_numeric($price_value) || $price_value <= 0) {
        return ['success' => false, 'message' => 'Harga harus berupa angka positif'];
    }
    
    // Check if package exists
    $stmt = $pdo->prepare("SELECT id FROM packages WHERE id = ? AND is_active = 1");
    $stmt->execute([$package_id]);
    if (!$stmt->fetch()) {
        return ['success' => false, 'message' => 'Paket tidak ditemukan'];
    }
    
    // Check duplicate price type for same package
    $stmt = $pdo->prepare("SELECT id FROM package_prices WHERE package_id = ? AND type = ?");
    $stmt->execute([$package_id, $type]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Tipe harga untuk paket ini sudah ada'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get package name for log
        $stmt = $pdo->prepare("SELECT name FROM packages WHERE id = ?");
        $stmt->execute([$package_id]);
        $package = $stmt->fetch();
        
        // Insert price
        $stmt = $pdo->prepare("INSERT INTO package_prices (package_id, type, price) VALUES (?, ?, ?)");
        $stmt->execute([$package_id, $type, $price_value]);
        
        // Log activity
        $type_name = empty($type) ? 'Reguler' : ucfirst($type);
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'ADD_PRICE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menambahkan harga $type_name untuk paket: {$package['name']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Harga berhasil ditambahkan'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Update Price
function processUpdatePrice($id) {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $package_id = (int)($_POST['package_id'] ?? 0);
    $type = trim($_POST['type'] ?? '');
    $price_value = str_replace(['.', ','], '', $_POST['price'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if ($package_id <= 0) {
        return ['success' => false, 'message' => 'Paket harus dipilih'];
    }
    
    if (!is_numeric($price_value) || $price_value <= 0) {
        return ['success' => false, 'message' => 'Harga harus berupa angka positif'];
    }
    
    // Check if package exists
    $stmt = $pdo->prepare("SELECT id FROM packages WHERE id = ? AND is_active = 1");
    $stmt->execute([$package_id]);
    if (!$stmt->fetch()) {
        return ['success' => false, 'message' => 'Paket tidak ditemukan'];
    }
    
    // Check duplicate price type for same package (exclude current)
    $stmt = $pdo->prepare("SELECT id FROM package_prices WHERE package_id = ? AND type = ? AND id != ?");
    $stmt->execute([$package_id, $type, $id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Tipe harga untuk paket ini sudah ada'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get old price info for log
        $stmt = $pdo->prepare("SELECT pp.*, p.name as package_name FROM package_prices pp LEFT JOIN packages p ON pp.package_id = p.id WHERE pp.id = ?");
        $stmt->execute([$id]);
        $old_price = $stmt->fetch();
        
        // Update price
        $stmt = $pdo->prepare("UPDATE package_prices SET package_id = ?, type = ?, price = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$package_id, $type, $price_value, $is_active, $id]);
        
        // Log activity
        $type_name = empty($type) ? 'Reguler' : ucfirst($type);
        $old_type_name = empty($old_price['type']) ? 'Reguler' : ucfirst($old_price['type']);
        $old_price_fmt = number_format($old_price['price'], 0, ',', '.');
        $new_price_fmt = number_format($price_value, 0, ',', '.');
        
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_PRICE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], 
            "Memperbarui harga {$old_price['package_name']} ($old_type_name: Rp $old_price_fmt) menjadi ($type_name: Rp $new_price_fmt)"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Harga berhasil diperbarui'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Delete Price
function processDeletePrice($id) {
    global $pdo;
    
    // Get price info for log
    $stmt = $pdo->prepare("SELECT pp.*, p.name as package_name FROM package_prices pp LEFT JOIN packages p ON pp.package_id = p.id WHERE pp.id = ?");
    $stmt->execute([$id]);
    $price = $stmt->fetch();
    
    if (!$price) {
        return ['success' => false, 'message' => 'Harga tidak ditemukan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete price
        $stmt = $pdo->prepare("DELETE FROM package_prices WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        $type_name = empty($price['type']) ? 'Reguler' : ucfirst($price['type']);
        $price_fmt = number_format($price['price'], 0, ',', '.');
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'DELETE_PRICE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menghapus harga $type_name (Rp $price_fmt) untuk paket: {$price['package_name']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Harga berhasil dihapus'];
        
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
    <title>Kelola Harga Paket - Admin ALFARUQ TEAM</title>
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
        <!-- LIST PRICES -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Daftar Harga Paket</h5>
                    <p class="text-white mb-0 opacity-75">Total <?php echo count($prices); ?> harga terdaftar</p>
                </div>
                <a href="?action=create" class="btn-admin-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Harga Baru
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($prices)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tags fa-4x text-muted mb-4"></i>
                        <h5 class="text-muted">Belum ada harga</h5>
                        <p class="text-muted mb-4">Mulai dengan menambahkan harga baru</p>
                        <a href="?action=create" class="btn-admin-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Harga Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Paket</th>
                                    <th>Tipe</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prices as $item): 
                                    $type_name = empty($item['type']) ? 'Reguler' : ucfirst($item['type']);
                                    $type_color = match($item['type']) {
                                        'bronze' => 'bg-brown',
                                        'silver' => 'bg-secondary',
                                        'gold' => 'bg-warning text-dark',
                                        'platinum' => 'bg-light',
                                        default => 'bg-light-green'
                                    };
                                    $price_formatted = 'Rp ' . number_format($item['price'], 0, ',', '.');
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light-green text-dark">#<?php echo $item['id']; ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?php echo htmlspecialchars($item['package_name']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $type_color; ?>">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($type_name); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo $price_formatted; ?></div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $item['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fas fa-<?php echo $item['is_active'] ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                            <?php echo $item['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $item['id']; ?>" 
                                               class="btn btn-outline-warning" 
                                               data-bs-toggle="tooltip" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo addslashes($item['package_name'] . ' - ' . $type_name . ': ' . $price_formatted); ?>')"
                                                    class="btn btn-outline-danger" 
                                                    data-bs-toggle="tooltip" 
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
        <!-- CREATE/EDIT PRICE FORM -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $action == 'create' ? 'plus' : 'edit'; ?> me-2"></i>
                            <?php echo $action == 'create' ? 'Tambah Harga Baru' : 'Edit Harga: ' . htmlspecialchars($price['package_name'] . ' - ' . (empty($price['type']) ? 'Reguler' : ucfirst($price['type']))); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=<?php echo $action == 'create' ? 'create' : 'update&id=' . $id; ?>">
                            <div class="mb-4">
                                <label for="package_id" class="form-label">Paket *</label>
                                <select class="form-control" id="package_id" name="package_id" required>
                                    <option value="">-- Pilih Paket --</option>
                                    <?php foreach ($packages as $package): ?>
                                        <option value="<?php echo $package['id']; ?>" 
                                            <?php echo ($action == 'edit' && $price['package_id'] == $package['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($package['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="type" class="form-label">Tipe Harga *</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <?php foreach ($price_types as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" 
                                            <?php echo ($action == 'edit' && $price['type'] == $value) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Pilih "Default / Reguler" jika hanya ada 1 harga untuk paket ini</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="price" class="form-label">Harga (Rp) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" id="price" name="price" 
                                           value="<?php echo $action == 'edit' ? number_format($price['price'], 0, ',', '.') : ''; ?>" 
                                           placeholder="Contoh: 29.500.000" 
                                           required>
                                </div>
                                <small class="text-muted">Masukkan angka tanpa titik atau koma (akan diformat otomatis)</small>
                            </div>
                            
                            <?php if ($action == 'edit'): ?>
                            <div class="mb-4">
                                <label class="form-label">Status</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           value="1" <?php echo $price['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Aktif (tampilkan di website)
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Informasi:</strong> 
                                Pastikan tipe harga untuk setiap paket unik (tidak boleh duplikat).
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="price.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn-admin-primary">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $action == 'create' ? 'Simpan Harga' : 'Update Harga'; ?>
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
        function confirmDelete(priceId, priceInfo) {
            Swal.fire({
                title: 'Hapus Harga?',
                html: `Apakah Anda yakin ingin menghapus harga <strong>${priceInfo}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?action=delete&id=' + priceId;
                }
            });
        }
        
        // Format price input
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('price');
            
            if (priceInput) {
                priceInput.addEventListener('input', function() {
                    let value = this.value.replace(/[^\d]/g, '');
                    
                    if (value) {
                        // Format dengan titik sebagai pemisah ribuan
                        value = parseInt(value, 10).toLocaleString('id-ID');
                        this.value = value;
                    }
                });
                
                // Validasi saat form submit
                const form = priceInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        let rawValue = priceInput.value.replace(/\./g, '');
                        if (!/^\d+$/.test(rawValue)) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'error',
                                title: 'Format Harga Salah',
                                text: 'Harga harus berupa angka positif'
                            });
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>