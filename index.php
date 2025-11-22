<?php
require_once 'config/database.php';

// Ambil settings (tagline & contact phone)
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2', 'contact_phone')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);

// Ambil paket unggulan aktif (limit 3)
$queryPackages = "SELECT * FROM packages WHERE is_active = 1 ORDER BY id DESC LIMIT 3";
$stmtPackages = $pdo->prepare($queryPackages);
$stmtPackages->execute();
$packages = $stmtPackages->fetchAll();

// Ambil testimoni 5 terbaru (approve)
$queryTestimonials = "SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 5";
$stmtTestimonials = $pdo->prepare($queryTestimonials);
$stmtTestimonials->execute();
$testimonials = $stmtTestimonials->fetchAll();

// Ambil galeri 8 foto aktif
$queryGalleries = "SELECT * FROM galleries WHERE type = 'image' AND is_active = 1 ORDER BY created_at DESC LIMIT 8";
$stmtGalleries = $pdo->prepare($queryGalleries);
$stmtGalleries->execute();
$galleries = $stmtGalleries->fetchAll();

$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
$whatsapp = $settings['contact_phone'] ?? "+6281234567890";
?>
<?php include 'views/header.php'; ?>

<header>
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2500">
    <div class="carousel-inner">

      <div class="carousel-item active" style="height:90vh; background: url('assets/img/gambar 1.jpeg') center center/cover no-repeat;">
        <div class="overlay d-flex flex-column justify-content-center align-items-center text-center text-white h-100 px-3"
             style="background-color: rgba(22, 73, 36, 0.63);">
          <h6 class="text-uppercase fw-semibold mb-2" style="letter-spacing: 3px; color: #FAE314;"><?php echo htmlspecialchars($tagline1); ?></h6>
          <h1 class="display-4 fw-bold" style="font-family: 'Montserrat', sans-serif;">ALFARUQ TEAM</h1>
          <p class="lead mt-3 w-75 mx-auto" style="font-family: 'Poppins', sans-serif;"><?php echo htmlspecialchars($tagline2); ?></p>
          <a href="packages.php" class="btn btn-success rounded-pill px-4 py-2 mt-4 shadow"
             style="background: #33a661; border: none;">Lihat Paket Umroh</a>
        </div>
      </div>

      <div class="carousel-item" style="height:90vh; background: url('assets/img/gambar 2.jpeg') center center/cover no-repeat;">
        <div class="overlay d-flex flex-column justify-content-center align-items-center text-center text-white h-100 px-3"
             style="background-color: rgba(22,73,36, 0.6);">
          <h6 class="text-uppercase fw-semibold mb-2" style="letter-spacing: 3px; color: #FAE314;">HARGA HEMAT FASILITAS TERHORMAT</h6>
          <h1 class="display-4 fw-bold" style="font-family: 'Montserrat', sans-serif;">Melayani Perjalanan Ibadah Anda</h1>
          <p class="lead mt-3 w-75 mx-auto" style="font-family: 'Poppins', sans-serif;">Dapatkan pengalaman umroh terbaik bersama ALFARUQ TEAM.</p>
          <a href="contact.php" class="btn btn-success rounded-pill px-4 py-2 mt-4 shadow"
             style="background: #33a661; border: none;">Hubungi Kami</a>
        </div>
      </div>

    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon bg-success rounded-circle p-3" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon bg-success rounded-circle p-3" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
</header>

<!-- Paket Unggulan -->
<section id="packages" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Paket Unggulan</h2>
        <div class="row">
            <?php foreach ($packages as $package): ?>
                <div class="col-md-4 mb-4">
                    <div class="card rounded shadow-sm border-0 h-100">
                        <img src="<?php echo htmlspecialchars($package['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title text-success"><?php echo htmlspecialchars($package['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($package['description'], 0, 100)) . '...'; ?></p>
                            <p class="text-primary fw-bold">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?> / <?php echo (int)$package['duration']; ?> hari</p>
                            <a href="package-detail.php?id=<?php echo (int)$package['id']; ?>" class="btn btn-success rounded-pill">Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimoni -->
<section id="testimonials" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Testimoni Jamaah</h2>
        <div class="row">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-md-6 mb-4">
                    <div class="card rounded shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?php echo htmlspecialchars($testimonial['image']); ?>" class="rounded-circle me-3" width="50" height="50" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                    <small class="text-warning"><?php echo str_repeat('★', (int)$testimonial['rating']); ?></small>
                                </div>
                            </div>
                            <p class="card-text"><?php echo htmlspecialchars($testimonial['message']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- Galeri Slider -->
<section id="gallery" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Galeri Perjalanan</h2>
        <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($galleries as $index => $gallery): ?>
                    <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($gallery['image']); ?>" class="d-block w-100 rounded" alt="<?php echo htmlspecialchars($gallery['title']); ?>">
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?php echo htmlspecialchars($gallery['title']); ?></h5>
                            <p><?php echo htmlspecialchars($gallery['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true" style="background-color: #33a661; border-radius: 50%; padding: 10px;"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true" style="background-color: #33a661; border-radius: 50%; padding: 10px;"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<!-- CTA WhatsApp -->
<section id="cta" class="py-5 bg-warning text-dark text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">Siap Berangkat Umroh?</h2>
        <p class="mb-4">Hubungi kami sekarang untuk konsultasi gratis!</p>
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>?text=Halo%20ALFARUQ%20TEAM,%20saya%20ingin%20konsultasi%20paket%20umroh"
           class="btn btn-success btn-lg rounded-pill px-4" target="_blank">Chat WhatsApp</a>
    </div>
</section>

<?php include 'views/footer.php'; ?>
