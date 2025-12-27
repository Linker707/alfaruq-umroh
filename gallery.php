<?php
// gallery.php - Hanya update style CSS
require_once 'config/database.php';

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

$queryGalleries = "SELECT * FROM galleries WHERE type = 'image' AND is_active = 1";
$params = [];
if ($filter !== 'all') {
    $queryGalleries .= " AND destination = ?";
    $params[] = $filter;
}
$queryGalleries .= " ORDER BY created_at DESC LIMIT 12";

$stmtGalleries = $pdo->prepare($queryGalleries);
$stmtGalleries->execute($params);
$galleries = $stmtGalleries->fetchAll();

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
        <h1 class="display-4 fw-bold text-white">Gallery Alfaruq Team</h1>
        <p class="lead text-white opacity-90"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section Pembuka -->
<section id="gallery-intro" class="py-5 bg-green-50">
    <div class="container text-center">
        <h2 class="text-green-900 fw-bold mb-4">Kenangan Perjalanan Ibadah Bersama Kami</h2>
        <p class="text-neutral-700 mb-0 mx-auto" style="max-width: 800px;">
            Jelajahi momen-momen indah dari perjalanan umroh dan wisata religi bersama ALFARUQ TEAM. 
            Dari kota suci Makkah dan Madinah hingga destinasi menarik di Thaif dan Turki, setiap gambar menceritakan kisah spiritual yang tak terlupakan.
        </p>
    </div>
</section>

<!-- Section Filter Button - Style modern -->
<section id="gallery-filter" class="py-4 bg-white">
    <div class="container text-center">
        <h5 class="text-green-900 fw-bold mb-4">Filter Berdasarkan Destinasi</h5>
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <?php
            $filters = [
                'all' => 'Semua',
                'makkah' => 'Makkah',
                'madinah' => 'Madinah',
                'thaif' => 'Thaif',
                'turki' => 'Turki'
            ];
            foreach ($filters as $key => $label): ?>
                <a href="gallery.php?filter=<?php echo urlencode($key); ?>" 
                   class="btn-modern-green <?php echo ($filter === $key) ? 'primary' : 'outline-green'; ?>">
                    <?php echo htmlspecialchars($label); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section Galeri Gambar - Style modern -->
<section id="gallery-grid" class="py-5 bg-green-50">
    <div class="container">
        <?php if (!empty($galleries)): ?>
            <div class="row g-4">
                <?php foreach ($galleries as $gallery): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="gallery-item-modern-light">
                            <img src="<?php echo htmlspecialchars($gallery['image']); ?>" 
                                 class="gallery-img-modern" 
                                 alt="<?php echo htmlspecialchars($gallery['title']); ?>"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#galleryModal"
                                 data-bs-image="<?php echo htmlspecialchars($gallery['image']); ?>"
                                 data-bs-title="<?php echo htmlspecialchars($gallery['title']); ?>"
                                 data-bs-description="<?php echo htmlspecialchars($gallery['description']); ?>">
                            
                            <div class="gallery-overlay-modern-light">
                                <div class="gallery-info-modern">
                                    <h6 class="text-white mb-1"><?php echo htmlspecialchars($gallery['title']); ?></h6>
                                    <small class="text-white opacity-75">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($gallery['destination'] ?? 'Umroh'); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state-modern">
                    <i class="fas fa-image text-green-300" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-green-800">Tidak ada gambar ditemukan</h5>
                    <p class="text-neutral-700 mb-4">Coba filter lain untuk melihat galeri kami</p>
                    <a href="gallery.php?filter=all" class="btn-modern-green primary with-icon">
                        <i class="fas fa-images me-2"></i>Lihat Semua
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section - Style modern -->
<section id="cta-gallery" class="py-5 bg-green-gradient">
    <div class="container text-center">
        <h2 class="text-white mb-3 fw-bold">Ingin Pengalaman Serupa?</h2>
        <p class="text-white opacity-90 mb-4">Bergabunglah dengan perjalanan ibadah kami dan buat kenangan tak terlupakan.</p>
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
            <a href="packages.php" class="btn-modern-green accent with-icon">
                <i class="fas fa-box-open me-2"></i>Lihat Paket
            </a>
            <a href="contact.php" class="btn-modern-green outline text-white border-white">
                <i class="fas fa-phone-alt me-2"></i>Hubungi Kami
            </a>
        </div>
    </div>
</section>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close bg-white rounded-circle p-2" 
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="" class="img-fluid rounded mb-3" id="modalGalleryImage" alt="">
                <h5 id="modalGalleryTitle" class="text-green-900"></h5>
                <p id="modalGalleryDescription" class="text-neutral-700 mb-0"></p>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#galleryModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var image = button.data('bs-image');
        var title = button.data('bs-title');
        var description = button.data('bs-description');
        
        var modal = $(this);
        modal.find('#modalGalleryImage').attr('src', image);
        modal.find('#modalGalleryTitle').text(title);
        modal.find('#modalGalleryDescription').text(description);
    });
});
</script>

<?php include 'views/footer.php'; ?>