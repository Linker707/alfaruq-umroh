<?php
require_once 'includes/auth.php';
checkAccess('master_admin');
$pageTitle = 'Pengaturan Sistem';
require_once 'includes/header.php';

$csrfToken = generateCsrfToken();

// Get all settings
$query = "SELECT key_name, value FROM settings ORDER BY key_name";
$settings = $pdo->query($query)->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    
    // Update settings
    $successCount = 0;
    $error = '';
    
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST['settings'] as $key => $value) {
            $value = trim($value);
            
            $query = "UPDATE settings SET value = ?, updated_at = NOW() WHERE key_name = ?";
            $stmt = $pdo->prepare($query);
            if ($stmt->execute([$value, $key])) {
                $successCount++;
            }
        }
        
        $pdo->commit();
        
        // Log activity
        logActivity('UPDATE_SETTINGS', "Memperbarui $successCount pengaturan");
        
        $_SESSION['success_message'] = "$successCount pengaturan berhasil diperbarui!";
        header('Location: settings.php');
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Gagal memperbarui pengaturan: ' . $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Pengaturan Sistem</h5>
                <p class="text-muted mb-0 small">Kelola pengaturan umum website</p>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-building me-2"></i>Informasi Perusahaan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Perusahaan</label>
                                        <input type="text" class="form-control" 
                                               name="settings[company_name]" 
                                               value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tagline 1</label>
                                        <input type="text" class="form-control" 
                                               name="settings[tagline1]" 
                                               value="<?php echo htmlspecialchars($settings['tagline1'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tagline 2</label>
                                        <input type="text" class="form-control" 
                                               name="settings[tagline2]" 
                                               value="<?php echo htmlspecialchars($settings['tagline2'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Kontak & Alamat</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Alamat Kantor 1</label>
                                        <textarea class="form-control" rows="2" 
                                                  name="settings[office_address1]"><?php echo htmlspecialchars($settings['office_address1'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Alamat Kantor 2</label>
                                        <textarea class="form-control" rows="2" 
                                                  name="settings[office_address2]"><?php echo htmlspecialchars($settings['office_address2'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Telepon Utama</label>
                                        <input type="text" class="form-control" 
                                               name="settings[contact_phone]" 
                                               value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email Perusahaan</label>
                                        <input type="email" class="form-control" 
                                               name="settings[company_email]" 
                                               value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Visi & Misi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Visi</label>
                                        <textarea class="form-control" rows="3" 
                                                  name="settings[vision]"><?php echo htmlspecialchars($settings['vision'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Misi</label>
                                        <textarea class="form-control" rows="5" 
                                                  name="settings[mission]"><?php echo htmlspecialchars($settings['mission'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-phone me-2"></i>Admin & Cabang</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Admin 1</label>
                                        <input type="text" class="form-control" 
                                               name="settings[admin_phone1]" 
                                               value="<?php echo htmlspecialchars($settings['admin_phone1'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Admin 2</label>
                                        <input type="text" class="form-control" 
                                               name="settings[admin_phone2]" 
                                               value="<?php echo htmlspecialchars($settings['admin_phone2'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Alamat Cabang</label>
                                        <textarea class="form-control" rows="4" 
                                                  name="settings[branch_addresses]"><?php echo htmlspecialchars($settings['branch_addresses'] ?? ''); ?></textarea>
                                        <small class="text-muted">Pisahkan dengan baris baru</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Semua Pengaturan
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>