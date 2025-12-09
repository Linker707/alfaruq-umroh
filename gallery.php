<?php
// gallery.php - Halaman galeri ALFARUQ TEAM, menampilkan gambar perjalanan dengan filter destinasi
require_once 'config/database.php'; // Memuat file koneksi database PDO untuk interaksi dengan MySQL

// Ambil filter dari URL (GET) - default 'all'
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

// Query galeri: hanya image aktif, urutkan berdasarkan created_at DESC, dengan filter destinasi jika bukan 'all'
$queryGalleries = "SELECT * FROM galleries WHERE type = 'image' AND is_active = 1";
$params = [];
if ($filter !== 'all') {
    $queryGalleries .= " AND destination = ?";
    $params[] = $filter;
}
$queryGalleries .= " ORDER BY created_at DESC LIMIT 12"; // Limit 12 untuk performa, bisa tambah pagination nanti

$stmtGalleries = $pdo->prepare($queryGalleries);
$stmtGalleries->execute($params);
$galleries = $stmtGalleries->fetchAll();

// Ambil settings untuk tagline (opsional)
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
?>
<?php include 'views/header.php'; // Include file header (navbar, meta tags, dll.) ?>

<!-- Hero Section Kecil - Dengan judul dan tagline -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Gallery Alfaruq Team</h1>
        <p class="lead"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section Pembuka - Teks singkat tentang galeri -->
<section id="gallery-intro" class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="text-success fw-bold mb-4">Kenangan Perjalanan Ibadah Bersama Kami</h2>
        <p class="text-muted mb-0">Jelajahi momen-momen indah dari perjalanan umroh dan wisata religi bersama ALFARUQ TEAM. Dari kota suci Makkah dan Madinah hingga destinasi menarik di Thaif dan Turki, setiap gambar menceritakan kisah spiritual yang tak terlupakan.</p>
    </div>
</section>

<!-- Section Filter Button - Button untuk filter destinasi -->
<section id="gallery-filter" class="py-4 bg-white">
    <div class="container text-center">
        <h5 class="text-success fw-bold mb-4">Filter Berdasarkan Destinasi</h5>
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <?php
            // Array filter destinasi
            $filters = [
                'all' => 'Semua',
                'makkah' => 'Makkah',
                'madinah' => 'Madinah',
                'thaif' => 'Thaif',
                'turki' => 'Turki'
            ];
            foreach ($filters as $key => $label): ?>
                <a href="gallery.php?filter=<?php echo urlencode($key); ?>" class="btn <?php echo ($filter === $key) ? 'btn-success' : 'btn-outline-success'; ?> rounded-pill px-4 py-2 fw-bold" style="transition: all 0.3s ease;">
                    <?php echo htmlspecialchars($label); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section Galeri Gambar - Grid responsif dengan efek hover -->
<section id="gallery-grid" class="py-5 bg-light">
    <div class="container">
        <?php if (!empty($galleries)): ?>
            <div class="row g-4"> <!-- Gap 4 untuk spacing rapi -->
                <?php foreach ($galleries as $gallery): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6"> <!-- Grid responsif: 4 di lg, 3 di md, 2 di sm -->
                        <div class="card border-0 shadow-sm rounded overflow-hidden position-relative" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <!-- Gambar dengan efek hover zoom -->
                            <img src="<?php echo htmlspecialchars($gallery['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($gallery['title']); ?>" style="height: 250px; object-fit: cover; transition: transform 0.3s ease;">
                            <!-- Overlay teks saat hover -->
                            <div class="card-img-overlay d-flex align-items-end p-3" style="background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%); opacity: 0; transition: opacity 0.3s ease;">
                                <div class="text-white">
                                    <h6 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($gallery['title']); ?></h6>
                                    <p class="card-text small mb-0"><?php echo htmlspecialchars($gallery['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p class="text-muted">Tidak ada gambar ditemukan untuk filter ini. Coba filter lain.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section - Call to action untuk lihat paket atau hubungi -->
<section id="cta-gallery" class="py-5 bg-warning text-dark text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">Ingin Pengalaman Serupa?</h2>
        <p class="mb-4">Bergabunglah dengan perjalanan ibadah kami dan buat kenangan tak terlupakan.</p>
        <a href="packages.php" class="btn btn-success btn-lg rounded-pill px-4 me-2">Lihat Paket</a>
        <a href="contact.php" class="btn btn-outline-success btn-lg rounded-pill px-4">Hubungi Kami</a>
    </div>
</section>

<?php include 'views/footer.php'; // Include file footer (script, dll.) ?>