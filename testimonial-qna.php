<?php
session_start();
require_once 'config/database.php';

$testimonial = $_SESSION['testimonial'] ?? null;
if (!$testimonial) {
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_qna'])) {
    $qnaAnswers = [];
    for ($i = 1; $i <= 23; $i++) {
        $qnaAnswers["q$i"] = trim($_POST["q$i"] ?? '');
    }
    
    $required = ['q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7', 'q8', 'q9', 'q10', 'q11', 'q12', 'q13', 'q14', 'q15', 'q16', 'q17', 'q18', 'q19', 'q20', 'q21', 'q22', 'q23'];
    $valid = true;
    foreach ($required as $q) {
        if (empty($qnaAnswers[$q])) {
            $valid = false;
            break;
        }
    }
    
    $departureDay = trim($_POST['departure_day'] ?? '');
    $departureMonth = trim($_POST['departure_month'] ?? '');
    $departureYear = trim($_POST['departure_year'] ?? '');
    if (empty($departureDay) || empty($departureMonth) || empty($departureYear)) {
        $valid = false;
    }
    
    if (!$valid) {
        $message = '<div class="alert alert-danger rounded-pill text-center">Semua pertanyaan dan tanggal keberangkatan wajib diisi!</div>';
    } else {
        $departureDate = $departureYear . '-' . $departureMonth . '-' . $departureDay;
        
        $queryInsert = "INSERT INTO testimonials (name, email, rating, message, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, q11, q12, q13, q14, q15, q16, q17, q18, q19, q20, q21, q22, q23, departure_date, image, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'assets/img/comment-photo.jpg', 1)";
        $stmtInsert = $pdo->prepare($queryInsert);
        if ($stmtInsert->execute([
            $testimonial['name'], $testimonial['email'], $testimonial['rating'], $qnaAnswers['q23'],
            $qnaAnswers['q1'], $qnaAnswers['q2'], $qnaAnswers['q3'], $qnaAnswers['q4'], $qnaAnswers['q5'],
            $qnaAnswers['q6'], $qnaAnswers['q7'], $qnaAnswers['q8'], $qnaAnswers['q9'], $qnaAnswers['q10'],
            $qnaAnswers['q11'], $qnaAnswers['q12'], $qnaAnswers['q13'], $qnaAnswers['q14'], $qnaAnswers['q15'],
            $qnaAnswers['q16'], $qnaAnswers['q17'], $qnaAnswers['q18'], $qnaAnswers['q19'], $qnaAnswers['q20'],
            $qnaAnswers['q21'], $qnaAnswers['q22'], $qnaAnswers['q23'], $departureDate
        ])) {
            $_SESSION['success_message'] = 'Jazakillah Khairan Katsiiran Telah Mengisi Kuisioner';
            unset($_SESSION['testimonial']);
            header('Location: index.php');
            exit;
        } else {
            $message = '<div class="alert alert-danger rounded-pill text-center">Gagal mengirim. Coba lagi.</div>';
        }
    }
}

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
        <h1 class="display-4 fw-bold text-white">QnA Testimoni Lengkap</h1>
        <p class="lead text-white opacity-90"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section QnA Form - Style modern -->
<section id="qna-form" class="py-5 bg-green-50">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-green-900 mb-3">Jawab Pertanyaan Berikut untuk Testimoni Lengkap</h2>
            <p class="lead text-neutral-700 mb-0">Bantu kami meningkatkan pelayanan dengan memberikan feedback Anda</p>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card-modern-green-light">
                    <div class="card-body-modern">
                        <form method="POST" action="testimonial-qna.php" id="qnaForm">
                            <!-- Pertanyaan 1-5: Data pribadi dan awal -->
                            <h5 class="text-green-800 fw-bold mb-3 border-bottom pb-2">
                                <i class="fas fa-user-circle me-2"></i>Data Pribadi
                            </h5>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-modern fw-semibold">1. Nama Lengkap</label>
                                    <input type="text" class="form-control-modern" name="q1" 
                                           value="<?php echo htmlspecialchars($testimonial['name']); ?>" 
                                           placeholder="Masukkan nama lengkap Anda" readonly>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label-modern fw-semibold">2. Nomor Handphone</label>
                                    <input type="tel" class="form-control-modern" name="q2" 
                                           placeholder="Masukkan nomor handphone aktif" required>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label-modern fw-semibold">3. Dengan siapa Anda berangkat Umroh?</label>
                                    <input type="text" class="form-control-modern" name="q3" 
                                           placeholder="Contoh: Sendiri, keluarga, rombongan kantor, dll" required>
                                </div>
                                
                                <!-- Tanggal Keberangkatan -->
                                <div class="col-12">
                                    <label class="form-label-modern fw-semibold">3a. Tanggal Keberangkatan</label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <select class="form-control-modern" name="departure_day" required>
                                                <option value="">Tanggal</option>
                                                <?php for ($i = 1; $i <= 31; $i++) { ?>
                                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo $i; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-control-modern" name="departure_month" required>
                                                <option value="">Bulan</option>
                                                <option value="01">Januari</option>
                                                <option value="02">Februari</option>
                                                <option value="03">Maret</option>
                                                <option value="04">April</option>
                                                <option value="05">Mei</option>
                                                <option value="06">Juni</option>
                                                <option value="07">Juli</option>
                                                <option value="08">Agustus</option>
                                                <option value="09">September</option>
                                                <option value="10">Oktober</option>
                                                <option value="11">November</option>
                                                <option value="12">Desember</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-control-modern" name="departure_year" required>
                                                <option value="">Tahun</option>
                                                <?php for ($i = 2020; $i <= 2030; $i++) { ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label-modern fw-semibold">4. Darimana Anda mengetahui Alfaruq Team?</label>
                                    <div class="row g-2">
                                        <?php 
                                        $sources = [
                                            'Media sosial' => 'Media sosial',
                                            'Teman atau keluarga' => 'Teman atau keluarga',
                                            'Website' => 'Website',
                                            'Rekanan travel' => 'Rekanan travel',
                                            'Lainnya' => 'Lainnya'
                                        ];
                                        foreach ($sources as $value => $label) { ?>
                                        <div class="col-md-3">
                                            <div class="form-check-modern">
                                                <input class="form-check-input" type="radio" name="q4" 
                                                       value="<?php echo htmlspecialchars($value); ?>" id="q4_<?php echo htmlspecialchars($value); ?>" required>
                                                <label class="form-check-label" for="q4_<?php echo htmlspecialchars($value); ?>">
                                                    <?php echo htmlspecialchars($label); ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label-modern fw-semibold">5. Apa alasan utama Anda memilih Alfaruq Team?</label>
                                    <textarea class="form-control-modern" name="q5" rows="3" 
                                              placeholder="Jelaskan alasan Anda memilih Alfaruq Team" required></textarea>
                                </div>
                            </div>

                            <!-- Pertanyaan 6-19: Penilaian pelayanan -->
                            <h5 class="text-green-800 fw-bold mb-3 mt-5 border-bottom pb-2">
                                <i class="fas fa-star me-2"></i>Penilaian Pelayanan
                            </h5>
                            
                            <?php for ($i = 6; $i <= 19; $i += 2) { ?>
                                <div class="mb-4">
                                    <label class="form-label-modern fw-semibold">
                                        <?php echo $i; ?>. 
                                        <?php 
                                        $questions = [
                                            6 => 'Penilaian Anda terhadap pelayanan Alfaruq Team dalam pemberian informasi sebelum keberangkatan.',
                                            8 => 'Penilaian Anda terhadap pelayanan dan penyampaian informasi saat manasik.',
                                            10 => 'Penilaian Anda terhadap pelayanan Alfaruq Team selama tour berlangsung.',
                                            12 => 'Penilaian Anda terhadap makanan yang disajikan selama program Umroh.',
                                            14 => 'Penilaian Anda terhadap pembimbingan ibadah atau Tour Leader.',
                                            16 => 'Penilaian Anda terhadap pelayanan Muthawif atau Guide Local.',
                                            18 => 'Penilaian Anda terhadap itinerary program Umroh.'
                                        ];
                                        echo $questions[$i] ?? '';
                                        ?>
                                    </label>
                                    
                                    <div class="row g-2 mb-3">
                                        <?php 
                                        $ratings = [
                                            'Sangat puas' => 'Sangat puas',
                                            'Puas' => 'Puas',
                                            'Cukup puas' => 'Cukup puas',
                                            'Kurang puas' => 'Kurang puas',
                                            'Sangat tidak puas' => 'Sangat tidak puas'
                                        ];
                                        foreach ($ratings as $value => $label) { ?>
                                            <div class="col-md-3 col-6">
                                                <div class="form-check-modern">
                                                    <input class="form-check-input" type="radio" name="q<?php echo $i; ?>" 
                                                           value="<?php echo htmlspecialchars($value); ?>" id="q<?php echo $i; ?>_<?php echo htmlspecialchars($value); ?>" required>
                                                    <label class="form-check-label" for="q<?php echo $i; ?>_<?php echo htmlspecialchars($value); ?>">
                                                        <?php echo htmlspecialchars($label); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label-modern fw-semibold">
                                            <?php echo $i + 1; ?>. Jelaskan alasan Anda memilih penilaian tersebut.
                                        </label>
                                        <textarea class="form-control-modern" name="q<?php echo $i + 1; ?>" rows="3" required></textarea>
                                    </div>
                                </div>
                            <?php } ?>

                            <!-- Pertanyaan 20-23: Kesimpulan -->
                            <h5 class="text-green-800 fw-bold mb-3 mt-5 border-bottom pb-2">
                                <i class="fas fa-check-circle me-2"></i>Kesimpulan
                            </h5>
                            
                            <div class="mb-4">
                                <label class="form-label-modern fw-semibold">20. Apakah Anda berminat memilih Alfaruq Team untuk perjalanan Umroh selanjutnya?</label>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <div class="form-check-modern">
                                            <input class="form-check-input" type="radio" name="q20" value="Ya" id="q20_ya" required>
                                            <label class="form-check-label" for="q20_ya">Ya</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check-modern">
                                            <input class="form-check-input" type="radio" name="q20" value="Tidak" id="q20_tidak">
                                            <label class="form-check-label" for="q20_tidak">Tidak</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label-modern fw-semibold">21. Jelaskan alasan Anda memilih jawaban tersebut.</label>
                                <textarea class="form-control-modern" name="q21" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label-modern fw-semibold">22. Berikan kritik dan saran Anda mengenai keseluruhan perjalanan Umroh atau tour yang kami selenggarakan.</label>
                                <textarea class="form-control-modern" name="q22" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label-modern fw-semibold">23. Bagaimana kesan Anda secara keseluruhan terhadap perjalanan Umroh atau tour bersama Alfaruq Team?</label>
                                <textarea class="form-control-modern" name="q23" rows="3" required></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="text-center mt-5">
                                <button type="submit" name="submit_qna" class="btn-modern-green primary lg with-icon">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Testimoni Lengkap
                                </button>
                                <p class="text-neutral-600 mt-3">
                                    <i class="fas fa-info-circle text-green-600 me-1"></i>
                                    Testimoni Anda akan dipublikasikan setelah melalui proses moderasi.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section - Style modern -->
<section id="cta-testimonial" class="py-5 bg-green-gradient">
    <div class="container text-center">
        <h2 class="text-white mb-3 fw-bold">Terima Kasih atas Partisipasi Anda</h2>
        <p class="text-white opacity-90 mb-4">
            Testimoni Anda sangat berharga bagi kami untuk meningkatkan kualitas pelayanan.
        </p>
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
            <a href="index.php#testimonials" class="btn-modern-green accent with-icon">
                <i class="fas fa-eye me-2"></i>Lihat Testimoni Lainnya
            </a>
            <a href="index.php" class="btn-modern-green outline text-white border-white">
                <i class="fas fa-home me-2"></i>Kembali ke Home
            </a>
        </div>
    </div>
</section>

<script src="js/form-validation.js"></script>

<?php include 'views/footer.php'; ?>