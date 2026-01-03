<?php
require_once 'includes/auth.php';

// Hanya master_admin yang bisa akses
if ($_SESSION['admin_role'] !== 'master_admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Default action
$action = isset($_GET['action']) ? $_GET['action'] : 'edit';
$message = '';
$error = '';

// Load current settings
$settings = [];
$stmt = $pdo->query("SELECT key_name, value FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['key_name']] = $row['value'];
}

// Process update action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action == 'update') {
    $process = processUpdateProfile();
    if ($process['success']) {
        $_SESSION['success_message'] = $process['message'];
        header('Location: profile.php');
        exit;
    }
    $error = $process['message'];
}

// Get message from session
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Function: Update Profile
function processUpdateProfile() {
    global $pdo;
    
    // Define allowed setting keys
    $allowed_keys = [
        'company_name',
        'tagline1', 'tagline2',
        'vision', 'mission',
        'contact_address', 'contact_phone', 'contact_email',
        'ppiu_license',
        'office_address1', 'office_address2',
        'branch_addresses',
        'admin_phone1', 'admin_phone2',
        'company_email'
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($allowed_keys as $key) {
            $value = trim($_POST[$key] ?? '');
            
            // Check if setting exists
            $stmt = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
            $stmt->execute([$key]);
            
            if ($stmt->fetch()) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE key_name = ?");
                $stmt->execute([$value, $key]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)");
                $stmt->execute([$key, $value]);
            }
        }
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_COMPANY_PROFILE', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Memperbarui profil perusahaan"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Profil perusahaan berhasil diperbarui'];
        
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
    <title>Profil Perusahaan - Admin ALFARUQ TEAM</title>
    <style>
        .profile-section {
            margin-bottom: 30px;
        }
        
        .section-header {
            background: linear-gradient(135deg, #f0f9f0 0%, #e8f5e9 100%);
            border-left: 5px solid #4CAF50;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .section-header h5 {
            color: #2E7D32;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #2E7D32;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-label i {
            width: 20px;
            text-align: center;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .preview-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .preview-title {
            color: #2E7D32;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .preview-value {
            color: #333;
            line-height: 1.6;
        }
        
        .info-box {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #2E7D32;
            margin-right: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: #e8f5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: #2E7D32;
            font-size: 1.5rem;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2E7D32;
            margin-bottom: 5px;
        }
        
        .stat-label {
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
        
        <div class="row">
            <div class="col-lg-8">
                <div class="admin-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Profil Perusahaan</h5>
                            <p class="text-white mb-0 opacity-75">Kelola informasi perusahaan ALFARUQ TEAM</p>
                        </div>
                        <a href="../index.php" target="_blank" class="btn-admin-outline">
                            <i class="fas fa-external-link-alt me-2"></i>Lihat Website
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=update">
                            <!-- Company Basic Information -->
                            <div class="profile-section">
                                <div class="section-header">
                                    <h5><i class="fas fa-info-circle"></i>Informasi Dasar Perusahaan</h5>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="company_name" class="form-label">
                                            <i class="fas fa-signature"></i>Nama Perusahaan *
                                        </label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" 
                                               value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>" 
                                               placeholder="Contoh: PT. ALFARUQ ANUGERAH UTAMA" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="ppiu_license" class="form-label">
                                            <i class="fas fa-certificate"></i>Nomor PPIU *
                                        </label>
                                        <input type="text" class="form-control" id="ppiu_license" name="ppiu_license" 
                                               value="<?php echo htmlspecialchars($settings['ppiu_license'] ?? ''); ?>" 
                                               placeholder="Contoh: SK PPIU NO.24022300153650007" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tagline1" class="form-label">
                                        <i class="fas fa-quote-left"></i>Tagline 1 *
                                    </label>
                                    <input type="text" class="form-control" id="tagline1" name="tagline1" 
                                           value="<?php echo htmlspecialchars($settings['tagline1'] ?? ''); ?>" 
                                           placeholder="Contoh: LANGKAH AWAL MENUJU BAITULLAH" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tagline2" class="form-label">
                                        <i class="fas fa-quote-right"></i>Tagline 2 *
                                    </label>
                                    <input type="text" class="form-control" id="tagline2" name="tagline2" 
                                           value="<?php echo htmlspecialchars($settings['tagline2'] ?? ''); ?>" 
                                           placeholder="Contoh: HARGA HEMAT FASILITAS TERHORMAT" required>
                                </div>
                            </div>
                            
                            <!-- Vision & Mission -->
                            <div class="profile-section">
                                <div class="section-header">
                                    <h5><i class="fas fa-bullseye"></i>Visi & Misi Perusahaan</h5>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="vision" class="form-label">
                                        <i class="fas fa-eye"></i>Visi Perusahaan *
                                    </label>
                                    <textarea class="form-control" id="vision" name="vision" 
                                              placeholder="Visi perusahaan..." required><?php echo htmlspecialchars($settings['vision'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="mission" class="form-label">
                                        <i class="fas fa-flag"></i>Misi Perusahaan *
                                    </label>
                                    <textarea class="form-control" id="mission" name="mission" 
                                              placeholder="Misi perusahaan (bisa dalam poin-poin)..." 
                                              rows="6" required><?php echo htmlspecialchars($settings['mission'] ?? ''); ?></textarea>
                                    <small class="text-muted">Gunakan baris baru untuk setiap poin misi</small>
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="profile-section">
                                <div class="section-header">
                                    <h5><i class="fas fa-address-book"></i>Informasi Kontak</h5>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_phone" class="form-label">
                                            <i class="fas fa-phone"></i>Telepon Kontak *
                                        </label>
                                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                               value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" 
                                               placeholder="Contoh: +62 812-6630-3236" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_email" class="form-label">
                                            <i class="fas fa-envelope"></i>Email Kontak *
                                        </label>
                                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                               value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" 
                                               placeholder="Contoh: alfaruq5619@gmail.com" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_address" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>Alamat Kontak *
                                    </label>
                                    <textarea class="form-control" id="contact_address" name="contact_address" 
                                              placeholder="Alamat lengkap perusahaan..." required><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Office Addresses -->
                            <div class="profile-section">
                                <div class="section-header">
                                    <h5><i class="fas fa-building"></i>Alamat Kantor</h5>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="office_address1" class="form-label">
                                        <i class="fas fa-map-pin"></i>Alamat Kantor 1 (Utama) *
                                    </label>
                                    <textarea class="form-control" id="office_address1" name="office_address1" 
                                              placeholder="Alamat kantor utama..." required><?php echo htmlspecialchars($settings['office_address1'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="office_address2" class="form-label">
                                        <i class="fas fa-map-pin"></i>Alamat Kantor 2 (Cabang)
                                    </label>
                                    <textarea class="form-control" id="office_address2" name="office_address2" 
                                              placeholder="Alamat kantor cabang..."><?php echo htmlspecialchars($settings['office_address2'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="branch_addresses" class="form-label">
                                        <i class="fas fa-code-branch"></i>Alamat Cabang Lainnya
                                    </label>
                                    <textarea class="form-control" id="branch_addresses" name="branch_addresses" 
                                              placeholder="Daftar alamat cabang lainnya..."><?php echo htmlspecialchars($settings['branch_addresses'] ?? ''); ?></textarea>
                                    <small class="text-muted">Gunakan baris baru untuk setiap cabang</small>
                                </div>
                            </div>
                            
                            <!-- Admin Contacts -->
                            <div class="profile-section">
                                <div class="section-header">
                                    <h5><i class="fas fa-user-tie"></i>Kontak Admin</h5>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_phone1" class="form-label">
                                            <i class="fas fa-phone-alt"></i>Nomor Admin 1 *
                                        </label>
                                        <input type="text" class="form-control" id="admin_phone1" name="admin_phone1" 
                                               value="<?php echo htmlspecialchars($settings['admin_phone1'] ?? ''); ?>" 
                                               placeholder="Contoh: +6281266303236" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_phone2" class="form-label">
                                            <i class="fas fa-phone-alt"></i>Nomor Admin 2
                                        </label>
                                        <input type="text" class="form-control" id="admin_phone2" name="admin_phone2" 
                                               value="<?php echo htmlspecialchars($settings['admin_phone2'] ?? ''); ?>" 
                                               placeholder="Contoh: +6281377327477">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="company_email" class="form-label">
                                        <i class="fas fa-at"></i>Email Perusahaan *
                                    </label>
                                    <input type="email" class="form-control" id="company_email" name="company_email" 
                                           value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>" 
                                           placeholder="Contoh: alfaruq5619@gmail.com" required>
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <i class="fas fa-info-circle"></i>
                                <strong>Informasi:</strong> Semua informasi yang diisi akan ditampilkan di website publik ALFARUQ TEAM.
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                                </a>
                                <button type="submit" class="btn-admin-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
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
                        <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview Profil</h5>
                    </div>
                    <div class="card-body">
                        <div class="preview-card">
                            <div class="preview-title">
                                <i class="fas fa-building"></i> Nama Perusahaan
                            </div>
                            <div class="preview-value" id="preview-company-name">
                                <?php echo htmlspecialchars($settings['company_name'] ?? 'PT. ALFARUQ ANUGERAH UTAMA'); ?>
                            </div>
                        </div>
                        
                        <div class="preview-card">
                            <div class="preview-title">
                                <i class="fas fa-certificate"></i> Lisensi PPIU
                            </div>
                            <div class="preview-value" id="preview-ppiu">
                                <?php echo htmlspecialchars($settings['ppiu_license'] ?? 'SK PPIU NO.24022300153650007'); ?>
                            </div>
                        </div>
                        
                        <div class="preview-card">
                            <div class="preview-title">
                                <i class="fas fa-quote-left"></i> Tagline
                            </div>
                            <div class="preview-value" id="preview-tagline">
                                <?php echo htmlspecialchars(($settings['tagline1'] ?? 'LANGKAH AWAL MENUJU BAITULLAH') . ' - ' . ($settings['tagline2'] ?? 'HARGA HEMAT FASILITAS TERHORMAT')); ?>
                            </div>
                        </div>
                        
                        <div class="preview-card">
                            <div class="preview-title">
                                <i class="fas fa-phone"></i> Kontak
                            </div>
                            <div class="preview-value">
                                <div id="preview-contact-phone">
                                    <strong>Telepon:</strong> <?php echo htmlspecialchars($settings['contact_phone'] ?? '+62 812-6630-3236'); ?>
                                </div>
                                <div id="preview-contact-email" class="mt-2">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($settings['contact_email'] ?? 'alfaruq5619@gmail.com'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="admin-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Website</h5>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-number" id="stat-users">
                                    <?php 
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="stat-label">Total Users</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="stat-number" id="stat-packages">
                                    <?php 
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM packages WHERE is_active = 1");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="stat-label">Paket Aktif</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div class="stat-number" id="stat-testimonials">
                                    <?php 
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_approved = 1");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="stat-label">Testimoni Disetujui</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-images"></i>
                                </div>
                                <div class="stat-number" id="stat-gallery">
                                    <?php 
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM galleries WHERE is_active = 1");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="stat-label">Foto Galeri</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-link me-2"></i>Tautan Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="../index.php" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-external-link-alt me-2"></i>Lihat Website
                            </a>
                            <a href="package.php" class="btn btn-outline-success">
                                <i class="fas fa-box-open me-2"></i>Kelola Paket
                            </a>
                            <a href="testimonial.php" class="btn btn-outline-info">
                                <i class="fas fa-comments me-2"></i>Kelola Testimoni
                            </a>
                            <a href="gallery.php" class="btn btn-outline-warning">
                                <i class="fas fa-images me-2"></i>Kelola Galeri
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Live preview update
        document.addEventListener('DOMContentLoaded', function() {
            // Company name preview
            const companyNameInput = document.getElementById('company_name');
            const companyNamePreview = document.getElementById('preview-company-name');
            
            if (companyNameInput && companyNamePreview) {
                companyNameInput.addEventListener('input', function() {
                    companyNamePreview.textContent = this.value || 'PT. ALFARUQ ANUGERAH UTAMA';
                });
            }
            
            // PPIU preview
            const ppiuInput = document.getElementById('ppiu_license');
            const ppiuPreview = document.getElementById('preview-ppiu');
            
            if (ppiuInput && ppiuPreview) {
                ppiuInput.addEventListener('input', function() {
                    ppiuPreview.textContent = this.value || 'SK PPIU NO.24022300153650007';
                });
            }
            
            // Tagline preview
            const tagline1Input = document.getElementById('tagline1');
            const tagline2Input = document.getElementById('tagline2');
            const taglinePreview = document.getElementById('preview-tagline');
            
            function updateTaglinePreview() {
                const tagline1 = tagline1Input?.value || 'LANGKAH AWAL MENUJU BAITULLAH';
                const tagline2 = tagline2Input?.value || 'HARGA HEMAT FASILITAS TERHORMAT';
                taglinePreview.textContent = tagline1 + ' - ' + tagline2;
            }
            
            if (tagline1Input) tagline1Input.addEventListener('input', updateTaglinePreview);
            if (tagline2Input) tagline2Input.addEventListener('input', updateTaglinePreview);
            
            // Contact preview
            const contactPhoneInput = document.getElementById('contact_phone');
            const contactEmailInput = document.getElementById('contact_email');
            const contactPhonePreview = document.getElementById('preview-contact-phone');
            const contactEmailPreview = document.getElementById('preview-contact-email');
            
            function updateContactPreview() {
                if (contactPhoneInput && contactPhonePreview) {
                    contactPhonePreview.innerHTML = `<strong>Telepon:</strong> ${contactPhoneInput.value || '+62 812-6630-3236'}`;
                }
                if (contactEmailInput && contactEmailPreview) {
                    contactEmailPreview.innerHTML = `<strong>Email:</strong> ${contactEmailInput.value || 'alfaruq5619@gmail.com'}`;
                }
            }
            
            if (contactPhoneInput) contactPhoneInput.addEventListener('input', updateContactPreview);
            if (contactEmailInput) contactEmailInput.addEventListener('input', updateContactPreview);
            
            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    let firstInvalidField = null;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            if (!firstInvalidField) {
                                firstInvalidField = field;
                            }
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Form Belum Lengkap',
                            text: 'Harap isi semua field yang wajib diisi',
                            timer: 3000,
                            showConfirmButton: false
                        });
                        
                        // Focus on first invalid field
                        if (firstInvalidField) {
                            firstInvalidField.focus();
                        }
                    } else {
                        // Show loading state
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
                            submitBtn.disabled = true;
                        }
                    }
                });
            }
            
            // Add validation styling
            const inputs = document.querySelectorAll('input[required], textarea[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>
</body>
</html>