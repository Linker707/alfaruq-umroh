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
$message = '';
$error = '';

// Process actions
switch ($action) {
    case 'create':
        $process = processCreateSchedule();
        if ($process['success']) {
            header('Location: schedule.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
        
    case 'update':
        $process = processUpdateSchedule($id);
        if ($process['success']) {
            header('Location: schedule.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
        
    case 'delete':
        $process = processDeleteSchedule($id);
        if ($process['success']) {
            header('Location: schedule.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
}

// Get message from URL
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

// Get schedule data for edit
$schedule = null;
if ($id > 0 && $action == 'edit') {
    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
    $stmt->execute([$id]);
    $schedule = $stmt->fetch();
    
    if (!$schedule) {
        header('Location: schedule.php');
        exit;
    }
}

// Get all schedules for list
$stmt = $pdo->query("SELECT * FROM schedules ORDER BY departure_date ASC");
$schedules = $stmt->fetchAll();

// Get active packages for dropdown
$packages = $pdo->query("SELECT id, name FROM packages WHERE is_active = 1 ORDER BY name")->fetchAll();

// Function: Create Schedule
function processCreateSchedule() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $departure_date = $_POST['departure_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $available_slots = (int)($_POST['available_slots'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $airline = trim($_POST['airline'] ?? '');
    $route = trim($_POST['route'] ?? '');
    $departure_day = trim($_POST['departure_day'] ?? '');
    $duration_days = (int)($_POST['duration_days'] ?? 0);
    $package_id = (int)($_POST['package_id'] ?? 0);
    
    // Validation
    if (empty($departure_date) || empty($return_date) || empty($airline) || empty($route)) {
        return ['success' => false, 'message' => 'Tanggal, maskapai, dan rute harus diisi'];
    }
    
    if (strtotime($departure_date) >= strtotime($return_date)) {
        return ['success' => false, 'message' => 'Tanggal kembali harus setelah tanggal keberangkatan'];
    }
    
    if ($available_slots < 0) {
        return ['success' => false, 'message' => 'Slot tersedia tidak boleh negatif'];
    }
    
    if ($duration_days <= 0) {
        return ['success' => false, 'message' => 'Durasi hari harus lebih dari 0'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Auto-calculate duration if not provided
        if ($duration_days == 0) {
            $departure = new DateTime($departure_date);
            $return = new DateTime($return_date);
            $interval = $departure->diff($return);
            $duration_days = $interval->days;
        }
        
        // Auto-determine departure day name
        if (empty($departure_day)) {
            $day_names = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
            $departure_day_num = date('w', strtotime($departure_date));
            $departure_day = $day_names[$departure_day_num];
        }
        
        // Get package info if package_id is provided
        $package_name = $package_description = $package_price = null;
        if ($package_id > 0) {
            $stmt = $pdo->prepare("SELECT name, description FROM packages WHERE id = ?");
            $stmt->execute([$package_id]);
            if ($package = $stmt->fetch()) {
                $package_name = $package['name'];
                $package_description = $package['description'];
                
                // Get min price from package_prices
                $price_stmt = $pdo->prepare("SELECT MIN(price) as min_price FROM package_prices WHERE package_id = ? AND is_active = 1");
                $price_stmt->execute([$package_id]);
                $price = $price_stmt->fetch();
                $package_price = $price['min_price'];
            }
        }
        
        // Insert schedule
        $stmt = $pdo->prepare("INSERT INTO schedules (departure_date, return_date, available_slots, status, airline, route, departure_day, duration_days, package_name, package_description, package_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $departure_date, 
            $return_date, 
            $available_slots, 
            $status, 
            $airline, 
            $route, 
            $departure_day, 
            $duration_days,
            $package_name,
            $package_description,
            $package_price
        ]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'ADD_SCHEDULE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menambahkan jadwal baru: $departure_date - $airline"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Jadwal berhasil ditambahkan'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Update Schedule
function processUpdateSchedule($id) {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $departure_date = $_POST['departure_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $available_slots = (int)($_POST['available_slots'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $airline = trim($_POST['airline'] ?? '');
    $route = trim($_POST['route'] ?? '');
    $departure_day = trim($_POST['departure_day'] ?? '');
    $duration_days = (int)($_POST['duration_days'] ?? 0);
    $package_id = (int)($_POST['package_id'] ?? 0);
    
    // Validation
    if (empty($departure_date) || empty($return_date) || empty($airline) || empty($route)) {
        return ['success' => false, 'message' => 'Tanggal, maskapai, dan rute harus diisi'];
    }
    
    if (strtotime($departure_date) >= strtotime($return_date)) {
        return ['success' => false, 'message' => 'Tanggal kembali harus setelah tanggal keberangkatan'];
    }
    
    if ($available_slots < 0) {
        return ['success' => false, 'message' => 'Slot tersedia tidak boleh negatif'];
    }
    
    if ($duration_days <= 0) {
        return ['success' => false, 'message' => 'Durasi hari harus lebih dari 0'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get package info if package_id is provided
        $package_name = $package_description = $package_price = null;
        if ($package_id > 0) {
            $stmt = $pdo->prepare("SELECT name, description FROM packages WHERE id = ?");
            $stmt->execute([$package_id]);
            if ($package = $stmt->fetch()) {
                $package_name = $package['name'];
                $package_description = $package['description'];
                
                // Get min price from package_prices
                $price_stmt = $pdo->prepare("SELECT MIN(price) as min_price FROM package_prices WHERE package_id = ? AND is_active = 1");
                $price_stmt->execute([$package_id]);
                $price = $price_stmt->fetch();
                $package_price = $price['min_price'];
            }
        }
        
        // Update schedule
        $stmt = $pdo->prepare("UPDATE schedules SET departure_date = ?, return_date = ?, available_slots = ?, status = ?, airline = ?, route = ?, departure_day = ?, duration_days = ?, package_name = ?, package_description = ?, package_price = ? WHERE id = ?");
        $stmt->execute([
            $departure_date, 
            $return_date, 
            $available_slots, 
            $status, 
            $airline, 
            $route, 
            $departure_day, 
            $duration_days,
            $package_name,
            $package_description,
            $package_price,
            $id
        ]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_SCHEDULE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Memperbarui jadwal: $departure_date - $airline"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Jadwal berhasil diperbarui'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Delete Schedule
function processDeleteSchedule($id) {
    global $pdo;
    
    // Get schedule info for log
    $stmt = $pdo->prepare("SELECT departure_date, airline FROM schedules WHERE id = ?");
    $stmt->execute([$id]);
    $schedule = $stmt->fetch();
    
    if (!$schedule) {
        return ['success' => false, 'message' => 'Jadwal tidak ditemukan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete schedule
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'DELETE_SCHEDULE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menghapus jadwal: {$schedule['departure_date']} - {$schedule['airline']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Jadwal berhasil dihapus'];
        
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
    <title>Kelola Jadwal - Admin ALFARUQ TEAM</title>
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
        <!-- LIST SCHEDULES -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Daftar Jadwal Keberangkatan</h5>
                    <p class="text-white mb-0 opacity-75">Total <?php echo count($schedules); ?> jadwal terdaftar</p>
                </div>
                <a href="?action=create" class="btn-admin-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Jadwal Baru
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($schedules)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                        <h5 class="text-muted">Belum ada jadwal</h5>
                        <p class="text-muted mb-4">Mulai dengan menambahkan jadwal baru</p>
                        <a href="?action=create" class="btn-admin-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Jadwal Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal Keberangkatan</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Maskapai</th>
                                    <th>Rute</th>
                                    <th>Slot</th>
                                    <th>Status</th>
                                    <th>Durasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $item): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light-green text-dark">#<?php echo $item['id']; ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-medium">
                                            <?php echo date('d M Y', strtotime($item['departure_date'])); ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            <?php echo $item['departure_day']; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="fw-medium">
                                            <?php echo date('d M Y', strtotime($item['return_date'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light-green p-2 me-3">
                                                <i class="fas fa-plane text-primary-green"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($item['airline']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['route']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($item['available_slots'] > 0): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-user-check me-1"></i>
                                                <?php echo $item['available_slots']; ?> slot
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-user-slash me-1"></i>
                                                Habis
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['status'] === 'available'): ?>
                                            <span class="status-badge status-active">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Tersedia
                                            </span>
                                        <?php elseif ($item['status'] === 'full'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Penuh
                                            </span>
                                        <?php elseif ($item['status'] === 'cancelled'): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-ban me-1"></i>
                                                Dibatalkan
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <?php echo htmlspecialchars($item['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-green text-dark">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo $item['duration_days']; ?> hari
                                        </span>
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
                                                    onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo addslashes($item['airline'] . ' - ' . date('d M Y', strtotime($item['departure_date']))); ?>')"
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
        <!-- CREATE/EDIT SCHEDULE FORM -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $action == 'create' ? 'plus' : 'edit'; ?> me-2"></i>
                            <?php echo $action == 'create' ? 'Tambah Jadwal Baru' : 'Edit Jadwal: ' . htmlspecialchars($schedule['airline'] . ' - ' . date('d M Y', strtotime($schedule['departure_date']))); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=<?php echo $action == 'create' ? 'create' : 'update&id=' . $id; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="departure_date" class="form-label">Tanggal Keberangkatan *</label>
                                    <input type="date" class="form-control" id="departure_date" name="departure_date" 
                                           value="<?php echo $action == 'edit' ? $schedule['departure_date'] : ''; ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="return_date" class="form-label">Tanggal Kembali *</label>
                                    <input type="date" class="form-control" id="return_date" name="return_date" 
                                           value="<?php echo $action == 'edit' ? $schedule['return_date'] : ''; ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="airline" class="form-label">Maskapai *</label>
                                    <input type="text" class="form-control" id="airline" name="airline" 
                                           value="<?php echo $action == 'edit' ? htmlspecialchars($schedule['airline']) : ''; ?>" 
                                           placeholder="Contoh: LION AIR, BATIK AIR" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="route" class="form-label">Rute *</label>
                                    <input type="text" class="form-control" id="route" name="route" 
                                           value="<?php echo $action == 'edit' ? htmlspecialchars($schedule['route']) : ''; ?>" 
                                           placeholder="Contoh: BTH JED BTH" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="departure_day" class="form-label">Hari Keberangkatan</label>
                                    <select class="form-control" id="departure_day" name="departure_day">
                                        <option value="">Otomatis dari tanggal</option>
                                        <option value="MINGGU" <?php echo ($action == 'edit' && $schedule['departure_day'] == 'MINGGU') ? 'selected' : ''; ?>>MINGGU</option>
                                        <option value="SENIN" <?php echo ($action == 'edit' && $schedule['departure_day'] == 'SENIN') ? 'selected' : ''; ?>>SENIN</option>
                                        <option value="SELASA" <?php echo ($action == 'edit' && $schedule['departure_day'] == 'SELASA') ? 'selected' : ''; ?>>SELASA</option>
                                        <option value="RABU" <?php echo ($action == 'edit' && $schedule['departure_day'] == 'RABU') ? 'selected' : ''; ?>>RABU</option>
                                        <option value="KAMIS" <?php echo ($action == 'edit' && $schedule['departure_day'] == 'KAMIS') ? 'selected' : ''; ?>>KAMIS</option>
                                        <option value="JUMAT" <?php echo ($action == 'edit' && $schedule['departure_day'] == 'JUMAT') ? 'selected' : ''; ?>>JUMAT</option>
                                        <option value="SABTU" <?php echo ($action == 'edit' && $schedule['departure_day'] == 'SABTU') ? 'selected' : ''; ?>>SABTU</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="available_slots" class="form-label">Slot Tersedia *</label>
                                    <input type="number" class="form-control" id="available_slots" name="available_slots" 
                                           value="<?php echo $action == 'edit' ? $schedule['available_slots'] : '45'; ?>" 
                                           min="0" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="duration_days" class="form-label">Durasi (hari) *</label>
                                    <input type="number" class="form-control" id="duration_days" name="duration_days" 
                                           value="<?php echo $action == 'edit' ? $schedule['duration_days'] : '12'; ?>" 
                                           min="1" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="available" <?php echo ($action == 'edit' && $schedule['status'] == 'available') ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="full" <?php echo ($action == 'edit' && $schedule['status'] == 'full') ? 'selected' : ''; ?>>Penuh</option>
                                        <option value="cancelled" <?php echo ($action == 'edit' && $schedule['status'] == 'cancelled') ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="package_id" class="form-label">Paket Terkait</label>
                                    <select class="form-control" id="package_id" name="package_id">
                                        <option value="">-- Pilih Paket --</option>
                                        <?php foreach ($packages as $package): ?>
                                            <option value="<?php echo $package['id']; ?>" 
                                                <?php echo ($action == 'edit' && isset($schedule['package_name']) && $schedule['package_name'] == $package['name']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($package['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Informasi:</strong> 
                                <?php if ($action == 'create'): ?>
                                    Jika "Hari Keberangkatan" dikosongkan, sistem akan menentukan otomatis dari tanggal.
                                <?php else: ?>
                                    Mengubah paket terkait akan memperbarui informasi paket di jadwal ini.
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="schedule.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn-admin-primary">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $action == 'create' ? 'Simpan Jadwal' : 'Update Jadwal'; ?>
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
        function confirmDelete(scheduleId, scheduleName) {
            Swal.fire({
                title: 'Hapus Jadwal?',
                html: `Apakah Anda yakin ingin menghapus jadwal <strong>${scheduleName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?action=delete&id=' + scheduleId;
                }
            });
        }
        
        // Auto-calculate duration when dates change
        document.addEventListener('DOMContentLoaded', function() {
            const departureDateInput = document.getElementById('departure_date');
            const returnDateInput = document.getElementById('return_date');
            const durationInput = document.getElementById('duration_days');
            
            if (departureDateInput && returnDateInput && durationInput) {
                function calculateDuration() {
                    if (departureDateInput.value && returnDateInput.value) {
                        const departure = new Date(departureDateInput.value);
                        const returnDate = new Date(returnDateInput.value);
                        
                        if (returnDate > departure) {
                            const diffTime = Math.abs(returnDate - departure);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                            durationInput.value = diffDays;
                        }
                    }
                }
                
                departureDateInput.addEventListener('change', calculateDuration);
                returnDateInput.addEventListener('change', calculateDuration);
            }
            
            // Set min date for departure date to today
            if (departureDateInput) {
                const today = new Date().toISOString().split('T')[0];
                departureDateInput.min = today;
                
                // Set min for return date when departure date is selected
                departureDateInput.addEventListener('change', function() {
                    if (returnDateInput) {
                        const departureDate = new Date(this.value);
                        const nextDay = new Date(departureDate);
                        nextDay.setDate(nextDay.getDate() + 1);
                        returnDateInput.min = nextDay.toISOString().split('T')[0];
                        
                        // If return date is before new min, clear it
                        if (returnDateInput.value && new Date(returnDateInput.value) < nextDay) {
                            returnDateInput.value = '';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>