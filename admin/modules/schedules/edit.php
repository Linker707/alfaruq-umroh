<?php
require_once '../../includes/auth.php';
checkAccess('admin');
$pageTitle = 'Edit Jadwal';
require_once '../../includes/header.php';

$csrfToken = generateCsrfToken();

// Get schedule ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Get schedule data
$query = "SELECT * FROM schedules WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    
    // Get and validate form data
    $departure_date = $_POST['departure_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $available_slots = (int)($_POST['available_slots'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $airline = trim($_POST['airline'] ?? '');
    $route = trim($_POST['route'] ?? '');
    $departure_day = trim($_POST['departure_day'] ?? '');
    $duration_days = (int)($_POST['duration_days'] ?? 12);
    
    // Validation
    $errors = [];
    
    if (empty($departure_date)) $errors[] = 'Tanggal keberangkatan harus diisi';
    if (empty($return_date)) $errors[] = 'Tanggal kembali harus diisi';
    if ($available_slots < 0) $errors[] = 'Slot tidak valid';
    if (empty($airline)) $errors[] = 'Nama maskapai harus diisi';
    if (empty($route)) $errors[] = 'Rute harus diisi';
    
    if (empty($errors)) {
        try {
            // Update schedule
            $query = "UPDATE schedules SET 
                      departure_date = ?, return_date = ?, available_slots = ?, status = ?,
                      airline = ?, route = ?, departure_day = ?, duration_days = ?
                      WHERE id = ?";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                $departure_date,
                $return_date,
                $available_slots,
                $status,
                $airline,
                $route,
                $departure_day,
                $duration_days,
                $id
            ]);
            
            // Log activity
            logActivity('UPDATE_SCHEDULE', "Memperbarui jadwal ID: $id");
            
            // Success message
            $_SESSION['success_message'] = 'Jadwal berhasil diperbarui!';
            header('Location: index.php');
            exit;
            
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui jadwal: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Jadwal Keberangkatan</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="row g-3">
                        <!-- Tanggal Keberangkatan -->
                        <div class="col-md-6">
                            <label for="departure_date" class="form-label">Tanggal Keberangkatan *</label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" 
                                   value="<?php echo $schedule['departure_date']; ?>" required>
                        </div>
                        
                        <!-- Tanggal Kembali -->
                        <div class="col-md-6">
                            <label for="return_date" class="form-label">Tanggal Kembali *</label>
                            <input type="date" class="form-control" id="return_date" name="return_date" 
                                   value="<?php echo $schedule['return_date']; ?>" required>
                        </div>
                        
                        <!-- Maskapai -->
                        <div class="col-md-6">
                            <label for="airline" class="form-label">Maskapai *</label>
                            <input type="text" class="form-control" id="airline" name="airline" 
                                   value="<?php echo htmlspecialchars($schedule['airline']); ?>" 
                                   placeholder="Contoh: LION AIR" required>
                        </div>
                        
                        <!-- Rute -->
                        <div class="col-md-6">
                            <label for="route" class="form-label">Rute *</label>
                            <input type="text" class="form-control" id="route" name="route" 
                                   value="<?php echo htmlspecialchars($schedule['route']); ?>" 
                                   placeholder="Contoh: BTH JED BTH" required>
                        </div>
                        
                        <!-- Hari Keberangkatan -->
                        <div class="col-md-4">
                            <label for="departure_day" class="form-label">Hari Keberangkatan</label>
                            <select class="form-select" id="departure_day" name="departure_day">
                                <option value="">Pilih Hari</option>
                                <option value="SENIN" <?php echo $schedule['departure_day'] == 'SENIN' ? 'selected' : ''; ?>>Senin</option>
                                <option value="SELASA" <?php echo $schedule['departure_day'] == 'SELASA' ? 'selected' : ''; ?>>Selasa</option>
                                <option value="RABU" <?php echo $schedule['departure_day'] == 'RABU' ? 'selected' : ''; ?>>Rabu</option>
                                <option value="KAMIS" <?php echo $schedule['departure_day'] == 'KAMIS' ? 'selected' : ''; ?>>Kamis</option>
                                <option value="JUMAT" <?php echo $schedule['departure_day'] == 'JUMAT' ? 'selected' : ''; ?>>Jumat</option>
                                <option value="SABTU" <?php echo $schedule['departure_day'] == 'SABTU' ? 'selected' : ''; ?>>Sabtu</option>
                                <option value="MINGGU" <?php echo $schedule['departure_day'] == 'MINGGU' ? 'selected' : ''; ?>>Minggu</option>
                            </select>
                        </div>
                        
                        <!-- Jumlah Slot -->
                        <div class="col-md-4">
                            <label for="available_slots" class="form-label">Jumlah Slot *</label>
                            <input type="number" class="form-control" id="available_slots" name="available_slots" 
                                   value="<?php echo $schedule['available_slots']; ?>" min="0" max="999" required>
                        </div>
                        
                        <!-- Durasi -->
                        <div class="col-md-4">
                            <label for="duration_days" class="form-label">Durasi (hari)</label>
                            <input type="number" class="form-control" id="duration_days" name="duration_days" 
                                   value="<?php echo $schedule['duration_days'] ?? 12; ?>" min="1" max="30">
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-12">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="available" <?php echo $schedule['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="full" <?php echo $schedule['status'] == 'full' ? 'selected' : ''; ?>>Full</option>
                                <option value="cancelled" <?php echo $schedule['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>