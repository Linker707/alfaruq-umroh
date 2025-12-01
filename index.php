<?php
// index.php - Homepage ALFARUQ TEAM
require_once 'config/database.php'; // Memuat koneksi database PDO

// Mulai session di awal file untuk menyimpan data testimonial sementara
session_start();

// Check jika ada pesan sukses dari testimonial-qna.php
$popupMessage = '';
if (isset($_SESSION['success_message'])) {
    $popupMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Hapus session setelah ambil
}

// Ambil settings (tagline & contact phone) - Menggunakan PDO prepared statement untuk keamanan
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2', 'contact_phone')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR); // Mengambil sebagai array key => value

// Ambil paket unggulan aktif (limit 3) - Query paket yang aktif, urutkan berdasarkan ID terbaru
$queryPackages = "SELECT * FROM packages WHERE is_active = 1 ORDER BY id DESC LIMIT 3";
$stmtPackages = $pdo->prepare($queryPackages);
$stmtPackages->execute();
$packages = $stmtPackages->fetchAll();

// Ambil semua testimoni yang approved - Hapus LIMIT agar semua muncul di slider
$queryTestimonials = "SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC";
$stmtTestimonials = $pdo->prepare($queryTestimonials);
$stmtTestimonials->execute();
$testimonials = $stmtTestimonials->fetchAll();

// Ambil galeri 8 foto aktif - Galeri gambar yang aktif, urutkan terbaru
$queryGalleries = "SELECT * FROM galleries WHERE type = 'image' AND is_active = 1 ORDER BY created_at DESC LIMIT 8";
$stmtGalleries = $pdo->prepare($queryGalleries);
$stmtGalleries->execute();
$galleries = $stmtGalleries->fetchAll();

// Set fallback untuk tagline dan whatsapp jika tidak ada di DB
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
$whatsapp = $settings['contact_phone'] ?? "+6281234567890";

// Handle form testimonial awal (nama, email, rating) - Gabungkan handler menjadi satu, simpan ke session, redirect ke QnA
$message = ''; // Variabel untuk pesan sukses/error, ditampilkan di halaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    // Ambil data dari form (sesuai nama field di form: name, email, rating)
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);

    // Validasi sederhana: pastikan semua field diisi, rating 1-5, email valid
    if (empty($name) || empty($email) || $rating < 1 || $rating > 5) {
        $message = '<div class="alert alert-danger">Semua field wajib diisi, dan rating 1-5!</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Email tidak valid!</div>';
    } else {
        // Simpan data awal ke session untuk digunakan di halaman QnA
        $_SESSION['testimonial'] = [
            'name' => $name,
            'email' => $email,
            'rating' => $rating
        ];
        // Redirect ke halaman QnA setelah sukses
        header('Location: testimonial-qna.php');
        exit; // Hentikan eksekusi script agar tidak lanjut ke HTML
    }
}
?>
<?php include 'views/header.php'; // Include file header (navbar, meta tags, dll.) ?>

<!-- Hero Section - Slider gambar dengan overlay teks -->
<header>
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2500">
        <div class="carousel-inner">
            <!-- Slide 1 -->
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
            <!-- Slide 2 -->
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
        <!-- Kontrol carousel -->
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

<!-- Paket Unggulan - Grid card paket dengan link ke detail -->
<section id="packages" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Paket Unggulan</h2>
        <div class="row">
            <?php foreach ($packages as $package): // Loop untuk setiap paket ?>
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
        <!-- Button tambahan untuk berpindah ke page packages -->
        <div class="text-center mt-4">
            <a href="packages.php" class="btn btn-success btn-lg rounded-pill px-4">Lihat Semua Paket</a>
        </div>
    </div>
</section>

<!-- Pembatas setelah Paket Unggulan - Garis horizontal dengan ikon hijau -->
<div class="divider my-5">
    <div class="container">
        <hr class="my-4" style="border: 1px solid #33a661; opacity: 0.5;">
        <div class="text-center">
            <i class="fas fa-leaf text-success" style="font-size: 2rem;"></i> <!-- Ikon daun hijau untuk tema -->
        </div>
    </div>
</div>

<!-- Testimoni - Carousel slider dengan 3 card per slide, semua dari DB -->
<section id="testimonials" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Testimoni Jamaah</h2>
        <?php if (!empty($testimonials)): ?>
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000"> <!-- Interval 5 detik -->
                <div class="carousel-inner">
                    <?php
                    $totalTestimonials = count($testimonials);
                    $testimonialsPerSlide = 3; // 3 card per slide
                    $totalSlides = ceil($totalTestimonials / $testimonialsPerSlide);
                    for ($slideIndex = 0; $slideIndex < $totalSlides; $slideIndex++):
                        $startIndex = $slideIndex * $testimonialsPerSlide;
                        $slideTestimonials = array_slice($testimonials, $startIndex, $testimonialsPerSlide);
                    ?>
                        <div class="carousel-item <?php echo ($slideIndex === 0) ? 'active' : ''; ?>">
                            <div class="row justify-content-center">
                                <?php foreach ($slideTestimonials as $testimonial): ?>
                                    <div class="col-md-4 mb-4"> <!-- 3 card per row -->
                                        <div class="card rounded shadow-sm border-0 h-100">
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
                    <?php endfor; ?>
                </div>
                <!-- Kontrol carousel -->
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true" style="background-color: #33a661; border-radius: 50%; padding: 10px;"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true" style="background-color: #33a661; border-radius: 50%; padding: 10px;"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <!-- Indikator carousel (opsional, untuk navigasi dot) -->
                <div class="carousel-indicators">
                    <?php for ($i = 0; $i < $totalSlides; $i++): ?>
                        <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo ($i === 0) ? 'active' : ''; ?>" aria-current="true" aria-label="Slide <?php echo $i + 1; ?>"></button>
                    <?php endfor; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Belum ada testimoni.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Form Input Testimoni Awal - Form sederhana untuk nama, email, rating -->
<section id="testimonial-form" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Berikan Testimoni Anda</h2>
        <?php echo $message; // Tampilkan pesan error jika ada ?>
        <form method="POST" action="" class="row g-3 justify-content-center">
            <div class="col-md-4">
                <label for="name" class="form-label fw-bold text-success">Nama Lengkap</label>
                <input type="text" class="form-control rounded-pill" id="name" name="name" style="border: 2px solid #33a661;" required>
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label fw-bold text-success">Email</label>
                <input type="email" class="form-control rounded-pill" id="email" name="email" style="border: 2px solid #33a661;" required>
            </div>
            <div class="col-md-4">
                <label for="rating" class="form-label fw-bold text-success">Rating (1-5 Bintang)</label>
                <select class="form-select rounded-pill" id="rating" name="rating" style="border: 2px solid #33a661;" required>
                    <option value="">Pilih Rating</option>
                    <option value="1">1 ★</option>
                    <option value="2">2 ★★</option>
                    <option value="3">3 ★★★</option>
                    <option value="4">4 ★★★★</option>
                    <option value="5">5 ★★★★★</option>
                </select>
            </div>
            <div class="col-12 text-center">
                <button type="submit" name="submit_testimonial" class="btn btn-success btn-lg rounded-pill px-4">Lanjutkan</button>
            </div>
        </form>
    </div>
</section>

<!-- Pembatas setelah Galeri Perjalanan - Garis horizontal dengan ikon hijau -->
<div class="divider my-5">
    <div class="container">
        <hr class="my-4" style="border: 1px solid #33a661; opacity: 0.5;">
        <div class="text-center">
            <i class="fas fa-leaf text-success" style="font-size: 2rem;"></i> <!-- Ikon daun hijau untuk tema -->
        </div>
    </div>
</div>

<!-- Galeri Slider - Carousel gambar galeri -->
<section id="gallery" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Galeri Perjalanan</h2>
        <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($galleries as $index => $gallery): // Loop untuk setiap gambar galeri ?>
                    <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($gallery['image']); ?>" class="d-block w-100 rounded" alt="<?php echo htmlspecialchars($gallery['title']); ?>">
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?php echo htmlspecialchars($gallery['title']); ?></h5>
                            <p><?php echo htmlspecialchars($gallery['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Kontrol carousel -->
            <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true" style="background-color: #33a661; border-radius: 50%; padding: 10px;"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true" style="background-color: #33a661; border-radius: 50%; padding: 10px;"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <!-- Button tambahan untuk detail lebih lanjut ke page gallery -->
        <div class="text-center mt-4">
            <a href="gallery.php" class="btn btn-success btn-lg rounded-pill px-4">Detail Lebih Lanjut</a>
        </div>
    </div>
</section>

<!-- Pembatas setelah Galeri Perjalanan - Garis horizontal dengan ikon hijau -->
<div class="divider my-5">
    <div class="container">
        <hr class="my-4" style="border: 1px solid #33a661; opacity: 0.5;">
        <div class="text-center">
            <i class="fas fa-leaf text-success" style="font-size: 2rem;"></i> <!-- Ikon daun hijau untuk tema -->
        </div>
    </div>
</div>

<!-- Section Mitra Kami - Grid logo mitra dengan card menarik, animasi, efek, dan hover -->
<section id="partners" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Mitra Kami</h2>
        <p class="text-center text-muted mb-5">Kami bekerja sama dengan mitra terpercaya untuk memberikan layanan terbaik kepada jamaah.</p>
        <div class="row g-4 justify-content-center"> <!-- Grid responsif dengan gap -->
            <?php
            // Array logo mitra dengan path gambar (sesuaikan path jika berbeda)
            $partners = [
                ['name' => 'Kemenag', 'image' => 'assets/img/KEMENAG.png'],
                ['name' => '5 PASTI UMRAH', 'image' => 'assets/img/5pasti.png'],
                ['name' => 'SISKOPATUH', 'image' => 'assets/img/SISKOPATUH.png'],
                ['name' => 'ASPHIRASI', 'image' => 'assets/img/LOGO ASPHIRASI.png'],
                ['name' => 'Lion Air', 'image' => 'assets/img/lionair.png'],
                ['name' => 'Batik Air', 'image' => 'assets/img/batik-air.png'],
                ['name' => 'Bank BSI', 'image' => 'assets/img/logo-bsi.png'],
                ['name' => 'Bank BCA', 'image' => 'assets/img/logo-bca.png']
            ];
            foreach ($partners as $partner): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 d-flex justify-content-center"> <!-- Kolom responsif, center align -->
                    <div class="card border-0 shadow-sm rounded-lg h-100 d-flex align-items-center justify-content-center p-4" style="transition: transform 0.3s ease, box-shadow 0.3s ease; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); max-width: 200px;"> <!-- Card dengan gradient background, efek hover -->
                        <img src="<?php echo htmlspecialchars($partner['image']); ?>" class="card-img-top img-fluid" alt="<?php echo htmlspecialchars($partner['name']); ?>" style="max-height: 80px; object-fit: contain; transition: transform 0.3s ease;"> <!-- Logo dengan efek hover zoom -->
        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA WhatsApp - Call to action untuk hubungi via WhatsApp -->
<section id="cta" class="py-5 bg-warning text-dark text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">Siap Berangkat Umroh?</h2>
        <p class="mb-4">Hubungi kami sekarang untuk konsultasi gratis!</p>

        <?php 
        // Nomor WhatsApp (bisa fixed atau bisa dari database)
        $waNumber = "6281266303236";

        // Pesan otomatis yang aman untuk URL
        $text = urlencode("Halo ALFARUQ TEAM, saya ingin konsultasi paket umroh");
        ?>

        <a href="https://wa.me/<?php echo $waNumber; ?>?text=<?php echo $text; ?>"
           class="btn btn-success btn-lg rounded-pill px-4" 
           target="_blank">
            Chat WhatsApp
        </a>
    </div>
</section>


<?php include 'views/footer.php'; // Include file footer (script, dll.) ?>

<!-- Script untuk pop-up notifikasi jika ada pesan sukses -->
<?php if ($popupMessage): ?>
    <script>
        alert("<?php echo addslashes($popupMessage); ?>");
    </script>
<?php endif; ?>