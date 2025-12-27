<?php
// contact.php - Hanya update style CSS
require_once 'config/database.php';

$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN (
    'office_address1', 
    'office_address2', 
    'branch_addresses', 
    'admin_phone1', 
    'admin_phone2', 
    'company_email', 
    'contact_email', 
    'tagline1', 
    'tagline2', 
    'contact_phone'
)";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);

$officeAddress1 = $settings['office_address1'] ?? '';
$officeAddress2 = $settings['office_address2'] ?? '';
$branchAddresses = $settings['branch_addresses'] ?? '';
$adminPhone1 = $settings['admin_phone1'] ?? '';
$adminPhone2 = $settings['admin_phone2'] ?? '';
$companyEmail = $settings['company_email'] ?? $settings['contact_email'] ?? '';
$tagline1 = $settings['tagline1'] ?? '';
$tagline2 = $settings['tagline2'] ?? '';
$whatsapp = $settings['contact_phone'] ?? "+6281234567890";

// Proses form
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $userMessage = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($phone) || empty($userMessage)) {
        $message = '<div class="alert alert-danger rounded-pill text-center">Semua field harus diisi!</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger rounded-pill text-center">Email tidak valid!</div>';
    } else {
        $whatsappMessage = 
            "Halo ALFARUQ TEAM, saya ingin menghubungi Anda.\n\n" .
            "Nama: $name\n" .
            "Email: $email\n" .
            "Telepon: $phone\n" .
            "Pesan: $userMessage";

        $encodedMessage = urlencode($whatsappMessage);
        $whatsappUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp) . "?text=$encodedMessage";

        header("Location: $whatsappUrl");
        exit;
    }
}
?>

<?php include 'views/header.php'; ?>

<!-- Load Core JavaScript -->
<script src="js/modern-green.js"></script>
<script src="js/responsive.js"></script>

<!-- Load Form Validation hanya di halaman contact -->
<script src="js/form-validation.js"></script>

<!-- Inline CSS untuk animasi -->
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .is-valid {
        border-color: #28a745 !important;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
    }
    
    .success-message {
        animation: fadeIn 0.5s ease-in;
    }
    
    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
</style>

<!-- Hero Section - Style modern -->
<section class="py-5 bg-green-gradient text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold text-white">Hubungi Kami</h1>
        <p class="lead text-white opacity-90"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section Lokasi Kantor -->
<section id="locations" class="py-5 bg-green-50">
    <div class="container">
        <h2 class="text-center mb-5 text-green-900 fw-bold">Lokasi Kantor & Cabang</h2>
        <div class="row">
            <!-- Kantor Pusat -->
            <div class="col-md-4 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern text-center d-flex flex-column">
                        <div class="icon-box-modern mx-auto mb-4">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5 class="card-title-modern text-green-900 mb-3">Kantor Pusat</h5>
                        <p class="card-text-modern text-neutral-700 mb-4 flex-grow-1"><?php echo htmlspecialchars($officeAddress1 ?: 'Alamat belum tersedia.'); ?></p>
                        <div class="mt-auto">
                            <p class="text-neutral-600 mb-2"><i class="fas fa-clock text-green-600 me-2"></i>Senin - Jumat: 08:30 - 17:00</p>
                            <p class="text-neutral-600"><i class="fas fa-clock text-green-600 me-2"></i>Sabtu: 08:30 - 14:00</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kantor Cabang Utama -->
            <div class="col-md-4 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern text-center d-flex flex-column">
                        <div class="icon-box-modern mx-auto mb-4">
                            <i class="fas fa-store"></i>
                        </div>
                        <h5 class="card-title-modern text-green-900 mb-3">Kantor Cabang Utama</h5>
                        <p class="card-text-modern text-neutral-700 mb-4 flex-grow-1"><?php echo htmlspecialchars($officeAddress2 ?: 'Alamat belum tersedia.'); ?></p>
                        <div class="mt-auto">
                            <p class="text-neutral-600 mb-2"><i class="fas fa-clock text-green-600 me-2"></i>Senin - Jumat: 08:30 - 17:00</p>
                            <p class="text-neutral-600"><i class="fas fa-clock text-green-600 me-2"></i>Sabtu: 08:30 - 14:00</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cabang Lainnya -->
            <div class="col-md-4 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern text-center d-flex flex-column">
                        <div class="icon-box-modern mx-auto mb-4">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5 class="card-title-modern text-green-900 mb-3">Cabang Lainnya</h5>
                        <div class="card-text-modern text-neutral-700 mb-4 flex-grow-1">
                            <?php if ($branchAddresses): ?>
                                <?php foreach (explode(';', $branchAddresses) as $branch): ?>
                                    <p class="mb-2"><?php echo htmlspecialchars(trim($branch)); ?></p>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-neutral-600">Cabang belum tersedia.</p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-auto">
                            <small class="text-neutral-600"><i class="fas fa-info-circle text-green-600 me-1"></i>Jam operasional bervariasi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Informasi Kontak -->
<section id="contact-info" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5 text-green-900 fw-bold">Informasi Kontak</h2>
        <div class="row">
            <!-- Admin 1 -->
            <div class="col-md-4 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern text-center d-flex flex-column">
                        <div class="icon-box-modern mx-auto mb-4">
                            <!-- GANTI ICON INI -->
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h5 class="card-title-modern text-green-900 mb-3">Admin 1</h5>
                        <?php if ($adminPhone1): ?>
                            <p class="card-text-modern text-neutral-700 mb-4"><?php echo htmlspecialchars($adminPhone1); ?></p>
                            <div class="mt-auto">
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $adminPhone1); ?>?text=Halo%20Admin%201,%20saya%20ingin%20konsultasi" 
                                class="btn-modern-green primary with-icon w-100" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i>Chat WhatsApp
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="card-text-modern text-neutral-700 mb-4">Nomor belum tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Admin 2 -->
            <div class="col-md-4 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern text-center d-flex flex-column">
                        <div class="icon-box-modern mx-auto mb-4">
                            <!-- GANTI ICON INI -->
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h5 class="card-title-modern text-green-900 mb-3">Admin 2</h5>
                        <?php if ($adminPhone2): ?>
                            <p class="card-text-modern text-neutral-700 mb-4"><?php echo htmlspecialchars($adminPhone2); ?></p>
                            <div class="mt-auto">
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $adminPhone2); ?>?text=Halo%20Admin%202,%20saya%20ingin%20konsultasi" 
                                class="btn-modern-green primary with-icon w-100" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i>Chat WhatsApp
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="card-text-modern text-neutral-700 mb-4">Nomor belum tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="col-md-4 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern text-center d-flex flex-column">
                        <div class="icon-box-modern mx-auto mb-4">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5 class="card-title-modern text-green-900 mb-3">Email</h5>
                        <?php if (!empty($companyEmail)): ?>
                            <p class="card-text-modern text-neutral-700 mb-4"><?php echo htmlspecialchars($companyEmail); ?></p>
                            <div class="mt-auto">
                                <a href="mailto:<?php echo htmlspecialchars($companyEmail); ?>" 
                                   class="btn-modern-green primary with-icon w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Email
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="card-text-modern text-neutral-700 mb-4">Email belum tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Form Kontak -->
<section id="contact-form" class="py-5 bg-green-50">
    <div class="container">
        <h2 class="text-center mb-5 text-green-900 fw-bold">Kirim Pesan Langsung</h2>
        <div class="row justify-content-center">
            <!-- Form Kontak -->
            <div class="col-lg-8 col-md-10 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern">
                        <div class="icon-box-modern mx-auto mb-4 text-center">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4 class="text-green-900 text-center mb-4">Kirim Pesan</h4>
                        
                        <!-- Tampilkan pesan error/success dari PHP -->
                        <?php if ($message): ?>
                            <div class="alert-message mb-4">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="contactForm" method="POST" action="">
                            <!-- Nama Lengkap -->
                            <div class="mb-3">
                                <label for="fullname" class="form-label text-green-900">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control-modern" 
                                       id="fullname" 
                                       name="fullname" 
                                       placeholder="Masukkan nama lengkap Anda"
                                       required>
                                <div class="invalid-feedback">
                                    Mohon isi nama lengkap Anda.
                                </div>
                            </div>
                            
                            <!-- Nomor Telepon -->
                            <div class="mb-3">
                                <label for="phone" class="form-label text-green-900">
                                    Nomor Telepon <span class="text-danger">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control-modern" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="Contoh: 081234567890"
                                       pattern="[0-9]{10,13}"
                                       required>
                                <div class="invalid-feedback">
                                    Mohon isi nomor telepon yang valid (10-13 digit).
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label text-green-900">
                                    Email
                                </label>
                                <input type="email" 
                                       class="form-control-modern" 
                                       id="email" 
                                       name="email" 
                                       placeholder="nama@email.com">
                                <div class="invalid-feedback">
                                    Mohon isi email yang valid.
                                </div>
                            </div>
                            
                            <!-- Pesan -->
                            <div class="mb-4">
                                <label for="message" class="form-label text-green-900">
                                    Pesan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control-modern" 
                                          id="message" 
                                          name="message" 
                                          rows="4" 
                                          placeholder="Tulis pesan Anda di sini..."
                                          required></textarea>
                                <div class="invalid-feedback">
                                    Mohon isi pesan Anda.
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn-modern-green primary with-icon w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'views/footer.php'; ?>