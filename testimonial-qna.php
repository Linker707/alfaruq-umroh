<?php
// testimonial-qna.php - Halaman QnA lengkap untuk testimonial, ambil data awal dari session, simpan semua jawaban ke DB
session_start(); // Mulai session untuk ambil data awal dari index.php
require_once 'config/database.php'; // Koneksi DB

// Ambil data awal dari session (dari form di index.php)
$testimonial = $_SESSION['testimonial'] ?? null;
if (!$testimonial) {
    // Jika tidak ada data, redirect kembali ke index
    header('Location: index.php');
    exit;
}

$message = ''; // Pesan sukses/error

// Handle submit QnA lengkap
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_qna'])) {
    // Ambil jawaban dari form (q1 sampai q23)
    $qnaAnswers = [];
    for ($i = 1; $i <= 23; $i++) {
        $qnaAnswers["q$i"] = trim($_POST["q$i"] ?? '');
    }
    
    // Validasi: Pastikan semua field terisi
    $required = ['q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7', 'q8', 'q9', 'q10', 'q11', 'q12', 'q13', 'q14', 'q15', 'q16', 'q17', 'q18', 'q19', 'q20', 'q21', 'q22', 'q23'];
    $valid = true;
    foreach ($required as $q) {
        if (empty($qnaAnswers[$q])) {
            $valid = false;
            break;
        }
    }
    
    if (!$valid) {
        $message = 'Semua pertanyaan wajib diisi!';
    } else {
        // Simpan ke DB: Pisah ke kolom q1-q23, message dari q23, image default, is_approved = 1
        $queryInsert = "INSERT INTO testimonials (name, email, rating, message, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, q11, q12, q13, q14, q15, q16, q17, q18, q19, q20, q21, q22, q23, image, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'assets/img/comment-photo.jpg', 1)";
        $stmtInsert = $pdo->prepare($queryInsert);
        if ($stmtInsert->execute([
            $testimonial['name'], $testimonial['email'], $testimonial['rating'], $qnaAnswers['q23'],  // message dari q23
            $qnaAnswers['q1'], $qnaAnswers['q2'], $qnaAnswers['q3'], $qnaAnswers['q4'], $qnaAnswers['q5'],
            $qnaAnswers['q6'], $qnaAnswers['q7'], $qnaAnswers['q8'], $qnaAnswers['q9'], $qnaAnswers['q10'],
            $qnaAnswers['q11'], $qnaAnswers['q12'], $qnaAnswers['q13'], $qnaAnswers['q14'], $qnaAnswers['q15'],
            $qnaAnswers['q16'], $qnaAnswers['q17'], $qnaAnswers['q18'], $qnaAnswers['q19'], $qnaAnswers['q20'],
            $qnaAnswers['q21'], $qnaAnswers['q22'], $qnaAnswers['q23']
        ])) {
            // Set pesan sukses ke session dan redirect ke index.php
            $_SESSION['success_message'] = 'Jazakillah Khairan Katsiiran Telah Mengisi Kuisioner';
            unset($_SESSION['testimonial']); // Hapus session testimonial
            header('Location: index.php'); // Redirect ke index
            exit;
        } else {
            $message = 'Gagal mengirim. Coba lagi.';
        }
    }
}

// Ambil settings untuk tagline
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);
$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
?>
<?php include 'views/header.php'; ?>

<!-- Hero Section -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">QnA Testimoni Lengkap</h1>
        <p class="lead"><?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?></p>
    </div>
</section>

<!-- Section QnA Form - Form panjang dengan semua 23 pertanyaan -->
<section id="qna-form" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 text-success fw-bold">Jawab Pertanyaan Berikut untuk Testimoni Lengkap</h2>
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Terima kasih') !== false ? 'alert-success' : 'alert-danger'; ?> text-center rounded-pill">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card rounded shadow-sm border-0 p-4" style="border: 3px solid #33a661 !important;">
                    <form method="POST" action="testimonial-qna.php">
                        <!-- Pertanyaan 1-5: Data pribadi dan awal -->
                        <h5 class="text-success fw-bold mb-3">Data Pribadi</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">1. Nama Lengkap</label>
                            <input type="text" class="form-control rounded-pill" name="q1" value="<?php echo htmlspecialchars($testimonial['name']); ?>" placeholder="Masukkan nama lengkap Anda" style="border: 2px solid #33a661;" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">2. Nomor Handphone</label>
                            <input type="tel" class="form-control rounded-pill" name="q2" placeholder="Masukkan nomor handphone aktif" style="border: 2px solid #33a661;" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">3. Dengan siapa Anda berangkat Umroh?</label>
                            <input type="text" class="form-control rounded-pill" name="q3" placeholder="Contoh: Sendiri, keluarga, rombongan kantor, dll" style="border: 2px solid #33a661;" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">4. Darimana Anda mengetahui Alfaruq Team?</label>
                            <div>
                                <input type="radio" name="q4" value="Media sosial" required> Media sosial<br>
                                <input type="radio" name="q4" value="Teman atau keluarga"> Teman atau keluarga<br>
                                <input type="radio" name="q4" value="Website"> Website<br>
                                <input type="radio" name="q4" value="Rekanan travel"> Rekanan travel<br>
                                <input type="radio" name="q4" value="Lainnya"> Lainnya: <input type="text" name="q4_other" placeholder="Jelaskan" style="border: 1px solid #33a661; margin-left: 10px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">5. Apa alasan utama Anda memilih Alfaruq Team?</label>
                            <textarea class="form-control rounded" name="q5" rows="3" placeholder="Jelaskan alasan Anda memilih Alfaruq Team" style="border: 2px solid #33a661;" required></textarea>
                        </div>

                        <!-- Pertanyaan 6-19: Penilaian pelayanan -->
                        <h5 class="text-success fw-bold mb-3 mt-5">Penilaian Pelayanan</h5>
                        <?php for ($i = 6; $i <= 19; $i += 2): // Loop untuk radio + textarea ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-success"><?php echo $i; ?>. <?php echo $i == 6 ? 'Penilaian Anda terhadap pelayanan Alfaruq Team dalam pemberian informasi sebelum keberangkatan.' : ($i == 8 ? 'Penilaian Anda terhadap pelayanan dan penyampaian informasi saat manasik.' : ($i == 10 ? 'Penilaian Anda terhadap pelayanan Alfaruq Team selama tour berlangsung.' : ($i == 12 ? 'Penilaian Anda terhadap makanan yang disajikan selama program Umroh.' : ($i == 14 ? 'Penilaian Anda terhadap pembimbingan ibadah atau Tour Leader.' : ($i == 16 ? 'Penilaian Anda terhadap pelayanan Muthawif atau Guide Local.' : 'Penilaian Anda terhadap itinerary program Umroh.'))))); ?></label>
                                <div>
                                    <input type="radio" name="q<?php echo $i; ?>" value="Sangat puas" required> Sangat puas<br>
                                    <input type="radio" name="q<?php echo $i; ?>" value="Puas"> Puas<br>
                                    <input type="radio" name="q<?php echo $i; ?>" value="Cukup puas"> Cukup puas<br>
                                    <input type="radio" name="q<?php echo $i; ?>" value="Kurang puas"> Kurang puas<br>
                                    <input type="radio" name="q<?php echo $i; ?>" value="Sangat tidak puas"> Sangat tidak puas
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-success"><?php echo $i + 1; ?>. Jelaskan alasan Anda memilih penilaian tersebut.</label>
                                <textarea class="form-control rounded" name="q<?php echo $i + 1; ?>" rows="3" style="border: 2px solid #33a661;" required></textarea>
                            </div>
                        <?php endfor; ?>

                        <!-- Pertanyaan 20-23: Kesimpulan -->
                        <h5 class="text-success fw-bold mb-3 mt-5">Kesimpulan</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">20. Apakah Anda berminat memilih Alfaruq Team untuk perjalanan Umroh selanjutnya?</label>
                            <div>
                                <input type="radio" name="q20" value="Ya" required> Ya<br>
                                <input type="radio" name="q20" value="Tidak"> Tidak
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">21. Jelaskan alasan Anda memilih jawaban tersebut.</label>
                            <textarea class="form-control rounded" name="q21" rows="3" style="border: 2px solid #33a661;" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">22. Berikan kritik dan saran Anda mengenai keseluruhan perjalanan Umroh atau tour yang kami selenggarakan.</label>
                            <textarea class="form-control rounded" name="q22" rows="3" style="border: 2px solid #33a661;" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">23. Bagaimana kesan Anda secara keseluruhan terhadap perjalanan Umroh atau tour bersama Alfaruq Team?</label>
                            <textarea class="form-control rounded" name="q23" rows="3" style="border: 2px solid #33a661;" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="submit_qna" class="btn btn-success btn-lg rounded-pill px-5">Kirim Testimoni Lengkap</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'views/footer.php'; ?>