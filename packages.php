<?php
// packages.php - TAMBAHKAN DEBUGGING
require_once 'config/database.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];

// DEBUG: Tampilkan semua paket tanpa filter
echo "<!-- DEBUG MODE: Checking packages -->";

$queryAll = "SELECT COUNT(*) as total FROM packages WHERE is_active = 1";
$stmtAll = $pdo->prepare($queryAll);
$stmtAll->execute();
$total = $stmtAll->fetch()['total'];
echo "<!-- Total active packages in DB: $total -->";

$queryPackages = "SELECT p.*, 
                         MIN(pp.price) as min_price,
                         MAX(pp.price) as max_price
                  FROM packages p 
                  LEFT JOIN package_prices pp ON p.id = pp.package_id AND pp.is_active = 1
                  WHERE p.is_active = 1";

if (!empty($search)) {
    $queryPackages .= " AND p.name LIKE ?";
    $params[] = '%' . $search . '%';
}

$queryPackages .= " GROUP BY p.id ORDER BY p.id DESC";

// DEBUG: Tampilkan query
echo "<!-- Query: $queryPackages -->";

$stmtPackages = $pdo->prepare($queryPackages);
$stmtPackages->execute($params);
$packages = $stmtPackages->fetchAll();

// DEBUG: Tampilkan hasil
echo "<!-- Packages found: " . count($packages) . " -->";
foreach($packages as $idx => $pkg) {
    echo "<!-- Package $idx: ID=" . $pkg['id'] . ", Name=" . $pkg['name'] . ", Active=" . $pkg['is_active'] . " -->";
}

$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
?>
<?php include 'views/header.php'; ?>

<!-- Hero Section Kecil - Style modern -->
<section class="py-5 bg-green-gradient text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold text-white">Paket Umroh Kami</h1>
        <p class="lead text-white opacity-90"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section Daftar Paket -->
<section id="packages-list" class="py-5 bg-green-50">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-green-900 mb-3">Paket Umroh Kami</h2>
            <p class="lead text-neutral-700 mb-0">Pilih paket yang sesuai dengan kebutuhan dan budget Anda</p>
        </div>
        
        <!-- Search Box Modern -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-6">
                <div class="card-modern-green-light">
                    <div class="card-body-modern">
                        <form method="GET" action="packages.php" class="d-flex">
                            <input type="text" name="search" 
                                   class="form-control-modern me-2" 
                                   placeholder="Cari paket umroh..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn-modern-green primary">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (empty($packages)): ?>
            <div class="text-center py-5">
                <div class="empty-state-modern">
                    <i class="fas fa-box-open text-green-300" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-green-800">Tidak ada paket yang ditemukan</h5>
                    <p class="text-neutral-700 mb-4">Coba kata kunci lain atau lihat semua paket kami</p>
                    <a href="packages.php" class="btn-modern-green primary with-icon">
                        <i class="fas fa-redo me-2"></i>Lihat Semua
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Grid Paket Modern -->
            <div class="row">
                <?php foreach ($packages as $package): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card-modern-green-light h-100">
                            <img src="<?php echo htmlspecialchars($package['image']); ?>" 
                                 class="card-img-modern" 
                                 alt="Paket <?php echo htmlspecialchars($package['name']); ?>"
                                 style="height: 300px; object-fit: contain;">
                            
                            <div class="card-body-modern d-flex flex-column">
                                <h5 class="card-title-modern text-green-900 mb-3"><?php echo htmlspecialchars($package['name']); ?></h5>
                                
                                <p class="card-text-modern text-neutral-700 mb-4 flex-grow-1">
                                    <?php echo htmlspecialchars(substr($package['description'], 0, 120)); ?>
                                    <?php if (strlen($package['description']) > 120): ?>
                                    <span class="text-green-700 fw-medium">...selengkapnya</span>
                                    <?php endif; ?>
                                </p>
                                
                                <!-- Price -->
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <p class="package-price-modern mb-1 text-green-900">
                                                <?php if (isset($package['min_price']) && isset($package['max_price'])): ?>
                                                    <?php if ($package['min_price'] != $package['max_price']): ?>
                                                        Rp <?php echo number_format($package['min_price'], 0, ',', '.'); ?> - 
                                                        Rp <?php echo number_format($package['max_price'], 0, ',', '.'); ?>
                                                    <?php else: ?>
                                                        Rp <?php echo number_format($package['min_price'], 0, ',', '.'); ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    Hubungi Kami
                                                <?php endif; ?>
                                            </p>
                                            <small class="text-neutral-600">
                                                <i class="fas fa-user me-1 text-green-600"></i>Per Orang
                                            </small>
                                        </div>
                                        <div class="text-warning">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                    </div>
                                    
                                    <a href="package-detail.php?id=<?php echo (int)$package['id']; ?>" 
                                    class="btn-modern-green primary w-100 with-icon">
                                        <i class="fas fa-eye me-2"></i>Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section - Style modern -->
<section id="cta-packages" class="py-5 bg-green-gradient">
    <div class="container text-center">
        <h2 class="text-white mb-3 fw-bold">Butuh Bantuan Memilih Paket?</h2>
        <p class="text-white opacity-90 mb-4">Konsultasikan kebutuhan umroh Anda dengan tim kami.</p>
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
            <a href="contact.php" class="btn-modern-green accent with-icon">
                <i class="fas fa-phone-alt me-2"></i>Hubungi Kami
            </a>
            <a href="index.php" class="btn-modern-green outline text-white border-white">
                <i class="fas fa-home me-2"></i>Kembali ke Home
            </a>
        </div>
    </div>
</section>

<?php include 'views/footer.php'; ?>