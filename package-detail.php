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
        // Ambil semua jadwal dari tabel schedules
        $querySchedules = "SELECT * FROM schedules ORDER BY departure_date ASC";
        $stmtSchedules = $pdo->prepare($querySchedules);
        $stmtSchedules->execute();
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
?>
<?php include 'views/header.php'; ?>

<!-- Hero Section - Style modern -->
<section class="py-5 bg-green-gradient text-white text-center">
    <div class="container">
        <?php if ($package): ?>
            <h1 class="display-4 fw-bold text-white"><?php echo htmlspecialchars($package['name']); ?></h1>
            <p class="lead text-white opacity-90"><?php echo htmlspecialchars($tagline2); ?> - Detail Paket Lengkap</p>
        <?php else: ?>
            <h1 class="display-4 fw-bold text-white">Paket Tidak Ditemukan</h1>
            <p class="lead text-white opacity-90">Kembali ke halaman paket untuk melihat pilihan lain.</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($package): ?>
        <!-- Gambar Flyer - FULL CARD -->
<!-- Detail Paket - Style modern -->
<section id="package-detail" class="py-5 bg-green-50">
    <div class="container">
        <div class="row g-4">
            <!-- Gambar Flyer - SISI KANAN -->
            <div class="col-lg-6">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-flyer">
                        <div class="flyer-wrapper">
                            <img src="<?php echo htmlspecialchars($package['image']); ?>"
                                 class="flyer-image"
                                 alt="Flyer Paket <?php echo htmlspecialchars($package['name']); ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YwZjBmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjOTk5Ij5HdW1iYXIgVGlkYWsgQmlzYSBEaW11YWF0PC90ZXh0Pjwvc3ZnPg==';">
                            <div class="flyer-overlay">
                                <a href="<?php echo htmlspecialchars($package['image']); ?>" 
                                   class="flyer-zoom-btn" 
                                   target="_blank"
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="bottom" 
                                   title="Buka gambar di tab baru">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer-flyer text-center py-3 border-top">
                        <small class="text-neutral-600">
                            <i class="fas fa-info-circle me-1"></i>Flyer Paket <?php echo htmlspecialchars($package['name']); ?>
                        </small>
                    </div>
                </div>
            </div>
            <!-- Informasi Paket - SISI KIRI -->
            <div class="col-lg-6">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <h2 class="text-green-900 mb-0"><?php echo htmlspecialchars($package['name']); ?></h2>
                            <span class="badge bg-green-100 text-green-800 px-3 py-2 border border-green-300">
                                <i class="fas fa-clock me-1 text-green-600"></i><?php echo (int)$package['duration']; ?> Hari
                            </span>
                        </div>
                        
                        <p class="text-neutral-700 mb-4"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                        
                        <!-- Price -->
                        <div class="mb-4">
                            <h4 class="package-price-modern mb-2">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></h4>
                            <small class="text-neutral-600">
                                <i class="fas fa-user me-1"></i>Per Orang
                            </small>
                        </div>
                        
                        <!-- Facilities -->
                        <div class="mb-4">
                            <h5 class="text-green-800 fw-semibold mb-3">
                                <i class="fas fa-check-circle text-green-600 me-2"></i>Fasilitas Paket:
                            </h5>
                            <?php
                            $jsonCheck = json_decode($package['facilities'], true);
                            $facilities = (json_last_error() === JSON_ERROR_NONE && is_array($jsonCheck))
                                          ? $jsonCheck
                                          : preg_split('/\r\n|\r|\n/', $package['facilities']);
                            ?>
                            <ul class="package-facilities">
                                <?php foreach ($facilities as $facility):
                                    $facility = trim($facility);
                                    if ($facility === '') continue;
                                ?>
                                    <li><?php echo htmlspecialchars($facility); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- CTA Button -->
                        <div class="mt-4">
                            <a href="contact.php" class="btn-modern-green primary w-100 with-icon">
                                <i class="fas fa-calendar-check me-2"></i>Daftar Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
</section>

    <!-- Section Jadwal Keberangkatan - Style modern -->
<!-- Section Jadwal Keberangkatan - Style modern -->
<section id="schedules" class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-green-900 mb-3">Jadwal Keberangkatan</h2>
            <p class="lead text-neutral-700 mb-0">Daftar jadwal keberangkatan yang tersedia</p>
        </div>
        
        <?php if (!empty($schedules)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-green-100">
                        <tr>
                            <th class="text-green-900">Tanggal Keberangkatan</th>
                            <th class="text-green-900">Tanggal Kembali</th>
                            <th class="text-green-900">Maskapai</th>
                            <th class="text-green-900">Rute</th>
                            <th class="text-green-900">Hari</th>
                            <th class="text-green-900">Slot</th>
                            <th class="text-green-900">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-alt text-green-600 me-2"></i>
                                        <?php echo date('d M Y', strtotime($schedule['departure_date'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-check text-green-600 me-2"></i>
                                        <?php echo date('d M Y', strtotime($schedule['return_date'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-plane text-green-600 me-2"></i>
                                        <?php echo htmlspecialchars($schedule['airline']); ?>
                                    </div>
                                </td>
                                <td><small class="text-neutral-600"><?php echo htmlspecialchars($schedule['route']); ?></small></td>
                                <td><?php echo htmlspecialchars($schedule['departure_day']); ?></td>
                                <td>
                                    <?php if ($schedule['available_slots'] > 0): ?>
                                        <span class="badge bg-green-100 text-green-800">
                                            <?php echo (int)$schedule['available_slots']; ?> slot
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-red-100 text-red-800">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($schedule['status'] === 'available'): ?>
                                        <span class="badge bg-green-100 text-green-800">Tersedia</span>
                                    <?php elseif ($schedule['status'] === 'full'): ?>
                                        <span class="badge bg-red-100 text-red-800">Penuh</span>
                                    <?php else: ?>
                                        <span class="badge bg-gray-100 text-gray-800"><?php echo htmlspecialchars($schedule['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state-modern">
                    <i class="fas fa-calendar-times text-green-300" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-green-800">Belum ada jadwal keberangkatan tersedia</h5>
                    <p class="text-neutral-700 mb-4">Hubungi kami untuk informasi jadwal terbaru</p>
                    <a href="contact.php" class="btn-modern-green primary with-icon">
                        <i class="fas fa-phone-alt me-2"></i>Hubungi Kami
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
   
<?php else: ?>
    <section class="py-5 bg-green-50">
        <div class="container">
            <div class="text-center py-5">
                <div class="empty-state-modern">
                    <i class="fas fa-search text-green-300" style="font-size: 4rem;"></i>
                    <h2 class="text-green-800 mt-3">Oops!</h2>
                    <p class="text-neutral-700 mb-4"><?php echo htmlspecialchars($errorMessage); ?></p>
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <a href="packages.php" class="btn-modern-green primary with-icon">
                            <i class="fas fa-box-open me-2"></i>Kembali ke Daftar Paket
                        </a>
                        <a href="index.php" class="btn-modern-green outline-green with-icon">
                            <i class="fas fa-home me-2"></i>Kembali ke Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- CTA - Style modern -->
<section id="cta-detail" class="py-5 bg-green-gradient">
    <div class="container text-center">
        <h2 class="text-white mb-3 fw-bold">Siap Berangkat Umroh?</h2>
        <p class="text-white opacity-90 mb-4">Daftar sekarang dan dapatkan pengalaman ibadah terbaik.</p>
        <a href="contact.php" class="btn-modern-green accent with-icon">
            <i class="fas fa-phone-alt me-2"></i>Hubungi Kami
        </a>
    </div>
</section>

<?php include 'views/footer.php'; ?>

<script>
$(document).ready(function() {
    // Handle schedule selection
    $('.schedule-select-btn').click(function(e) {
        e.preventDefault();
        
        const scheduleId = $(this).data('schedule-id');
        const departureDate = $(this).data('departure-date');
        const returnDate = $(this).data('return-date');
        const airline = $(this).data('airline');
        
        // Format dates
        const formattedDeparture = new Date(departureDate).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const formattedReturn = new Date(returnDate).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Show confirmation modal or redirect
        if (confirm(`Pilih jadwal ini?\n\nKeberangkatan: ${formattedDeparture}\nKembali: ${formattedReturn}\nMaskapai: ${airline}`)) {
            window.location.href = `contact.php?schedule_id=${scheduleId}`;
        }
    });
    
    // Add tooltips for route info
    $('.route-info').each(function() {
        const routeText = $(this).text().trim();
        if (routeText.length > 20) {
            $(this).attr('title', routeText);
            $(this).tooltip({
                placement: 'top',
                trigger: 'hover'
            });
        }
    });
});
</script>