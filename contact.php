<?php
// contact.php - Halaman kontak ALFARUQ TEAM, menampilkan lokasi kantor, nomor admin, email, form kontak, dan peta Google Maps
require_once 'config/database.php'; // Memuat file koneksi database PDO untuk interaksi dengan MySQL

// Perbaikan: Tambahkan contact_email ke query
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

// Ambil data
$officeAddress1 = $settings['office_address1'] ?? '';
$officeAddress2 = $settings['office_address2'] ?? '';
$branchAddresses = $settings['branch_addresses'] ?? '';
$adminPhone1 = $settings['admin_phone1'] ?? '';
$adminPhone2 = $settings['admin_phone2'] ?? '';

// Perbaikan: Ambil company_email ATAU contact_email
$companyEmail = $settings['company_email'] 
    ?? $settings['contact_email'] 
    ?? '';

if ($companyEmail) {
    $companyEmail = trim($companyEmail); // trim spasi agar mailto tidak rusak
}

$tagline1 = $settings['tagline1'] ?? '';
$tagline2 = $settings['tagline2'] ?? '';
$whatsapp = $settings['contact_phone'] ?? "+6281234567890";

// Proses form → Redirect ke WhatsApp
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $userMessage = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($phone) || empty($userMessage)) {
        $message = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email tidak valid!';
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

<!-- Hero Section -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Hubungi Kami</h1>
        <p class="lead"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section Lokasi Kantor -->
<section id="locations" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Lokasi Kantor & Cabang</h2>
        <div class="row">

            <!-- Kantor Pusat -->
            <div class="col-md-4 mb-4">
                <div class="card rounded shadow-sm h-100" style="border: 3px solid #33a661 !important;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Kantor Pusat</h5>
                        <p class="card-text"><?php echo htmlspecialchars($officeAddress1 ?: 'Alamat belum tersedia.'); ?></p>
                        <p class="text-muted">Jam Operasional:<br> Senin - Jumat, 08:30 - 17:00<br>Sabtu, 08:30 - 14:00</p>
                    </div>
                </div>
            </div>

            <!-- Kantor Cabang Utama -->
            <div class="col-md-4 mb-4">
                <div class="card rounded shadow-sm h-100" style="border: 3px solid #33a661 !important;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Kantor Cabang Utama</h5>
                        <p class="card-text"><?php echo htmlspecialchars($officeAddress2 ?: 'Alamat belum tersedia.'); ?></p>
                        <p class="text-muted">Jam Operasional:<br> Senin - Jumat, 08:30 - 17:00<br>Sabtu, 08:30 - 14:00</p>
                    </div>
                </div>
            </div>

            <!-- Cabang Lainnya -->
            <div class="col-md-4 mb-4">
                <div class="card rounded shadow-sm h-100" style="border: 3px solid #33a661 !important;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Cabang Lainnya</h5>
                        <?php if ($branchAddresses): ?>
                            <ul class="list-unstyled">
                                <?php foreach (explode(';', $branchAddresses) as $branch): ?>
                                    <li><?php echo htmlspecialchars(trim($branch)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Cabang belum tersedia.</p>
                        <?php endif; ?>
                        <p class="text-muted">Jam Operasional bervariasi sesuai lokasi</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Section Informasi Kontak -->
<section id="contact-info" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Informasi Kontak</h2>
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="card rounded shadow-lg" style="border: 3px solid #33a661 !important;">
                    <div class="card-body p-4">
                        <div class="row g-4">

                            <!-- Admin 1 -->
                            <div class="col-md-4 d-flex">
                                <div class="card border-0 shadow-sm flex-fill">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-user-circle fa-2x text-success mb-3"></i>
                                        <h6 class="text-success fw-bold mb-2">Admin 1</h6>
                                        <p class="mb-3"><?php echo htmlspecialchars($adminPhone1 ?: 'Nomor belum tersedia.'); ?></p>
                                        <?php if ($adminPhone1): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $adminPhone1); ?>?text=Halo%20Admin%201,%20saya%20ingin%20konsultasi" 
                                               class="btn btn-success rounded-pill btn-sm px-3" target="_blank">Chat WhatsApp</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin 2 -->
                            <div class="col-md-4 d-flex">
                                <div class="card border-0 shadow-sm flex-fill">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-user-circle fa-2x text-success mb-3"></i>
                                        <h6 class="text-success fw-bold mb-2">Admin 2</h6>
                                        <p class="mb-3"><?php echo htmlspecialchars($adminPhone2 ?: 'Nomor belum tersedia.'); ?></p>
                                        <?php if ($adminPhone2): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $adminPhone2); ?>?text=Halo%20Admin%202,%20saya%20ingin%20konsultasi" 
                                               class="btn btn-success rounded-pill btn-sm px-3" target="_blank">Chat WhatsApp</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-4 d-flex">
                                <div class="card border-0 shadow-sm flex-fill">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-envelope fa-2x text-success mb-3"></i>
                                        <h6 class="text-success fw-bold mb-2">Email</h6>

                                        <p class="mb-3">
                                            <?php echo $companyEmail ? htmlspecialchars($companyEmail) : 'Email belum tersedia.'; ?>
                                        </p>

                                        <?php if (!empty($companyEmail)): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($companyEmail); ?>" 
                                               class="btn btn-success rounded-pill btn-sm px-3">
                                                Kirim Email
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        </div> 
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Form Kontak -->
<section id="contact-form" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Kirim Pesan Kepada Kami</h2>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'berhasil') !== false ? 'alert-success' : 'alert-danger'; ?> text-center rounded-pill">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <form method="POST" action="contact.php" class="card rounded shadow-sm border-0 p-4" style="background-color: #ffffff;">
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold text-success">Nama Lengkap</label>
                        <input type="text" class="form-control rounded-pill" id="name" name="name" style="border: 2px solid #33a661;" required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold text-success">Email</label>
                        <input type="email" class="form-control rounded-pill" id="email" name="email" style="border: 2px solid #33a661;" required>
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="form-label fw-bold text-success">Nomor Telepon</label>
                        <input type="tel" class="form-control rounded-pill" id="phone" name="phone" style="border: 2px solid #33a661;" required>
                    </div>
                    <div class="mb-4">
                        <label for="message" class="form-label fw-bold text-success">Pesan</label>
                        <textarea class="form-control rounded" id="message" name="message" rows="4" style="border: 2px solid #33a661;" required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5">Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</section>

<!-- Google Maps -->
<section id="map" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Lokasi Kantor Pusat</h2>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.302449513387!2d104.50586837411787!3d0.9213745990697199!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d96d006102a8b1%3A0x45e88564b0449e1c!2sPT.%20ALFARUQ%20ANUGERAH%20UTAMA%20TRAVEL!5e0!3m2!1sid!2sid!4v1763970608243!5m2!1sid!2sid"
                    width="100%" height="400" style="border:0; border-radius: 0.75rem;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>

    </div>
</section>

<?php include 'views/footer.php'; ?>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
