<?php
// packages.php - Halaman daftar paket umroh
require_once 'config/database.php'; // Koneksi database

// Ambil parameter search dari URL (GET)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query untuk paket aktif, dengan filter search jika ada
$queryPackages = "SELECT * FROM packages WHERE is_active = 1";
$params = [];
if (!empty($search)) {
    $queryPackages .= " AND name LIKE ?";
    $params[] = '%' . $search . '%';
}
$queryPackages .= " ORDER BY id DESC"; // Urutkan berdasarkan ID terbaru

$stmtPackages = $pdo->prepare($queryPackages);
$stmtPackages->execute($params);
$packages = $stmtPackages->fetchAll();

// Ambil settings untuk tagline jika perlu (opsional)
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
?>
<?php include 'views/header.php'; // Include header dengan navbar ?>

<!-- Hero Section Kecil untuk Packages -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($tagline1); ?></h1>
        <p class="lead"><?php echo htmlspecialchars($tagline2); ?> - Temukan Paket Umroh Terbaik Kami</p>
    </div>
</section>

<!-- Section Daftar Paket -->
<section id="packages-list" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Paket Umroh Kami</h2>
        
        <!-- Search Box -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <form method="GET" action="packages.php" class="d-flex">
                    <input type="text" name="search" class="form-control rounded-pill me-2" placeholder="Cari paket umroh..." value="<?php echo htmlspecialchars($search); ?>" style="border: 2px solid #33a661;">
                    <button type="submit" class="btn btn-success rounded-pill px-4">Cari</button>
                </form>
            </div>
        </div>
        
        <!-- Pesan Jika Tidak Ada Paket -->
        <?php if (empty($packages)): ?>
            <div class="text-center">
                <p class="text-muted">Tidak ada paket yang ditemukan. Coba kata kunci lain.</p>
            </div>
        <?php else: ?>
            <!-- Grid Paket -->
            <div class="row">
                <?php foreach ($packages as $package): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <!-- Card dengan outline hijau dan ukuran mengikuti flyer -->
                        <div class="card border border-success rounded shadow-sm h-auto" style="border-width: 3px !important;"> <!-- Outline hijau tebal -->
                            <!-- Gambar flyer full dan besar, card adjust ukuran -->
                            <img src="<?php echo htmlspecialchars($package['image']); ?>" class="card-img-top" alt="Paket <?php echo htmlspecialchars($package['name']); ?>" style="height: auto; max-height: 400px; object-fit: contain; width: 100%;"> <!-- Flyer terlihat full, besar, dan detail -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-success fw-bold"><?php echo htmlspecialchars($package['name']); ?></h5>
                                <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($package['description'], 0, 120)) . (strlen($package['description']) > 120 ? '...' : ''); ?></p>
                                <p class="text-primary fw-bold mb-2">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?> / <?php echo (int)$package['duration']; ?> hari</p>
                                <a href="package-detail.php?id=<?php echo (int)$package['id']; ?>" class="btn btn-success rounded-pill mt-auto">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section id="cta-packages" class="py-5 bg-warning text-dark text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">Butuh Bantuan Memilih Paket?</h2>
        <p class="mb-4">Konsultasikan kebutuhan umroh Anda dengan tim kami.</p>
        <a href="contact.php" class="btn btn-success btn-lg rounded-pill px-4">Hubungi Kami</a>
    </div>
</section>

<?php include 'views/footer.php'; // Include footer ?>
