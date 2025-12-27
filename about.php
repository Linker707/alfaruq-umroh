<?php
// about.php - Hanya update style CSS
require_once 'config/database.php';

$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('company_name', 'company_description', 'vision', 'mission', 'legal_ppiu', 'tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);

$companyName = $settings['company_name'] ?? 'PT. ALFARUQ ANUGERAH UTAMA (ALFARUQ TEAM)';
$companyDescription = $settings['company_description'] ?? 'Perusahaan travel umroh terpercaya dengan ribuan jamaah yang telah berangkat.';
$vision = $settings['vision'] ?? 'Menjadi travel umroh terdepan dalam memberikan pengalaman spiritual yang berkesan dan aman bagi setiap jamaah.';
$mission = $settings['mission'] ?? 'Menyediakan layanan umroh berkualitas tinggi dengan harga terjangkau, fasilitas lengkap, dan pendampingan profesional.';
$legalPpiu = $settings['legal_ppiu'] ?? 'Nomor Izin SK PPIU NO. 24022300153650007 - Dikeluarkan oleh Kementerian Agama RI.';
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
?>
<?php include 'views/header.php'; ?>

<!-- Hero Section Kecil - Menggunakan class modern -->
<section class="py-5 bg-green-gradient text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold text-white">Tentang Kami</h1>
        <p class="lead text-white opacity-90"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section Tentang Perusahaan - Style modern -->
<section id="about-company" class="py-5 bg-green-50">
    <div class="container">
        <div class="row align-items-center">
            <!-- Kolom Teks -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="text-green-gradient mb-3"><?php echo htmlspecialchars($companyName); ?></h2>
                <p class="text-neutral-700 mb-4"><?php echo htmlspecialchars($companyDescription); ?></p>
                <p class="text-neutral-700 mb-4">PT. ALFARUQ ANUGERAH UTAMA didirikan di Kota Tanjungpinang,
                    Provinsi Kepulauan Riau pada hari rabu tanggal 24 Rajab 1444 H
                    oleh bapak Antoni Saputra dan ibu Vira Aputrima Hara dengan
                    nama “ALFARUQ” yang terinspirasi dari makna Alfaruq itu sendiri.
                    Adapun makna dari Alfaruq adalah pembeda yang hak (benar)
                    dan yang bathil (tidak benar), nama yang diberikan oleh Allah
                    SWT kepada sahabat Umar Bin Khatab R.A, Insya Allah dengan
                    nama Alfaruq harapannya perusahaan ini dapat memberikan
                    edukasi dan pelayanan terbaik untuk semua umat, sesuai
                    dengan Mottonya “Memberikan Pelayanan Terbaik Dengan
                    Sepenuh Hati Sesuai Dengan Tuntunan Rasulullah SAW”.</p>
            </div>
            <!-- Kolom Gambar -->
            <div class="col-lg-6">
                <div class="card-modern-green-light">
                    <img src="assets/img/logo.svg" class="card-img-modern" alt="Tentang ALFARUQ TEAM" style="object-fit: contain; padding: 2rem; background: var(--green-50);">
                    <div class="card-body-modern text-center">
                        <h5 class="card-title-modern text-green-900">ALFARUQ TEAM</h5>
                        <p class="card-text-modern text-neutral-600">Harga Hemat Fasilitas Terhormat</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Visi dan Misi - Cards modern -->
<section id="vision-mission" class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-green-900 mb-3">Visi & Misi Kami</h2>
            <p class="lead text-neutral-700 mb-0">Menjadi penyelenggara tour wisata halal dan ibadah umroh
yang berkomitmen dalam pelayanan yang profesional.</p>
        </div>
        
        <div class="row">
            <!-- Card Visi -->
            <div class="col-md-6 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern text-center d-flex flex-column">
                        <div class="icon-box-modern mx-auto mb-4">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4 class="text-green-900 mb-3">Visi</h4>
                        <div class="vision-list text-start flex-grow-1">
                            <?php
                            // Misalnya data vision dipisah dengan baris baru
                            $vision_points = explode("\n", $vision);
                            echo '<ul class="list-unstyled">';
                            foreach ($vision_points as $point) {
                                if (trim($point) !== '') {
                                    echo '<li class="mb-2">';
                                    echo '<i class="fas fa-star text-green-700 me-2"></i>';
                                    echo htmlspecialchars(trim($point));
                                    echo '</li>';
                                }
                            }
                            echo '</ul>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card Misi -->
           <div class="col-md-6 mb-4">
                <div class="card-modern-green-light h-100">
                    <div class="card-body-modern text-center d-flex flex-column">
                        <div class="icon-box-modern mx-auto mb-4">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h4 class="text-green-900 mb-3">Misi</h4>
                        <div class="mission-list text-start flex-grow-1">
                            <?php
                            // Misalnya data mission dipisah dengan baris baru
                            $mission_points = explode("\n", $mission);
                            echo '<ul class="list-unstyled">';
                            foreach ($mission_points as $point) {
                                if (trim($point) !== '') {
                                    echo '<li class="mb-2">';
                                    echo '<i class="fas fa-check-circle text-green-700 me-2"></i>';
                                    echo htmlspecialchars(trim($point));
                                    echo '</li>';
                                }
                            }
                            echo '</ul>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Section Legalitas PPIU -->
<section id="legal" class="py-5 bg-green-50">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-green-900 mb-3">Legalitas & Sertifikasi</h2>
            <p class="lead text-neutral-700 mb-0">Beroperasi dengan izin resmi dan standar terbaik</p>
        </div>
        <div class="card-modern-green-light">
            <div class="card-body-modern">
                <div class="text-center mb-4">
                    <div class="icon-box-modern mx-auto mb-4">
                        <i class="fas fa-file-certificate"></i>
                        <div class="certificate-container">
                        <div class="certificate-row">
                            <div class="certificate-card">
                                <div class="certificate-image">
                                    <img src="assets/img/ppiu-certificate.png" alt="Sertifikat PPIU Resmi" class="certificate-img">
                                    <div class="certificate-overlay">
                                        <a href="assets/img/ppiu-certificate.png" class="view-certificate" target="_blank">
                                            <i class="fas fa-search-plus"></i> Lihat Detail
                                        </a>
                                    </div>
                                </div>
                                <div class="certificate-label">Sertifikat PPIU Resmi</div>
                            </div>
                            
                            <div class="certificate-card">
                                <div class="certificate-image">
                                    <img src="assets/img/bpw-certificate.png" alt="Sertifikat BPW Resmi" class="certificate-img">
                                    <div class="certificate-overlay">
                                        <a href="assets/img/bpw-certificate.png" class="view-certificate" target="_blank">
                                            <i class="fas fa-search-plus"></i> Lihat Detail
                                        </a>
                                    </div>
                                </div>
                                <div class="certificate-label">Sertifikat BPW Resmi</div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <h4 class="text-green-900 mb-3">Izin PPIU Resmi</h4>
                </div>
                
                <div class="legal-info-modern">
                    <div class="d-flex align-items-start mb-4">
                        <i class="fas fa-check-circle text-green-600 mt-1 me-3"></i>
                        <div>
                            <h6 class="text-green-800 mb-2"><?php echo htmlspecialchars($legalPpiu); ?></h6>
                            <p class="text-neutral-700 mb-0">Izin resmi dari Kementerian Agama Republik Indonesia sebagai Penyelenggara Perjalanan Ibadah Umroh (PPIU).</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 me-3"></i>
                        <div>
                            <h6 class="text-green-800 mb-2">Terdaftar & Diawasi</h6>
                            <p class="text-neutral-700 mb-0">Kami berkomitmen untuk mematuhi semua regulasi dari Kementerian Agama RI dan memberikan layanan yang legal serta terpercaya.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section - Style modern -->
<section id="cta-about" class="py-5 bg-green-gradient">
    <div class="container text-center">
        <h2 class="text-white mb-3 fw-bold">Ingin Tahu Lebih Lanjut?</h2>
        <p class="text-white opacity-90 mb-4">Hubungi kami untuk konsultasi gratis atau lihat paket umroh kami.</p>
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
            <a href="contact.php" class="btn-modern-green accent with-icon">
                <i class="fas fa-phone-alt me-2"></i>Hubungi Kami
            </a>
            <a href="packages.php" class="btn-modern-green outline text-white border-white">
                <i class="fas fa-box-open me-2"></i>Lihat Paket
            </a>
        </div>
    </div>
</section>

<?php include 'views/footer.php'; ?>