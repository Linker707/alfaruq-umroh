<?php
require_once 'config/database.php';

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

// 1. AMBIL DESTINASI DARI DATABASE
$queryDestinations = "SELECT d.*, 
                             (SELECT COUNT(*) FROM galleries g 
                              WHERE g.destination_id = d.id OR g.destination = d.slug) as gallery_count
                      FROM destinations d 
                      WHERE d.is_active = 1 
                      ORDER BY d.name ASC";
$stmtDestinations = $pdo->prepare($queryDestinations);
$stmtDestinations->execute();
$destinations = $stmtDestinations->fetchAll();

// 2. QUERY GALLERY DENGAN FILTER DINAMIS
$queryGalleries = "SELECT g.*, d.name as destination_name, d.slug as destination_slug
                   FROM galleries g 
                   LEFT JOIN destinations d ON (g.destination_id = d.id OR g.destination = d.slug)
                   WHERE g.type = 'image' AND g.is_active = 1";

$params = [];
if ($filter !== 'all') {
    // Cari destinasi berdasarkan slug
    $queryGalleries .= " AND (d.slug = ? OR g.destination = ?)";
    $params[] = $filter;
    $params[] = $filter;
}

$queryGalleries .= " ORDER BY g.created_at DESC LIMIT 12";

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

<!-- ... Hero Section ... -->

<!-- Section Filter Button - DYNAMIC -->
<section id="gallery-filter" class="py-4 bg-white">
    <div class="container text-center">
        <h5 class="text-green-900 fw-bold mb-4">Filter Berdasarkan Destinasi</h5>
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <!-- Semua -->
            <a href="gallery.php?filter=all" 
               class="btn-modern-green <?php echo ($filter === 'all') ? 'primary' : 'outline-green'; ?>">
                Semua
            </a>
            
            <!-- Dinamis dari database -->
            <?php foreach ($destinations as $dest): 
                if ($dest['gallery_count'] > 0): ?>
                    <a href="gallery.php?filter=<?php echo urlencode($dest['slug']); ?>" 
                       class="btn-modern-green <?php echo ($filter === $dest['slug']) ? 'primary' : 'outline-green'; ?>">
                        <?php echo htmlspecialchars($dest['name']); ?>
                        <span class="badge bg-green-100 text-green-800 ms-1">
                            <?php echo $dest['gallery_count']; ?>
                        </span>
                    </a>
                <?php endif; 
            endforeach; ?>
        </div>
    </div>
</section>

<!-- Section Galeri Gambar -->
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
                                        <?php echo htmlspecialchars($gallery['destination_name'] ?? $gallery['destination'] ?? 'Umroh'); ?>
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