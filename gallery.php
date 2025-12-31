<?php
require_once 'config/database.php';
require_once 'config/api-local.php'; // Gunakan API lokal

// Inisialisasi API
$api = new LocalAPI();

// Coba ambil dari API, jika gagal ambil dari database
try {
    $galleries = $api->getGalleries(12);
    
    // Debug: Tampilkan jumlah gallery dari API
    // echo "<!-- Gallery dari API: " . count($galleries) . " items -->";
    
} catch (Exception $e) {
    // Jika API error, fallback ke database
    $queryGalleries = "SELECT * FROM galleries WHERE is_active = 1 ORDER BY created_at DESC LIMIT 12";
    $stmtGalleries = $pdo->prepare($queryGalleries);
    $stmtGalleries->execute();
    $galleries = $stmtGalleries->fetchAll(PDO::FETCH_ASSOC);
}

// Filter jika ada
$filter = $_GET['filter'] ?? 'all';
if ($filter !== 'all') {
    $galleries = array_filter($galleries, function($gallery) use ($filter) {
        return ($gallery['destination'] ?? '') === $filter;
    });
}

// Ambil destinasi untuk filter
$queryDestinations = "SELECT * FROM destinations WHERE is_active = 1";
$stmtDestinations = $pdo->prepare($queryDestinations);
$stmtDestinations->execute();
$destinations = $stmtDestinations->fetchAll();
?>

<?php include 'views/header.php'; ?>

<!-- ... hero section ... -->

<section id="gallery-grid" class="py-5 bg-green-50">
    <div class="container">
        <div class="mb-4">
            <small class="text-muted">
                <?php 
                echo "Menampilkan " . count($galleries) . " gallery";
                if ($filter !== 'all') {
                    echo " (filter: $filter)";
                }
                ?>
            </small>
        </div>
        
        <?php if (!empty($galleries)): ?>
            <div class="row g-4">
                <?php foreach ($galleries as $gallery): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="gallery-item-modern-light">
                            <!-- Gambar dari API -->
                            <img src="<?php echo htmlspecialchars($gallery['image'] ?? 'assets/img/default.jpg'); ?>" 
                                 class="gallery-img-modern" 
                                 alt="<?php echo htmlspecialchars($gallery['title'] ?? ''); ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='assets/img/default.jpg';">
                            
                            <div class="gallery-overlay-modern-light">
                                <div class="gallery-info-modern">
                                    <h6 class="text-white mb-1">
                                        <?php echo htmlspecialchars($gallery['title'] ?? 'Gallery'); ?>
                                    </h6>
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
                    <h5 class="mt-3 text-green-800">Belum ada gallery</h5>
                    <p class="text-neutral-700 mb-4">Gallery akan muncul setelah diupload via admin panel</p>
                    
                    <!-- Test Upload dari Gallery -->
                    <div class="mt-4">
                        <h6 class="text-green-700 mb-3">Test Upload API Lokal:</h6>
                        <form id="testUploadForm" enctype="multipart/form-data" class="mb-3">
                            <input type="file" name="image" accept="image/*" class="form-control mb-2">
                            <button type="submit" class="btn-modern-green primary sm">
                                <i class="fas fa-upload me-2"></i>Test Upload
                            </button>
                        </form>
                        <div id="uploadResult"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Script untuk test upload langsung dari gallery
document.getElementById('testUploadForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('http://localhost/alfaroq-admin-api/api/upload', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        const resultDiv = document.getElementById('uploadResult');
        
        if (result.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>Success!</strong> Gambar berhasil diupload.<br>
                    URL: <a href="${result.url}" target="_blank">${result.url}</a><br>
                    <img src="${result.url}" style="max-width: 200px; margin-top: 10px;">
                </div>
            `;
            
            // Auto refresh setelah 3 detik
            setTimeout(() => location.reload(), 3000);
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Error:</strong> ${result.error}
                </div>
            `;
        }
    } catch (error) {
        document.getElementById('uploadResult').innerHTML = `
            <div class="alert alert-danger">
                <strong>Network Error:</strong> ${error.message}
            </div>
        `;
    }
});
</script>

<?php include 'views/footer.php'; ?>