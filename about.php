<?php
// about.php - Halaman tentang perusahaan ALFARUQ TEAM, menampilkan informasi perusahaan, visi misi, dan legalitas PPIU
require_once 'config/database.php'; // Memuat file koneksi database PDO untuk interaksi dengan MySQL

// Ambil data settings yang relevan untuk halaman about (nama perusahaan, deskripsi, visi, misi, legalitas PPIU)
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('company_name', 'company_description', 'vision', 'mission', 'legal_ppiu', 'tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings); // Prepare statement untuk keamanan
$stmtSettings->execute(); // Eksekusi query
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR); // Ambil sebagai array key-value untuk akses mudah

// Fallback jika data tidak ada di settings (untuk menghindari error)
$companyName = $settings['company_name'] ?? 'PT. ALFARUQ ANUGERAH UTAMA (ALFARUQ TEAM)';
$companyDescription = $settings['company_description'] ?? 'Perusahaan travel umroh terpercaya dengan pengalaman lebih dari 10 tahun.';
$vision = $settings['vision'] ?? 'Menjadi travel umroh terdepan dalam memberikan pengalaman spiritual yang berkesan dan aman bagi setiap jamaah.';
$mission = $settings['mission'] ?? 'Menyediakan layanan umroh berkualitas tinggi dengan harga terjangkau, fasilitas lengkap, dan pendampingan profesional.';
$legalPpiu = $settings['legal_ppiu'] ?? 'Nomor Izin PPIU: 123/ABC/2023 - Dikeluarkan oleh Kementerian Agama RI.';
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
?>
<?php include 'views/header.php'; // Include file header (navbar, meta tags, dll.) ?>

<!-- Hero Section Kecil - Menggunakan tagline untuk konsistensi -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Tentang Kami</h1>
        <p class="lead"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section Tentang Perusahaan - Menampilkan nama dan deskripsi perusahaan -->
<section id="about-company" class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <!-- Kolom Teks -->
            <div class="col-lg-6 mb-4">
                <h2 class="text-success fw-bold mb-3"><?php echo htmlspecialchars($companyName); ?></h2>
                <p class="text-muted"><?php echo htmlspecialchars($companyDescription); ?></p>
                <p class="text-muted">Sejak berdiri, kami telah melayani ribuan jamaah dengan komitmen untuk memberikan pengalaman umroh yang aman, nyaman, dan penuh berkah. Dengan tim profesional dan jaringan mitra terpercaya, kami siap mendampingi perjalanan ibadah Anda.</p>
            </div>
            <!-- Kolom Gambar (Placeholder - ganti dengan gambar perusahaan jika ada) -->
            <div class="col-lg-6">
                <img src="assets/img/logo.svg" class="img-fluid rounded shadow" alt="Tentang ALFARUQ TEAM" style="width: 100%; height: auto; max-height: 400px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<!-- Section Visi dan Misi - Menggunakan cards untuk tampilan yang menarik -->
<section id="vision-mission" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-4 text-success fw-bold">Visi & Misi Kami</h2>
        <div class="row">
            <!-- Card Visi -->
            <div class="col-md-6 mb-4">
                <div class="card rounded shadow-sm border-0 h-100" style="background-color: #f8f9fa;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Visi</h5>
                        <p class="card-text"><?php echo htmlspecialchars($vision); ?></p>
                    </div>
                </div>
            </div>
            <!-- Card Misi -->
            <div class="col-md-6 mb-4">
                <div class="card rounded shadow-sm border-0 h-100" style="background-color: #f8f9fa;">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Misi</h5>
                        <p class="card-text"><?php echo htmlspecialchars($mission); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Legalitas PPIU - Menampilkan informasi legal untuk kepercayaan -->
<section id="legal" class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="text-center mb-4 text-success fw-bold">Legalitas & Sertifikasi</h2>
        <div class="card rounded shadow-sm border-0 mx-auto" style="max-width: 600px; background-color: #ffffff;">
            <div class="card-body">
                <h5 class="card-title text-success fw-bold">Izin PPIU (Penyelenggara Perjalanan Ibadah Umroh)</h5>
                <p class="card-text"><?php echo htmlspecialchars($legalPpiu); ?></p>
                <p class="text-muted">Kami berkomitmen untuk mematuhi semua regulasi dari Kementerian Agama RI dan memberikan layanan yang legal serta terpercaya.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section - Call to action untuk hubungi atau lihat paket -->
<section id="cta-about" class="py-5 bg-warning text-dark text-center">
    <div class="container">
        <h2 class="mb-3 fw-bold">Ingin Tahu Lebih Lanjut?</h2>
        <p class="mb-4">Hubungi kami untuk konsultasi gratis atau lihat paket umroh kami.</p>
        <a href="contact.php" class="btn btn-success btn-lg rounded-pill px-4 me-2">Hubungi Kami</a>
        <a href="packages.php" class="btn btn-outline-success btn-lg rounded-pill px-4">Lihat Paket</a>
    </div>
</section>

<?php include 'views/footer.php'; // Include file footer (script, dll.) ?>
