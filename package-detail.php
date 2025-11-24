<?php
// package-detail.php - Halaman detail paket umroh berdasarkan ID dari URL
require_once 'config/database.php'; // Memuat file koneksi database PDO untuk interaksi dengan MySQL

// Ambil ID paket dari parameter URL (GET), pastikan integer untuk keamanan
$packageId = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Jika tidak ada ID, set 0

// Inisialisasi variabel untuk menyimpan data paket dan jadwal
$package = null; // Akan diisi jika paket ditemukan
$schedules = []; // Array untuk jadwal keberangkatan

// Jika ID valid (lebih dari 0), lakukan query untuk ambil data paket
if ($packageId > 0) {
    // Query untuk ambil detail paket berdasarkan ID, hanya yang aktif (is_active = 1)
    $queryPackage = "SELECT * FROM packages WHERE id = ? AND is_active = 1";
    $stmtPackage = $pdo->prepare($queryPackage); // Prepare statement untuk keamanan
    $stmtPackage->execute([$packageId]); // Eksekusi dengan parameter ID
    $package = $stmtPackage->fetch(); // Ambil satu baris data paket

    // Jika paket ditemukan, query jadwal keberangkatan yang terkait
    if ($package) {
        // Query jadwal: hanya yang available, tanggal keberangkatan >= hari ini, urutkan ascending
        $querySchedules = "SELECT * FROM schedules WHERE package_id = ? AND status = 'available' AND departure_date >= CURDATE() ORDER BY departure_date ASC";
        $stmtSchedules = $pdo->prepare($querySchedules);
        $stmtSchedules->execute([$packageId]);
        $schedules = $stmtSchedules->fetchAll(); // Ambil semua jadwal terkait
    }
}

// Jika paket tidak ditemukan, set pesan error
$errorMessage = (!$package) ? "Paket umroh tidak ditemukan atau tidak tersedia." : null;

// Ambil settings untuk tagline (opsional, untuk konsistensi dengan halaman lain)
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR); // Ambil sebagai array key-value
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH"; // Fallback jika tidak ada
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
?>
<?php include 'views/header.php'; // Include file header (navbar, meta tags, dll.) ?>

<!-- Hero Section Kecil - Menampilkan nama paket atau pesan error -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <?php if ($package): // Jika paket ada, tampilkan nama paket ?>
            <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($package['name']); // Escape output untuk keamanan ?></h1>
            <p class="lead"><?php echo htmlspecialchars($tagline2); ?> - Detail Paket Lengkap</p>
        <?php else: // Jika tidak ada, tampilkan pesan error ?>
            <h1 class="display-4 fw-bold">Paket Tidak Ditemukan</h1>
            <p class="lead">Kembali ke halaman paket untuk melihat pilihan lain.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Section Detail Paket - Hanya tampil jika paket ada -->
<?php if ($package): ?>
    <section id="package-detail" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <!-- Kolom Gambar Flyer - Besar dan full untuk detail -->
                <div class="col-lg-6 mb-4">
                    <!-- Gambar flyer paket, besar dan terlihat penuh tanpa crop -->
                    <img src="<?php echo htmlspecialchars($package['image']); ?>" class="img-fluid rounded shadow" alt="Flyer Paket <?php echo htmlspecialchars($package['name']); ?>" style="width: 100%; height: auto; max-height: 500px; object-fit: contain;">
                </div>
                <!-- Kolom Info Paket -->
                <div class="col-lg-6">
                    <h2 class="text-success fw-bold mb-3"><?php echo htmlspecialchars($package['name']); ?></h2>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($package['description']); ?></p>
                    <!-- Harga dan durasi -->
                    <p class="text-primary fw-bold fs-5 mb-2">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?> / <?php echo (int)$package['duration']; ?> hari</p>
                    <!-- Fasilitas sebagai list - Decode dari JSON atau comma-separated -->
                    <h5 class="text-success mb-2">Fasilitas Paket:</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <?php
                        // Decode fasilitas: jika JSON, parse; jika tidak, split comma
                        $facilities = json_decode($package['facilities'], true) ?? explode(',', $package['facilities']);
                        foreach ($facilities as $facility): // Loop untuk tampilkan setiap fasilitas ?>
                            <li class="list-group-item border-0 ps-0"><?php echo htmlspecialchars(trim($facility)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <!-- Tombol daftar dengan link ke register.php, kirim package_id -->
                    <a href="register.php?package_id=<?php echo (int)$package['id']; ?>" class="btn btn-success btn-lg rounded-pill px-4">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Jadwal Keberangkatan - Tampilkan jadwal terkait paket -->
    <section id="schedules" class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-4 text-success fw-bold">Jadwal Keberangkatan</h2>
            <?php if (!empty($schedules)): // Jika ada jadwal ?>
                <div class="row">
                    <?php foreach ($schedules as $schedule): // Loop jadwal ?>
                        <div class="col-md-6 mb-4">
                            <!-- Card jadwal dengan outline hijau -->
                            <div class="card border border-success rounded shadow-sm" style="border-width: 3px !important;">
                                <div class="card-body">
                                    <h5 class="card-title text-success fw-bold">Keberangkatan: <?php echo date('d M Y', strtotime($schedule['departure_date'])); ?></h5>
                                    <p class="card-text">Kembali: <?php echo date('d M Y', strtotime($schedule['return_date'])); ?></p>
                                    <p class="text-muted">Slot Tersedia: <?php echo (int)$schedule['available_slots']; ?></p>
                                    <!-- Tombol pilih jadwal, kirim package_id dan schedule_id -->
                                    <a href="register.php?package_id=<?php echo (int)$package['id']; ?>&schedule_id=<?php echo (int)$schedule['id']; ?>" class="btn btn-success rounded-pill">Pilih Jadwal</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: // Jika tidak ada jadwal ?>
                <div class="text-center">
                    <p class="text-muted">Belum ada jadwal keberangkatan tersedia untuk paket ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: // Jika paket tidak ada, tampilkan section error ?>
    <section id="error" class="py-5 bg-light text-center">
        <div class="container">
            <h2 class="text-danger fw-bold">Oops!</h2>
            <p class="text-muted"><?php echo htmlspecialchars($errorMessage); ?></p>
            <!-- Link kembali ke packages.php -->
            <a href="packages.php" class="btn btn-success rounded-pill px-4">Kembali ke Daftar Paket</a>
        </div>
    </section>
<?php endif; ?>

<!-- CTA Section - Call to action untuk hubungi -->
<section id="cta-detail" class="py-5 bg-warning text-dark text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">Siap Berangkat Umroh?</h2>
        <p class="mb-4">Daftar sekarang dan dapatkan pengalaman ibadah terbaik.</p>
        <a href="contact.php" class="btn btn-success btn-lg rounded-pill px-4">Hubungi Kami</a>
    </div>
</section>

<?php include 'views/footer.php'; // Include file footer (script, dll.) ?>
