<?php
// index.php - Homepage ALFARUQ TEAM dengan Modern Green Theme
require_once 'config/database.php';

// Mulai session
session_start();

// Check pesan sukses dari testimonial-qna.php
$popupMessage = '';
if (isset($_SESSION['success_message'])) {
    $popupMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Include header
include 'views/header.php';

// Ambil settings untuk halaman ini jika diperlukan lagi
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2', 'contact_phone')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);

// Ambil paket populer aktif (is_popular = 1) dengan harga min-max
$queryPackages = "SELECT p.*, 
                         MIN(pp.price) as min_price,
                         MAX(pp.price) as max_price
                  FROM packages p 
                  LEFT JOIN package_prices pp ON p.id = pp.package_id AND pp.is_active = 1
                  WHERE p.is_active = 1 AND p.is_popular = 1
                  GROUP BY p.id 
                  ORDER BY p.id DESC 
                  LIMIT 3";

// Jika tidak ada paket populer, ambil paket terbaru
$stmtPackages = $pdo->prepare($queryPackages);
$stmtPackages->execute();
$packages = $stmtPackages->fetchAll();

if (empty($packages)) {
    // Fallback: ambil 3 paket terbaru
    $queryFallback = "SELECT p.*, 
                             MIN(pp.price) as min_price,
                             MAX(pp.price) as max_price
                      FROM packages p 
                      LEFT JOIN package_prices pp ON p.id = pp.package_id AND pp.is_active = 1
                      WHERE p.is_active = 1 
                      GROUP BY p.id 
                      ORDER BY p.id DESC 
                      LIMIT 3";
    $stmtFallback = $pdo->prepare($queryFallback);
    $stmtFallback->execute();
    $packages = $stmtFallback->fetchAll();
}

// Ambil semua testimoni yang approved
$queryTestimonials = "SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC";
$stmtTestimonials = $pdo->prepare($queryTestimonials);
$stmtTestimonials->execute();
$testimonials = $stmtTestimonials->fetchAll();

// Ambil galeri 8 foto aktif
$queryGalleries = "SELECT * FROM galleries WHERE type = 'image' AND is_active = 1 ORDER BY created_at DESC LIMIT 8";
$stmtGalleries = $pdo->prepare($queryGalleries);
$stmtGalleries->execute();
$galleries = $stmtGalleries->fetchAll();

// Set fallback
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
$whatsapp = $settings['contact_phone'] ?? "+6281234567890";

// Handle form testimonial
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);

    if (empty($name) || empty($email) || $rating < 1 || $rating > 5) {
        $message = '<div class="alert alert-danger rounded-pill">Semua field wajib diisi, dan rating 1-5!</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger rounded-pill">Email tidak valid!</div>';
    } else {
        $_SESSION['testimonial'] = [
            'name' => $name,
            'email' => $email,
            'rating' => $rating
        ];
        header('Location: testimonial-qna.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<!-- ============================================
    MODERN HERO SECTION DENGAN CAROUSEL
============================================ -->
<header>
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active" style="height:90vh; background: url('assets/img/gambar 1.jpeg') center center/cover no-repeat;">
                <div class="hero-overlay-modern d-flex flex-column justify-content-center align-items-center text-center text-white h-100 px-3">
                    <!-- Tagline Badge -->
                    <div class="mb-4">
                        <span class="badge bg-accent-gradient text-dark px-4 py-2 rounded-pill animate__animated animate__pulse animate__infinite">
                            <i class="fas fa-star me-2"></i><?php echo htmlspecialchars($tagline1); ?>
                        </span>
                    </div>
                    
                    <!-- Main Heading -->
                    <h1 class="display-3 fw-bold mb-4 text-white">
                        ALFARUQ TEAM
                        <br>
                        <span class="fs-4 text-white opacity-90">Travel Umroh Terpercaya</span>
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="lead mb-5 text-white opacity-90 w-75 mx-auto">
                        <?php echo htmlspecialchars($tagline2); ?>
                        <br>
                        <small class="text-white opacity-75">Melayani perjalanan ibadah Anda dengan penuh kepercayaan dan profesionalisme.</small>
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <a href="packages.php" class="btn-modern-green primary lg with-icon">
                            <i class="fas fa-box-open me-2"></i> Lihat Paket Umroh
                        </a>
                        <a href="contact.php" class="btn-modern-green accent lg with-icon">
                            <i class="fas fa-phone-alt me-2"></i> Hubungi Kami
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="carousel-item" style="height:90vh; background: url('assets/img/gambar 2.jpeg') center center/cover no-repeat;">
                <div class="hero-overlay-modern d-flex flex-column justify-content-center align-items-center text-center text-white h-100 px-3">
                    <div class="mb-4">
                        <span class="badge bg-green-gradient px-4 py-2 rounded-pill">
                            <i class="fas fa-hands-helping me-2"></i>Pelayanan Profesional
                        </span>
                    </div>
                    
                    <h1 class="display-3 fw-bold mb-4 text-white">
                        Pengalaman Ibadah<br>Terbaik
                    </h1>
                    
                    <p class="lead mb-5 text-white opacity-90 w-75 mx-auto">
                        Dapatkan bimbingan ibadah lengkap, akomodasi terbaik, dan<br>pendampingan dari tim profesional kami.
                    </p>
                    
                    <a href="about.php" class="btn-modern-green primary lg with-icon">
                        <i class="fas fa-info-circle me-2"></i>Tentang Kami
                    </a>
                </div>
            </div>
            
            <!-- Slide 3 -->
            <div class="carousel-item" style="height:90vh; background: url('assets/img/makkah5.jpeg') center center/cover no-repeat;">
                <div class="hero-overlay-modern d-flex flex-column justify-content-center align-items-center text-center text-white h-100 px-3">
                    <div class="mb-4">
                        <span class="badge bg-accent-gradient text-dark px-4 py-2 rounded-pill">
                            <i class="fas fa-calendar-check me-2"></i>Jadwal Terjamin
                        </span>
                    </div>
                    
                    <h1 class="display-3 fw-bold mb-4 text-white">
                        Jadwal Keberangkatan<br>Pasti
                    </h1>
                    
                    <p class="lead mb-5 text-white opacity-90 w-75 mx-auto">
                        Pilih dari berbagai jadwal keberangkatan yang tersedia.<br>Keberangkatan tepat waktu, pulang dengan penuh berkah.
                    </p>
                    
                    <a href="packages.php" class="btn-modern-green primary lg with-icon">
                        <i class="fas fa-calendar-alt me-2"></i>Cek Jadwal
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Carousel Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon bg-green-gradient rounded-circle p-3" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon bg-green-gradient rounded-circle p-3" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        
        <!-- Indicators -->
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
    </div>
</header>

<!-- ============================================
    MODERN PACKAGE SECTION - CARD LEBIH TERANG
============================================ -->
<section id="packages" class="py-5 bg-green-50">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5">
            <h2 class="text-green-900 mb-3">Paket Unggulan</h2>
            <p class="lead text-neutral-700 mb-0">Pilih paket umroh terbaik sesuai kebutuhan dan budget Anda</p>
            <div class="mt-3">
                <span class="badge bg-green-100 text-green-800 px-3 py-2 border border-green-300">
                    <i class="fas fa-check-circle me-2 text-green-600"></i>Garansi Harga Terbaik
                </span>
            </div>
        </div>
        
        <!-- Package Cards -->
        <div class="row g-4">
            <?php foreach ($packages as $index => $package): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card-modern-green-light <?php echo $index === 0 ? 'featured' : ''; ?> 
                         animate__animated animate__fadeInUp" 
                     style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    
                    <?php if ($package['is_popular'] == 1): ?>
                        <div class="card-badge-popular">
                            <i class="fas fa-crown me-1"></i> Paket Populer
                        </div>
                    <?php endif; ?>
                    
                    <img src="<?php echo htmlspecialchars($package['image']); ?>" 
                         class="card-img-modern" 
                         alt="Paket <?php echo htmlspecialchars($package['name']); ?>"
                         loading="lazy">
                    
                    <div class="card-body-modern">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title-modern text-green-900 mb-0">
                                <?php echo htmlspecialchars($package['name']); ?>
                            </h5>
                            <span class="badge bg-green-100 text-green-800 px-3 border border-green-300">
                                <i class="fas fa-clock me-1 text-green-600"></i><?php echo (int)$package['duration']; ?> Hari
                            </span>
                        </div>
                        
                        <p class="card-text-modern text-neutral-700 mb-4">
                            <?php echo htmlspecialchars(substr($package['description'], 0, 120)); ?>
                            <?php if (strlen($package['description']) > 120): ?>
                            <span class="text-green-700 fw-medium">...selengkapnya</span>
                            <?php endif; ?>
                        </p>
                        
                        <!-- Facilities Preview -->
                        <div class="mb-4">
                            <h6 class="text-green-800 fw-semibold mb-3">
                                <i class="fas fa-check-circle text-green-600 me-2"></i>Fasilitas Unggulan:
                            </h6>
                            <?php 
                            $facilities = json_decode($package['facilities'], true);
                            if (is_array($facilities) && count($facilities) > 0):
                                $previewFacilities = array_slice($facilities, 0, 3);
                            ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($previewFacilities as $facility): ?>
                                <li class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check text-green-500 me-2" style="font-size: 0.875rem;"></i>
                                    <small class="text-neutral-700"><?php echo htmlspecialchars(substr(trim($facility), 0, 35)); ?></small>
                                </li>
                                <?php endforeach; ?>
                                <?php if (count($facilities) > 3): ?>
                                <li class="text-green-700 fw-medium mt-2">
                                    <i class="fas fa-plus-circle me-1"></i>
                                    +<?php echo count($facilities) - 3; ?> fasilitas lainnya
                                </li>
                                <?php endif; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        
                       <!-- Price & Rating -->
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <p class="package-price-modern mb-1 text-green-900">
                                        <?php if ($package['min_price'] != $package['max_price']): ?>
                                            Rp <?php echo number_format($package['min_price'], 0, ',', '.'); ?> - 
                                            Rp <?php echo number_format($package['max_price'], 0, ',', '.'); ?>
                                        <?php else: ?>
                                            Rp <?php echo number_format($package['min_price'], 0, ',', '.'); ?>
                                        <?php endif; ?>
                                    </p>
                                    <small class="text-neutral-600">
                                        <i class="fas fa-user me-1"></i>Per Orang
                                    </small>
                                </div>
                                <!-- Rating tetap sama -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- View All Button -->
        <div class="text-center mt-5 pt-3">
            <a href="packages.php" class="btn-modern-green primary lg with-icon">
                <i class="fas fa-boxes me-2"></i>Lihat Semua Paket
                <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============================================
    DECORATIVE DIVIDER
============================================ -->
<div class="divider-modern py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 text-center">
                <div class="icon-box-modern">
                    <i class="fas fa-shield-alt"></i>
                    <p class="mb-0 text-green-800 fw-semibold">Garansi Legal PPIU</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="icon-box-modern">
                    <i class="fas fa-handshake"></i>
                    <p class="mb-0 text-green-800 fw-semibold">Pelayanan Profesional</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="icon-box-modern">
                    <i class="fas fa-heart"></i>
                    <p class="mb-0 text-green-800 fw-semibold">Bimbingan Ibadah</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
    CLEAN MODERN TESTIMONIAL SECTION
============================================ -->
<section id="testimonials" class="py-5 bg-green-50">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5">
            <h2 class="text-green-900 mb-3">Apa Kata Mereka?</h2>
            <p class="lead text-neutral-700 mb-4">Pengalaman jamaah yang telah berangkat bersama ALFARUQ TEAM</p>
            
            <!-- Overall Rating -->
            <div class="d-inline-block bg-white px-4 py-3 rounded-pill shadow-sm mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center">
                        <div class="text-warning mb-1">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <small class="text-neutral-600">4.8/5 Rating</small>
                    </div>
                    <div class="vr"></div>
                    <div class="text-center">
                        <h4 class="text-green-700 fw-bold mb-0"><?php echo count($testimonials); ?>+</h4>
                        <small class="text-neutral-600">Testimoni</small>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($testimonials)): ?>
        <!-- Testimonial Grid -->
        <div class="testimonial-grid-clean">
            <?php foreach (array_slice($testimonials, 0, 6) as $index => $testimonial): ?>
            <div class="testimonial-clean-modern <?php echo $index === 0 ? 'featured' : ''; ?>">
                <!-- Rating -->
                <div class="testimonial-rating-clean">
                    <?php echo str_repeat('<i class="fas fa-star"></i>', (int)$testimonial['rating']); ?>
                    <?php echo str_repeat('<i class="far fa-star"></i>', 5 - (int)$testimonial['rating']); ?>
                    <span class="rating-badge-clean"><?php echo $testimonial['rating']; ?>.0</span>
                </div>
                
                <!-- Content -->
                <div class="testimonial-content-clean">
                    <p>"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
                </div>
                
                <!-- Author -->
                <div class="testimonial-author-clean">
                    <img src="<?php echo htmlspecialchars($testimonial['image']); ?>" 
                         class="testimonial-author-img-clean" 
                         alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                    <div class="testimonial-author-info-clean">
                        <h6 class="mb-1"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                        <div class="testimonial-date-clean">
                            <i class="far fa-calendar"></i>
                            <span><?php echo date('d M Y', strtotime($testimonial['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Departure Info -->
                <?php if (!empty($testimonial['q3'])): ?>
                <div class="testimonial-departure-clean">
                    <small>
                        <i class="fas fa-users text-green-600"></i>
                        <?php echo htmlspecialchars(substr($testimonial['q3'], 0, 25)); ?>
                    </small>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- CTA Buttons -->
        <div class="text-center mt-5 pt-3">
            <a href="testimonial-qna.php" class="btn-modern-green primary with-icon me-3">
                <i class="fas fa-pen me-2"></i>Berikan Testimoni
            </a>
            <a href="#" class="btn-modern-green outline-green with-icon" id="viewAllTestimonials">
                <i class="fas fa-eye me-2"></i>Lihat Semua
            </a>
        </div>
        
        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="empty-state-modern">
                <i class="fas fa-comments text-green-300"></i>
                <h5 class="mt-3 text-green-800">Belum ada testimoni</h5>
                <p class="text-neutral-700 mb-4">Jadilah yang pertama berbagi pengalaman</p>
                <a href="testimonial-qna.php" class="btn-modern-green primary with-icon">
                    <i class="fas fa-pen me-2"></i>Buat Testimoni Pertama
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================
    TESTIMONIAL FORM MODERN
============================================ -->
<section id="testimonial-form" class="py-5 bg-green-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="pe-lg-4">
                    <h2 class="text-green-gradient mb-3">Bagikan Pengalaman Anda</h2>
                    <p class="text-neutral-700 mb-4">
                        Ceritakan pengalaman ibadah umroh Anda bersama ALFARUQ TEAM. 
                        Testimoni Anda sangat berarti bagi kami dan calon jamaah lainnya.
                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box-small bg-green-100 text-green-700 me-3">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <h6 class="text-green-800 mb-0">Data Terjamin Aman</h6>
                            <small class="text-neutral-600">Informasi pribadi dijaga kerahasiaannya</small>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="icon-box-small bg-green-100 text-green-700 me-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h6 class="text-green-800 mb-0">Proses Cepat & Mudah</h6>
                            <small class="text-neutral-600">Hanya 5 menit untuk mengisi form</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="card-modern-green">
                    <div class="card-modern-green-body">
                        <h4 class="card-modern-green-title mb-4">Mulai Testimoni Anda</h4>
                        
                        <?php echo $message; ?>
                        
                        <form method="POST" action="" class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label-modern">
                                    <i class="fas fa-user text-green-600 me-2"></i>Nama Lengkap
                                </label>
                                <input type="text" class="form-control-modern" id="name" name="name" 
                                       placeholder="Masukkan nama lengkap Anda" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label-modern">
                                    <i class="fas fa-envelope text-green-600 me-2"></i>Email
                                </label>
                                <input type="email" class="form-control-modern" id="email" name="email" 
                                       placeholder="email@contoh.com" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label-modern">
                                    <i class="fas fa-star text-green-600 me-2"></i>Rating Pengalaman
                                </label>
                                <div class="rating-stars-modern">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" 
                                           class="d-none" required>
                                    <label for="star<?php echo $i; ?>" class="star-label">
                                        <i class="fas fa-star"></i>
                                    </label>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-neutral-600 mt-2 d-block">
                                    Klik bintang untuk memberikan rating (1-5)
                                </small>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" name="submit_testimonial" 
                                        class="btn-modern-green primary w-100 with-icon">
                                    <i class="fas fa-paper-plane me-2"></i>Lanjutkan ke Form Lengkap
                                </button>
                            </div>
                            
                            <div class="col-12">
                                <small class="text-neutral-600">
                                    <i class="fas fa-info-circle text-green-600 me-1"></i>
                                    Anda akan diarahkan ke halaman form testimoni lengkap setelah mengisi data dasar.
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
    MODERN GALLERY SECTION DENGAN KONTRAYS BAIK
============================================ -->
<section id="gallery" class="py-5 bg-green-50">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5">
            <h2 class="text-green-900 mb-3">Galeri Perjalanan</h2>
            <p class="lead text-neutral-700 mb-0">Momen-momen indah dari perjalanan ibadah bersama jamaah kami</p>
        </div>
        
        <?php if (!empty($galleries)): ?>
        <!-- Gallery Grid -->
        <div class="row g-3">
            <?php foreach ($galleries as $index => $gallery): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="gallery-item-modern-light">
                    <img src="<?php echo htmlspecialchars($gallery['image']); ?>" 
                         class="gallery-img-modern" 
                         alt="<?php echo htmlspecialchars($gallery['title']); ?>"
                         loading="lazy"
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
        
        <!-- View All Button -->
        <div class="text-center mt-5">
            <a href="gallery.php" class="btn-modern-green outline-green with-icon">
                <i class="fas fa-images me-2"></i>Lihat Semua Galeri
            </a>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <div class="empty-state-modern">
                <i class="fas fa-image text-green-300" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-green-800">Belum ada galeri</h5>
                <p class="text-neutral-700">Galeri perjalanan akan segera tersedia</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================
    MODERN PARTNERS SECTION
============================================ -->
<section id="partners" class="py-5 bg-green-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-green-gradient mb-3">Mitra Terpercaya</h2>
            <p class="lead text-neutral-700 mb-0">Bekerjasama dengan institusi dan perusahaan terkemuka</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php
            $partners = [
                ['name' => 'Kemenag', 'image' => 'assets/img/KEMENAG.png', 'desc' => 'Kementerian Agama RI'],
                ['name' => '5 PASTI UMRAH', 'image' => 'assets/img/5pasti.png', 'desc' => 'Travel Partner'],
                ['name' => 'SISKOPATUH', 'image' => 'assets/img/SISKOPATUH.png', 'desc' => 'Sistem Komputerisasi Patuh'],
                ['name' => 'ASPHIRASI', 'image' => 'assets/img/LOGO ASPHIRASI.png', 'desc' => 'Asosiasi Penyedia Haji & Umroh'],
                ['name' => 'Lion Air', 'image' => 'assets/img/lionair.png', 'desc' => 'Maskapai Penerbangan'],
                ['name' => 'Batik Air', 'image' => 'assets/img/batik-air.png', 'desc' => 'Maskapai Penerbangan'],
                ['name' => 'Bank BSI', 'image' => 'assets/img/logo-bsi.png', 'desc' => 'Bank Syariah Indonesia'],
                ['name' => 'Bank BCA', 'image' => 'assets/img/logo-bca.png', 'desc' => 'Bank Central Asia'],
            ];
            
            foreach ($partners as $partner): 
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="partner-card-modern">
                    <div class="partner-logo-modern">
                        <img src="<?php echo htmlspecialchars($partner['image']); ?>" 
                             alt="<?php echo htmlspecialchars($partner['name']); ?>"
                             loading="lazy">
                    </div>
                    <div class="partner-info-modern">
                        <h6 class="text-green-900 mb-1"><?php echo htmlspecialchars($partner['name']); ?></h6>
                        <small class="text-neutral-600"><?php echo htmlspecialchars($partner['desc']); ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================
    MODERN CTA SECTION
============================================ -->
<section id="cta" class="py-5 bg-green-gradient">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="text-white mb-3">Siap Memulai Perjalanan Ibadah Anda?</h2>
                <p class="text-white opacity-90 mb-0">
                    Konsultasikan kebutuhan umroh Anda dengan tim profesional kami. 
                    Dapatkan penawaran terbaik dan panduan lengkap untuk perjalanan spiritual Anda.
                </p>
            </div>
            
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <?php
                    $waNumber = preg_replace('/[^0-9]/', '', $whatsapp);
                    $text = urlencode("Halo ALFARUQ TEAM, saya ingin konsultasi paket umroh");
                    ?>
                    <a href="https://wa.me/<?php echo $waNumber; ?>?text=<?php echo $text; ?>" 
                       class="btn-modern-green accent with-icon" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                    <a href="contact.php" class="btn-modern-green outline text-white border-white">
                        <i class="fas fa-phone-alt me-2"></i>Hubungi
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
    MODERN FOOTER
============================================ -->
<footer class="footer-modern-green">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 mb-4">
                <div class="d-flex align-items-center mb-4">
                    <img src="assets/img/logo.svg" alt="Logo" width="60" class="me-3">
                    <div>
                        <h5 class="text-white mb-0">ALFARUQ TEAM</h5>
                        <small class="text-white opacity-75">Travel Umroh Terpercaya</small>
                    </div>
                </div>
                
                <p class="text-white opacity-75 mb-4">
                    <?php echo htmlspecialchars($tagline1); ?> - 
                    <?php echo htmlspecialchars($tagline2); ?>
                </p>
                
                <div class="social-links-modern">
                    <a href="#" class="social-link-modern" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link-modern" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link-modern" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://wa.me/<?php echo $waNumber; ?>" class="social-link-modern" title="WhatsApp" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-4 mb-4">
                <h6 class="text-white mb-3">Navigasi</h6>
                <ul class="footer-links-modern">
                    <li><a href="index.php"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                    <li><a href="about.php"><i class="fas fa-chevron-right me-2"></i>Tentang Kami</a></li>
                    <li><a href="packages.php"><i class="fas fa-chevron-right me-2"></i>Paket Umroh</a></li>
                    <li><a href="gallery.php"><i class="fas fa-chevron-right me-2"></i>Galeri</a></li>
                    <li><a href="contact.php"><i class="fas fa-chevron-right me-2"></i>Kontak</a></li>
                </ul>
            </div>
            
            <!-- Legal -->
            <div class="col-lg-3 col-md-4 mb-4">
                <h6 class="text-white mb-3">Legalitas</h6>
                <div class="legal-info-modern">
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas fa-file-certificate text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0">PPIU No: SK PPIU NO.24022300153650007</p>
                            <small class="text-white opacity-75">Izin resmi Kementerian Agama</small>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <i class="fas fa-shield-alt text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0">Terdaftar & Diawasi</p>
                            <small class="text-white opacity-75">SISKOPATUH & ASPHIRASI</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-3 col-md-4 mb-4">
                <h6 class="text-white mb-3">Kontak Kami</h6>
                <ul class="contact-info-modern">
                    <li class="d-flex align-items-start mb-3">
                        <i class="fas fa-map-marker-alt text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0">Ruko Bintan Center No. 56</p>
                            <small class="text-white opacity-75">Tanjungpinang, Kepulauan Riau</small>
                        </div>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="fas fa-phone text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0"><?php echo htmlspecialchars($whatsapp); ?></p>
                            <small class="text-white opacity-75">Admin 1: +62 812-6630-3236</small>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fas fa-envelope text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0">alfaruq5619@gmail.com</p>
                            <small class="text-white opacity-75">Email resmi perusahaan</small>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="border-white opacity-25 my-4">
        
        <!-- Copyright -->
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <p class="text-white opacity-75 mb-0">
                    &copy; <?php echo date('Y'); ?> PT. ALFARUQ ANUGERAH UTAMA. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-white opacity-50">
                    Made with <i class="fas fa-heart text-red-400"></i> for the ummah
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- ============================================
    GALLERY MODAL
============================================ -->
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

<!-- ============================================
    FLOATING WHATSAPP BUTTON
============================================ -->
<a href="https://wa.me/<?php echo $waNumber; ?>?text=<?php echo $text; ?>" 
   class="whatsapp-float-modern" target="_blank" title="Chat WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span class="whatsapp-pulse"></span>
</a>

<!-- ============================================
    BACK TO TOP BUTTON
============================================ -->
<button class="back-to-top-modern" id="backToTop" title="Kembali ke atas">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- ============================================
    SCRIPTS
============================================ -->
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (optional for animations) -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Modern Green Theme JS -->
<script src="assets/js/modern-green.js"></script>

<!-- Responsive JS -->
<script src="assets/js/responsive.js"></script>

<!-- Custom Scripts for this page -->
<script>
// DOM Ready Function
$(document).ready(function() {
    // Counter Animation
    $('.counter').each(function() {
        var $this = $(this);
        var countTo = $this.attr('data-count');
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $this.text(Math.floor(this.countNum));
            },
            complete: function() {
                $this.text(this.countNum);
            }
        });
    });
    
    // Rating Stars Interaction
    $('.star-label').click(function() {
        var rating = $(this).attr('for').replace('star', '');
        $('input[name="rating"]').val(rating);
        
        // Update stars display
        $('.star-label i').removeClass('fas text-warning').addClass('far text-muted');
        $(this).prevAll('.star-label').addBack().find('i')
            .removeClass('far text-muted').addClass('fas text-warning');
    });
    
    // Gallery Modal
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
    
    // Back to Top Button
    $('#backToTop').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 500);
    });
    
    // Show/Hide Back to Top
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });
    
    // Navbar scroll effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar-modern-green').addClass('scrolled');
        } else {
            $('.navbar-modern-green').removeClass('scrolled');
        }
    });
    
    // Popup Message
    <?php if ($popupMessage): ?>
    setTimeout(function() {
        alert("<?php echo addslashes($popupMessage); ?>");
    }, 500);
    <?php endif; ?>
});

// WhatsApp Tracking
document.querySelectorAll('a[href*="whatsapp"]').forEach(function(link) {
    link.addEventListener('click', function() {
        // You can add analytics tracking here
        console.log('WhatsApp clicked:', this.href);
    });
});
</script>

</body>
</html>