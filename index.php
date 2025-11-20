<?php
// index.php - Homepage ALFARUQ TEAM
// Include koneksi database
require_once 'config/database.php';

     // Handle form submit testimoni
     $message = ''; // Untuk pesan sukses/error
     if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
         $name = trim($_POST['name'] ?? '');
         $email = trim($_POST['email'] ?? '');
         $testimonial_message = trim($_POST['message'] ?? '');
         $rating = (int)($_POST['rating'] ?? 0);

         // Validasi sederhana
         if (empty($name) || empty($email) || empty($testimonial_message) || $rating < 1 || $rating > 5) {
             $message = '<div class="alert alert-danger">Semua field wajib diisi, dan rating 1-5!</div>';
         } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $message = '<div class="alert alert-danger">Email tidak valid!</div>';
         } else {
             // Insert ke database
             $queryInsert = "INSERT INTO testimonials (name, email, message, rating, is_approved) VALUES (?, ?, ?, ?, 0)";
             $stmtInsert = $pdo->prepare($queryInsert);
             if ($stmtInsert->execute([$name, $email, $testimonial_message, $rating])) {
                 $message = '<div class="alert alert-success">Terima kasih! Testimoni Anda telah dikirim dan akan dimoderasi.</div>';
             } else {
                 $message = '<div class="alert alert-danger">Gagal mengirim testimoni. Coba lagi!</div>';
             }
         }
     }
     

// Query untuk paket unggulan (ambil semua aktif)
$queryPackages = "SELECT * FROM packages WHERE is_active = 1 ORDER BY id DESC LIMIT 3";
$stmtPackages = $pdo->prepare($queryPackages);
$stmtPackages->execute();
$packages = $stmtPackages->fetchAll();

// Query untuk jadwal terdekat (ambil yang available dan departure_date terdekat)
$querySchedule = "SELECT s.*, p.name AS package_name FROM schedules s JOIN packages p ON s.package_id = p.id WHERE s.status = 'available' AND s.departure_date >= CURDATE() ORDER BY s.departure_date ASC LIMIT 1";
$stmtSchedule = $pdo->prepare($querySchedule);
$stmtSchedule->execute();
$nearestSchedule = $stmtSchedule->fetch();

// Query untuk testimoni (5 terbaru yang approved)
$queryTestimonials = "SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 5";
$stmtTestimonials = $pdo->prepare($queryTestimonials);
$stmtTestimonials->execute();
$testimonials = $stmtTestimonials->fetchAll();

// Query untuk galeri (8 foto aktif)
$queryGalleries = "SELECT * FROM galleries WHERE type = 'image' AND is_active = 1 ORDER BY created_at DESC LIMIT 8";
$stmtGalleries = $pdo->prepare($queryGalleries);
$stmtGalleries->execute();
$galleries = $stmtGalleries->fetchAll();

// Query untuk settings (tagline, dll.)
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2', 'contact_phone')";  // Diperbaiki: hanya 2 kolom
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR); // Sekarang OK

// Ambil tagline
$tagline1 = $settings['tagline1'] ?? 'LANGKAH AWAL MENUJU BAITULLAH';
$tagline2 = $settings['tagline2'] ?? 'HARGA HEMAT FASILITAS TERHORMAT';
$whatsapp = $settings['contact_phone'] ?? '+6281234567890';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALFARUQ TEAM - <?php echo $tagline1; ?></title>
    <meta name="description" content="Travel umroh terpercaya dengan harga hemat dan fasilitas terhormat. Pesan paket umroh Anda sekarang!">
    <meta name="keywords" content="umroh, travel umroh, ALFARUQ TEAM, paket umroh, harga hemat">
    <meta name="author" content="ALFARUQ TEAM">
    <!-- Open Graph untuk SEO -->
    <meta property="og:title" content="ALFARUQ TEAM - <?php echo $tagline1; ?>">
    <meta property="og:description" content="Travel umroh terpercaya dengan harga hemat dan fasilitas terhormat.">
    <meta property="og:image" content="assets/img/logo-alfaruq.jpg">
    <meta property="og:url" content="https://alfaruqteam.com">
    <!-- Schema JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "PT. ALFARUQ ANUGERAH UTAMA (ALFARUQ TEAM)",
        "url": "https://alfaruqteam.com",
        "logo": "assets/img/logo-alfaruq.jpg",
        "description": "<?php echo $tagline1; ?> - <?php echo $tagline2; ?>",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "<?php echo $whatsapp; ?>",
            "contactType": "customer service"
        }
    }
    </script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- Font Poppins/Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'views/header.php'; ?>

    <!-- Hero Section -->
    <section id="hero" class="hero-section text-white d-flex align-items-center" style="background: linear-gradient(135deg, #164924 0%, #33a661 100%), url('assets/img/masjid-hero.jpg') no-repeat center center; background-size: cover; height: 100vh;">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3"><?php echo $tagline1; ?></h1>
            <p class="lead mb-4"><?php echo $tagline2; ?></p>
            <a href="packages.php" class="btn btn-warning btn-lg rounded-pill px-4">Lihat Paket Umroh</a>
        </div>
    </section>

    <!-- Paket Unggulan -->
    <section id="packages" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4 text-success fw-bold">Paket Unggulan</h2>
            <div class="row">
                <?php foreach ($packages as $package): ?>
                <div class="col-md-4 mb-4">
                    <div class="card rounded shadow-sm border-0 h-100">
                        <img src="<?php echo $package['image']; ?>" class="card-img-top" alt="<?php echo $package['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title text-success"><?php echo $package['name']; ?></h5>
                            <p class="card-text"><?php echo substr($package['description'], 0, 100) . '...'; ?></p>
                            <p class="text-primary fw-bold">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?> / <?php echo $package['duration']; ?> hari</p>
                            <a href="package-detail.php?id=<?php echo $package['id']; ?>" class="btn btn-success rounded-pill">Detail</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Countdown Keberangkatan -->
    <section id="countdown" class="py-5 bg-success text-white">
        <div class="container text-center">
            <h2 class="mb-4 fw-bold">Keberangkatan Terdekat</h2>
            <?php if ($nearestSchedule): ?>
            <p class="mb-3">Paket: <?php echo $nearestSchedule['package_name']; ?> - Tanggal: <?php echo date('d M Y', strtotime($nearestSchedule['departure_date'])); ?></p>
            <div id="countdown-timer" class="d-flex justify-content-center gap-3">
                <div class="text-center">
                    <span id="days" class="display-4 fw-bold">00</span>
                    <p>Hari</p>
                </div>
                <div class="text-center">
                    <span id="hours" class="display-4 fw-bold">00</span>
                    <p>Jam</p>
                </div>
                <div class="text-center">
                    <span id="minutes" class="display-4 fw-bold">00</span>
                    <p>Menit</p>
                </div>
                <div class="text-center">
                    <span id="seconds" class="display-4 fw-bold">00</span>
                    <p>Detik</p>
                </div>
            </div>
            <?php else: ?>
            <p>Tidak ada jadwal keberangkatan tersedia saat ini.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Testimoni -->
    <section id="testimonials" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4 text-success fw-bold">Testimoni Jamaah</h2>
            <div class="row">
                <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-md-6 mb-4">
                    <div class="card rounded shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?php echo $testimonial['image']; ?>" class="rounded-circle me-3" width="50" height="50" alt="<?php echo $testimonial['name']; ?>">
                                <div>
                                    <h6 class="mb-0"><?php echo $testimonial['name']; ?></h6>
                                    <small class="text-warning"><?php echo str_repeat('★', $testimonial['rating']); ?></small>
                                </div>
                            </div>
                            <p class="card-text"><?php echo $testimonial['message']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

         <!-- Form Input Testimoni -->
     <section id="testimonial-form" class="py-5 bg-light">
         <div class="container">
             <h2 class="text-center mb-4 text-success fw-bold">Berikan Testimoni Anda</h2>
             <?php echo $message; ?> <!-- Tampilkan pesan sukses/error -->
             <form method="POST" action="" class="row g-3 justify-content-center">
                 <div class="col-md-6">
                     <label for="name" class="form-label">Nama</label>
                     <input type="text" class="form-control rounded" id="name" name="name" required>
                 </div>
                 <div class="col-md-6">
                     <label for="email" class="form-label">Email</label>
                     <input type="email" class="form-control rounded" id="email" name="email" required>
                 </div>
                 <div class="col-12">
                     <label for="rating" class="form-label">Rating (1-5 Bintang)</label>
                     <select class="form-select rounded" id="rating" name="rating" required>
                         <option value="">Pilih Rating</option>
                         <option value="1">1 ★</option>
                         <option value="2">2 ★★</option>
                         <option value="3">3 ★★★</option>
                         <option value="4">4 ★★★★</option>
                         <option value="5">5 ★★★★★</option>
                     </select>
                 </div>
                 <div class="col-12">
                     <label for="message" class="form-label">Pesan Testimoni</label>
                     <textarea class="form-control rounded" id="message" name="message" rows="4" required placeholder="Ceritakan pengalaman Anda..."></textarea>
                 </div>
                 <div class="col-12 text-center">
                     <button type="submit" name="submit_testimonial" class="btn btn-success btn-lg rounded-pill px-4">Kirim Testimoni</button>
                 </div>
             </form>
         </div>
     </section>
     

    <!-- Galeri Slider -->
    <section id="gallery" class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-4 text-success fw-bold">Galeri Perjalanan</h2>
            <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($galleries as $index => $gallery): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo $gallery['image']; ?>" class="d-block w-100 rounded" alt="<?php echo $gallery['title']; ?>">
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?php echo $gallery['title']; ?></h5>
                            <p><?php echo $gallery['description']; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
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
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>?text=Halo%20ALFARUQ%20TEAM,%20saya%20ingin%20konsultasi%20paket%20umroh" class="btn btn-success btn-lg rounded-pill px-4" target="_blank">Chat WhatsApp</a>
        </div>
    </section>

    <?php include 'views/footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS untuk Countdown -->
    <script>
        <?php if ($nearestSchedule): ?>
        const targetDate = new Date('<?php echo $nearestSchedule['departure_date']; ?>').getTime();
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDate - now;
            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                document.getElementById('days').innerText = days.toString().padStart(2, '0');
                document.getElementById('hours').innerText = hours.toString().padStart(2, '0');
                document.getElementById('minutes').innerText = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').innerText = seconds.toString().padStart(2, '0');
            }
        }
        setInterval(updateCountdown, 1000);
        updateCountdown();
        <?php endif; ?>
    </script>
</body>
</html>