<?php
require_once 'config/database.php';

$packageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$package = null;
$schedules = [];

if ($packageId > 0) {
    $queryPackage = "SELECT * FROM packages WHERE id = ? AND is_active = 1";
    $stmtPackage = $pdo->prepare($queryPackage);
    $stmtPackage->execute([$packageId]);
    $package = $stmtPackage->fetch();

    if ($package) {
        $querySchedules = "SELECT * FROM schedules 
                           WHERE package_id = ? 
                           AND status = 'available' 
                           AND departure_date >= CURDATE()
                           ORDER BY departure_date ASC";
        $stmtSchedules = $pdo->prepare($querySchedules);
        $stmtSchedules->execute([$packageId]);
        $schedules = $stmtSchedules->fetchAll();
    }
}

$errorMessage = (!$package) ? "Paket umroh tidak ditemukan atau tidak tersedia." : null;

$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);

$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";

include 'views/header.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <?php if ($package): ?>
            <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($package['name']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($tagline2); ?> - Detail Paket Lengkap</p>
        <?php else: ?>
            <h1 class="display-4 fw-bold">Paket Tidak Ditemukan</h1>
            <p class="lead">Kembali ke halaman paket untuk melihat pilihan lain.</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($package): ?>
    <!-- Detail Paket -->
    <section id="package-detail" class="py-5 bg-light">
        <div class="container">
            <div class="row">

                <!-- Gambar -->
                <div class="col-lg-6 mb-4">
                    <img src="<?php echo htmlspecialchars($package['image']); ?>"
                         class="img-fluid rounded shadow"
                         alt="Flyer Paket <?php echo htmlspecialchars($package['name']); ?>"
                         style="width: 100%; height: auto; max-height: 500px; object-fit: contain;">
                </div>

                <!-- Informasi -->
                <div class="col-lg-6">
                    <h2 class="text-success fw-bold mb-3"><?php echo htmlspecialchars($package['name']); ?></h2>

                    <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>

                    <p class="text-primary fw-bold fs-5 mb-3">
                        Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                        / <?php echo (int)$package['duration']; ?> hari
                    </p>

                    <!-- Fasilitas -->
                    <h5 class="text-success mb-2">Fasilitas Paket:</h5>
                    <ul class="package-facilities mb-4">
                        <?php
                        // JSON atau newline
                        $jsonCheck = json_decode($package['facilities'], true);
                        $facilities = (json_last_error() === JSON_ERROR_NONE && is_array($jsonCheck))
                                      ? $jsonCheck
                                      : preg_split('/\r\n|\r|\n/', $package['facilities']);

                        foreach ($facilities as $facility):
                            $facility = trim($facility);
                            if ($facility === '') continue;
                        ?>
                            <li><?php echo htmlspecialchars($facility); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="register.php?package_id=<?php echo (int)$package['id']; ?>"
                       class="btn btn-success btn-lg rounded-pill px-4">
                        Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Jadwal -->
    <section id="schedules" class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-4 text-success fw-bold">Jadwal Keberangkatan</h2>

            <?php if (!empty($schedules)): ?>
                <div class="row">
                    <?php foreach ($schedules as $schedule): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card border border-success rounded shadow-sm" style="border-width: 3px !important;">
                                <div class="card-body">
                                    <h5 class="card-title text-success fw-bold">
                                        Keberangkatan: <?php echo date('d M Y', strtotime($schedule['departure_date'])); ?>
                                    </h5>
                                    <p class="card-text">Kembali: <?php echo date('d M Y', strtotime($schedule['return_date'])); ?></p>
                                    <p class="text-muted">
                                        Slot Tersedia: <?php echo (int)$schedule['available_slots']; ?>
                                    </p>

                                    <a href="register.php?package_id=<?php echo (int)$package['id']; ?>&schedule_id=<?php echo (int)$schedule['id']; ?>"
                                       class="btn btn-success rounded-pill">
                                        Pilih Jadwal
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="text-center">
                    <p class="text-muted">Belum ada jadwal keberangkatan tersedia untuk paket ini.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

<?php else: ?>
    <section class="py-5 bg-light text-center">
        <div class="container">
            <h2 class="text-danger fw-bold">Oops!</h2>
            <p class="text-muted"><?php echo htmlspecialchars($errorMessage); ?></p>
            <a href="packages.php" class="btn btn-success rounded-pill px-4">Kembali ke Daftar Paket</a>
        </div>
    </section>
<?php endif; ?>

<!-- CTA -->
<section id="cta-detail" class="py-5 bg-warning text-dark text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">Siap Berangkat Umroh?</h2>
        <p class="mb-4">Daftar sekarang dan dapatkan pengalaman ibadah terbaik.</p>
        <a href="contact.php" class="btn btn-success btn-lg rounded-pill px-4">Hubungi Kami</a>
    </div>
</section>

<?php include 'views/footer.php'; ?>
