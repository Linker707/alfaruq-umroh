<?php
// contact.php - Halaman kontak ALFARUQ TEAM, menampilkan lokasi kantor, nomor admin, email, form kontak, dan peta Google Maps
require_once 'config/database.php'; // Memuat file koneksi database PDO untuk interaksi dengan MySQL

// Ambil semua data settings untuk kontak (lokasi, nomor, email, tagline) - tanpa fallback hardcoded
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('office_address1', 'office_address2', 'branch_addresses', 'admin_phone1', 'admin_phone2', 'company_email', 'tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings); // Prepare statement untuk keamanan
$stmtSettings->execute(); // Eksekusi query
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR); // Ambil sebagai array key-value

// Ambil data dari DB - jika key tidak ada, set kosong (tidak ada fallback hardcoded)
$officeAddress1 = $settings['office_address1'] ?? ''; // Office pusat - kosong jika tidak ada
$officeAddress2 = $settings['office_address2'] ?? ''; // Lokasi kedua - kosong jika tidak ada
$branchAddresses = $settings['branch_addresses'] ?? ''; // Cabang - kosong jika tidak ada
$adminPhone1 = $settings['admin_phone1'] ?? ''; // Nomor admin 1 - kosong jika tidak ada
$adminPhone2 = $settings['admin_phone2'] ?? ''; // Nomor admin 2 - kosong jika tidak ada
$companyEmail = $settings['company_email'] ?? ''; // Email - kosong jika tidak ada
$tagline1 = $settings['tagline1'] ?? ''; // Tagline 1 - kosong jika tidak ada
$tagline2 = $settings['tagline2'] ?? ''; // Tagline 2 - kosong jika tidak ada

// Proses form jika disubmit (POST) - simpan ke database tabel contacts
$message = ''; // Variabel untuk pesan sukses/error
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $userMessage = trim($_POST['message'] ?? '');

    // Validasi sederhana
    if (empty($name) || empty($email) || empty($phone) || empty($userMessage)) {
        $message = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email tidak valid!';
    } else {
        // Insert ke tabel contacts
        $queryInsert = "INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($queryInsert);
        if ($stmtInsert->execute([$name, $email, $phone, $userMessage])) {
            $message = 'Pesan berhasil dikirim! Kami akan segera menghubungi Anda.';
        } else {
            $message = 'Gagal mengirim pesan. Coba lagi nanti.';
        }
    }
}
?>
<?php include 'views/header.php'; // Include file header (navbar, meta tags, dll.) ?>

<!-- Hero Section Kecil - Menggunakan tagline dari DB -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Hubungi Kami</h1>
        <p class="lead"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section Lokasi Kantor - Cards dengan outline hijau, data dari DB -->
<section id="locations" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Lokasi Kantor & Cabang</h2>
        <div class="row">
            <!-- Card Kantor Pusat - Outline hijau, data dari DB -->
            <div class="col-md-4 mb-4">
                <div class="card rounded shadow-sm h-100" style="border: 3px solid #33a661 !important; background-color: #ffffff;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Kantor Pusat</h5>
                        <p class="card-text"><?php echo htmlspecialchars($officeAddress1 ?: 'Alamat belum tersedia.'); ?></p>
                        <p class="text-muted">Jam Operasional: Senin - Jumat, 08:00 - 17:00 WIB</p>
                    </div>
                </div>
            </div>
            <!-- Card Kantor Cabang Utama - Outline hijau, data dari DB -->
            <div class="col-md-4 mb-4">
                <div class="card rounded shadow-sm h-100" style="border: 3px solid #33a661 !important; background-color: #ffffff;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Kantor Cabang Utama</h5>
                        <p class="card-text"><?php echo htmlspecialchars($officeAddress2 ?: 'Alamat belum tersedia.'); ?></p>
                        <p class="text-muted">Jam Operasional: Senin - Jumat, 08:00 - 17:00 WIB</p>
                    </div>
                </div>
            </div>
            <!-- Card Cabang Lainnya - Outline hijau, satukan dalam satu card, data dari DB -->
            <div class="col-md-4 mb-4">
                <div class="card rounded shadow-sm h-100" style="border: 3px solid #33a661 !important; background-color: #ffffff;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Cabang Lainnya</h5>
                        <?php if ($branchAddresses): ?>
                            <ul class="list-unstyled">
                                <?php
                                // Split branch addresses berdasarkan semicolon dan tampilkan sebagai list
                                $branches = explode(';', $branchAddresses);
                                foreach ($branches as $branch): ?>
                                    <li><?php echo htmlspecialchars(trim($branch)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Cabang belum tersedia.</p>
                        <?php endif; ?>
                        <p class="text-muted">Jam Operasional: Sesuai lokasi masing-masing</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Informasi Kontak - Card dengan style lebih menarik, kontras, dan grid rapi -->
<section id="contact-info" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Informasi Kontak</h2>
        <div class="row justify-content-center">
            <div class="col-lg-10"> <!-- Sesuaikan lebar card untuk grid rapi -->
                <div class="card rounded shadow-lg" style="border: 3px solid #33a661 !important; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);"> <!-- Gradient background untuk kontras dan menarik -->
                    <div class="card-body p-4"> <!-- Tambah padding untuk clean -->
                        <div class="row g-4"> <!-- Gunakan g-4 untuk gap rapi antar kolom -->
                            <!-- Card Admin 1 - Style individual untuk menarik -->
                            <div class="col-md-4 d-flex">
                                <div class="card border-0 shadow-sm flex-fill" style="background-color: #ffffff; transition: transform 0.3s ease;"> <!-- Shadow dan transition untuk efek hover -->
                                    <div class="card-body text-center p-3">
                                        <div class="mb-3">
                                            <i class="fas fa-user-circle fa-2x text-success"></i> <!-- Icon untuk menarik -->
                                        </div>
                                        <h6 class="text-success fw-bold mb-2">Admin 1</h6>
                                        <p class="mb-3"><?php echo htmlspecialchars($adminPhone1 ?: 'Nomor belum tersedia.'); ?></p>
                                        <?php if ($adminPhone1): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $adminPhone1); ?>?text=Halo%20Admin%201,%20saya%20ingin%20konsultasi" class="btn btn-success rounded-pill btn-sm px-3" target="_blank">Chat WhatsApp</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Card Admin 2 - Style individual untuk menarik -->
                            <div class="col-md-4 d-flex">
                                <div class="card border-0 shadow-sm flex-fill" style="background-color: #ffffff; transition: transform 0.3s ease;"> <!-- Shadow dan transition untuk efek hover -->
                                    <div class="card-body text-center p-3">
                                        <div class="mb-3">
                                            <i class="fas fa-user-circle fa-2x text-success"></i> <!-- Icon untuk menarik -->
                                        </div>
                                        <h6 class="text-success fw-bold mb-2">Admin 2</h6>
                                        <p class="mb-3"><?php echo htmlspecialchars($adminPhone2 ?: 'Nomor belum tersedia.'); ?></p>
                                        <?php if ($adminPhone2): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $adminPhone2); ?>?text=Halo%20Admin%202,%20saya%20ingin%20konsultasi" class="btn btn-success rounded-pill btn-sm px-3" target="_blank">Chat WhatsApp</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Card Email - Style individual untuk menarik -->
                            <div class="col-md-4 d-flex">
                                <div class="card border-0 shadow-sm flex-fill" style="background-color: #ffffff; transition: transform 0.3s ease;"> <!-- Shadow dan transition untuk efek hover -->
                                    <div class="card-body text-center p-3">
                                        <div class="mb-3">
                                            <i class="fas fa-envelope fa-2x text-success"></i> <!-- Icon untuk menarik -->
                                        </div>
                                        <h6 class="text-success fw-bold mb-2">Email</h6>
                                        <p class="mb-3"><?php echo htmlspecialchars($companyEmail ?: 'Email belum tersedia.'); ?></p>
                                        <?php if ($companyEmail): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($companyEmail); ?>" class="btn btn-success rounded-pill btn-sm px-3">Kirim Email</a>
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

<!-- Section Form Kontak - Formulir data diri dengan outline hijau pada input, simpan ke database -->
<section id="contact-form" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Kirim Pesan Kepada Kami</h2>
        <?php if ($message): // Tampilkan pesan sukses/error jika ada ?>
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

<!-- Section Peta Google Maps - Untuk office pusat -->
<section id="map" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Lokasi Kantor Pusat</h2>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Embed Google Maps untuk alamat office pusat (ambil dari DB jika ada koordinat, tapi untuk sekarang placeholder) -->
                <!-- Jika ingin dinamis, tambah key 'office_lat' dan 'office_lng' di settings untuk koordinat -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.302449513387!2d104.50586837411787!3d0.9213745990697199!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d96d006102a8b1%3A0x45e88564b0449e1c!2sPT.%20ALFARUQ%20ANUGERAH%20UTAMA%20TRAVEL!5e0!3m2!1sid!2sid!4v1763970608243!5m2!1sid!2sid" width="100%" height="400" style="border:0; border-radius: 0.75rem;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                <!-- Catatan: Koordinat placeholder untuk Tanjung Pinang. Jika ingin dinamis, tambah kolom lat/lng di settings dan update embed URL -->
            </div>
        </div>
    </div>
</section>

<?php include 'views/footer.php'; // Include file footer (script, dll.) ?>

<!-- Tambah FontAwesome untuk icon (jika belum ada di header) -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- Untuk icon user dan envelope -->
