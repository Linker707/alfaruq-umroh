-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 08:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alfaruq_umroh`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `content`, `author`, `image`, `slug`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Panduan Persiapan Umroh', 'Artikel lengkap tentang persiapan fisik, spiritual, dan dokumen untuk umroh.', 'Admin ALFARUQ', 'assets/img/artikel-persiapan.jpg', 'panduan-persiapan-umroh', 1, '2025-11-19 09:17:03', '2025-11-19 09:17:03'),
(2, 'Tips Hemat Saat Umroh', 'Cara menghemat biaya tanpa mengurangi kualitas ibadah.', 'Admin ALFARUQ', 'assets/img/artikel-hemat.jpg', 'tips-hemat-umroh', 1, '2025-11-19 09:17:03', '2025-11-19 09:17:03'),
(3, 'Pengalaman Jamaah ALFARUQ TEAM', 'Cerita inspiratif dari jamaah yang telah berumroh.', 'Admin ALFARUQ', 'assets/img/artikel-pengalaman.jpg', 'pengalaman-jamaah-alfaruq', 1, '2025-11-19 09:17:03', '2025-11-19 09:17:03');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `galleries`
--

CREATE TABLE `galleries` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `type` enum('image','video') DEFAULT 'image',
  `destination` enum('makkah','madinah','thaif','turki') DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `galleries`
--

INSERT INTO `galleries` (`id`, `title`, `description`, `image`, `type`, `destination`, `is_active`, `created_at`) VALUES
(1, 'Keberangkatan dari Jakarta', 'Moment keberangkatan jamaah umroh dari bandara.', 'assets/img/galeri-keberangkatan.jpg', 'image', NULL, 1, '2025-11-19 09:17:03'),
(2, 'Hotel di Mekkah', 'Fasilitas hotel mewah untuk istirahat jamaah.', 'assets/img/galeri-hotel-mekkah.jpg', 'image', NULL, 1, '2025-11-19 09:17:03'),
(3, 'Sholat di Masjidil Haram', 'Jamaah melaksanakan sholat di Masjidil Haram.', 'assets/img/galeri-sholat-haram.jpg', 'image', NULL, 1, '2025-11-19 09:17:03'),
(4, 'Ziarah di Madinah', 'Kunjungan ke makam Rasulullah SAW.', 'assets/img/galeri-ziarah-madinah.jpg', 'image', NULL, 1, '2025-11-19 09:17:03'),
(5, 'Makan Bersama', 'Momen makan bersama jamaah.', 'assets/img/galeri-makan.jpg', 'image', NULL, 1, '2025-11-19 09:17:03'),
(6, 'Video Perjalanan', 'Rekaman video perjalanan umroh.', 'assets/img/galeri-video.mp4', 'video', NULL, 1, '2025-11-19 09:17:03'),
(7, 'Grup Jamaah', 'Foto bersama seluruh jamaah.', 'assets/img/galeri-grup.jpg', 'image', NULL, 1, '2025-11-19 09:17:03'),
(8, 'Sampai di Tanah Air', 'Kembali dengan penuh berkah.', 'assets/img/galeri-kembali.jpg', 'image', NULL, 1, '2025-11-19 09:17:03'),
(9, 'Keberangkatan ke Makkah', 'Moment keberangkatan jamaah menuju kota suci Makkah.', 'assets/img/gallery1.jpg', 'image', 'makkah', 1, '2025-11-24 07:57:18'),
(10, 'Sholat di Masjidil Haram', 'Jamaah melaksanakan sholat di Masjidil Haram, Makkah.', 'assets/img/gallery2.jpg', 'image', 'makkah', 1, '2025-11-24 07:57:18'),
(11, 'Ziarah di Madinah', 'Kunjungan ke makam Rasulullah di Madinah.', 'assets/img/gallery3.jpg', 'image', 'madinah', 1, '2025-11-24 07:57:18'),
(12, 'Perjalanan di Madinah', 'Aktivitas jamaah di kota Madinah.', 'assets/img/gallery4.jpg', 'image', 'madinah', 1, '2025-11-24 07:57:18'),
(13, 'Wisata di Thaif', 'Eksplorasi keindahan Thaif setelah umroh.', 'assets/img/gallery5.jpg', 'image', 'thaif', 1, '2025-11-24 07:57:18'),
(14, 'Pantai Thaif', 'Momen santai di pantai Thaif.', 'assets/img/gallery6.jpg', 'image', 'thaif', 1, '2025-11-24 07:57:18'),
(15, 'Kota Tua di Turki', 'Kunjungan ke situs bersejarah di Turki.', 'assets/img/gallery7.jpg', 'image', 'turki', 1, '2025-11-24 07:57:18'),
(16, 'Masjid di Turki', 'Sholat di masjid indah di Turki.', 'assets/img/gallery8.jpg', 'image', 'turki', 1, '2025-11-24 07:57:18'),
(17, 'Grup Jamaah di Makkah', 'Foto bersama jamaah di Makkah.', 'assets/img/gallery9.jpg', 'image', 'makkah', 1, '2025-11-24 07:57:18'),
(18, 'Sunrise di Madinah', 'Pemandangan sunrise di Madinah.', 'assets/img/gallery10.jpg', 'image', 'madinah', 1, '2025-11-24 07:57:18'),
(19, 'Kuliner Thaif', 'Mencoba makanan khas Thaif.', 'assets/img/gallery11.jpg', 'image', 'thaif', 1, '2025-11-24 07:57:18');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL,
  `facilities` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration`, `facilities`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PAKET LIBUR SEKOLAH', 'Paket yang cocok untuk perjalanan umroh dengan fasilitas bagus, bersama keluarga dan anak-anak diliburan sekolah.', 31000000.00, 12, 'Tiket Pesawat PP\r\nHotel\r\nVisa + Bus + Tasreh \r\nRaudhah\r\nMakan 3x Sehari FB\r\nPerlengkapan\r\nHandling\r\nTL & Mutowif\r\nUmroh 3x\r\nSiskopatuh + Asuransi\r\nZiarah Madinah & Mekkah\r\nKurma 1 Kardus\r\nAir Zam-Zam 5 liter\r\nSertifikat Umroh\r\nAyam Albaik', 'assets/img/libursekolah.png', 1, '2025-11-19 09:17:03', '2025-12-01 07:26:15'),
(2, 'UMROH AWAL RAMADHAN', 'Mulai Ramadhan Anda dengan pengalaman ibadah paling istimewa! Umroh Awal Ramadhan menawarkan suasana ibadah yang lebih tenang, kesempatan pahala berlipat, serta layanan premium yang membuat perjalanan semakin nyaman.\r\nNikmati hotel dekat Masjidil Haram & Nabawi, fasilitas mewah, dan pendampingan jamaah yang profesional. Tempat terbatas amankan kursi Anda sekarang dan sambut Ramadhan langsung dari Tanah Suci!', 29500000.00, 12, 'Tiket Pesawat PP\r\nHotel\r\nVisa + Bus + Tasreh \r\nRaudhah\r\nMakan 2 x 1 (Sahur & Iftar)\r\nPerlengkapan\r\nHandling\r\nTL & Mutowif\r\nUmroh 3x\r\nSiskopatuh + Asuransi\r\nZiarah Madinah & Mekkah\r\nKurma 1 Kardus\r\nAir Zam-Zam 5 liter\r\nSertifikat Umroh\r\nAyam Albaik', 'assets/img/umrohawalramadhan.png', 1, '2025-11-19 09:17:03', '2025-12-01 07:30:33'),
(3, 'UMROH AKHIR RAMADHAN', 'Raih kesempatan merasakan malam paling mulia langsung di Tanah Suci! Umroh Akhir Ramadhan memberi Anda peluang besar untuk mendapatkan keberkahan Lailatul Qadr, malam yang lebih baik dari seribu bulan.\r\nDengan suasana ibadah yang penuh ketenangan, hotel dekat Masjid, dan pendampingan ibadah yang profesional, Anda dapat fokus berdoa, bertawaf, dan memperbanyak amalan di malam-malam terakhir Ramadhan.\r\nTempat sangat terbatas wujudkan impian meraih Lailatul Qadr di Tanah Haram dan pulang dengan hati yang lebih bersih serta keberkahan yang tak ternilai.', 37500000.00, 16, 'Tiket Pesawat PP\r\nHotel\r\nVisa + Bus + Tasreh \r\nRaudhah\r\nMakan 2 x 1 (Sahur & Iftar)\r\nPerlengkapan\r\nHandling\r\nTL & Mutowif\r\nUmroh 3x\r\nSiskopatuh + Asuransi\r\nZiarah Madinah & Mekkah\r\nKurma 1 Kardus\r\nAir Zam-Zam 5 liter\r\nSertifikat Umroh\r\nAyam Albaik', 'assets/img/umrohakhirramadhan.jpg', 1, '2025-11-19 09:17:03', '2025-12-01 07:41:41');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `package_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date NOT NULL,
  `available_slots` int(11) NOT NULL,
  `status` enum('available','full','cancelled') DEFAULT 'available',
  `airline` varchar(100) DEFAULT NULL,
  `route` varchar(100) DEFAULT NULL,
  `departure_day` varchar(20) DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `package_id`, `departure_date`, `return_date`, `available_slots`, `status`, `airline`, `route`, `departure_day`, `duration_days`) VALUES
(1, 1, '2025-07-12', '2025-07-22', 0, 'full', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(2, 1, '2025-07-26', '2025-08-05', 0, 'full', 'BATIK AIR', 'BTH KUL JED KUL BTH', 'SABTU', 12),
(3, 1, '2025-08-04', '2025-08-14', 0, 'full', 'BATIK AIR', 'BTH KUL JED KUL BTH', 'SENIN', 12),
(4, 1, '2025-08-09', '2025-08-20', 0, 'full', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(5, 1, '2025-08-23', '2025-09-02', 0, 'full', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(6, 1, '2025-09-27', '2025-10-08', 0, 'full', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(7, 1, '2025-10-18', '2025-10-28', 0, 'full', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(8, 1, '2025-11-22', '2025-12-02', 0, 'full', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(9, 1, '2025-12-20', '2025-12-31', 24, 'available', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(10, 1, '2026-01-17', '2026-01-27', 45, 'available', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(11, 1, '2026-02-21', '2026-03-04', 45, 'available', 'LION AIR', 'BTH JED BTH', 'SABTU', 12),
(12, 1, '2026-03-21', '2026-04-01', 45, 'available', 'LION AIR', 'BTH JED BTH', 'SABTU', 12);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value`, `updated_at`) VALUES
(1, 'company_name', 'PT. ALFARUQ ANUGERAH UTAMA', '2025-12-01 04:30:08'),
(2, 'tagline1', 'LANGKAH AWAL MENUJU BAITULLAH', '2025-11-19 02:17:03'),
(3, 'tagline2', 'HARGA HEMAT FASILITAS TERHORMAT', '2025-11-19 02:17:03'),
(4, 'vision', 'Menjadi penyelenggara tour wisata halal dan ibadah umroh yang berkomitmen dalam pelayanan yang profesional.', '2025-12-01 04:30:08'),
(5, 'mission', 'Melayani jamaah dengan SOP yang terukur dan cermat\r\nMenjadi sarana umat islam untuk memulai langkah awalnya agar dapat beribadah ke Baitullah\r\nMenjadi piliihan umat islam untuk beribada ke Tanah Suci dan wisata tour halal\r\nMemberikan kemudahan dan kenyamanan dalam proses pengurusan sebelum maupun disaat di Tanah Suci\r\nMemberikan bimbingan dan pembinaan ibadah Umroh dan Haji sesuai dengan Al Qur’an dan Assunnah\r\nMemberikan kemudahan kepada seluruh umat islam untuk dapat melaksanakan ibadah Haji dan Umroh dengan cara memberikan harga hemat fasilitas terhormat.', '2025-12-01 04:30:08'),
(6, 'contact_address', 'Ruko Bintan Center No. 56 RT.04 RW. 01 KM.10, Tanjungpinang', '2025-12-01 04:30:08'),
(7, 'contact_phone', '+62 812-6630-3236', '2025-12-01 04:30:08'),
(8, 'contact_email', 'alfaruq5619@gmail.com', '2025-12-01 04:30:09'),
(9, 'ppiu_license', 'SK PPIU NO.24022300153650007', '2025-12-01 04:30:09'),
(10, 'office_address1', 'Ruko Bintan Center No. 56 RT.04 RW. 01 KM.10, Tanjungpinang', '2025-12-01 04:30:09'),
(11, 'office_address2', 'Ruko Grand Niaga Mas Blok C No. 69. Belian, Batam Centre', '2025-12-01 04:30:09'),
(12, 'branch_addresses', 'Cabang 1: Selat Panjang\r\n(Jl. Dorak RT 002 RW 003, kel. Selat panjang timur, kec. Tebing tinggi, kab. Kepulauan Meranti, provinsi riau)', '2025-12-01 04:30:09'),
(13, 'admin_phone1', '+6281266303236', '2025-12-01 04:30:09'),
(14, 'admin_phone2', '+6281377327477', '2025-12-01 04:30:09'),
(15, 'company_email', 'alfaruq5619@gmail.com', '2025-12-01 04:30:09');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `image` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `q1` varchar(255) DEFAULT NULL,
  `q2` varchar(20) DEFAULT NULL,
  `q3` varchar(255) DEFAULT NULL,
  `q4` varchar(255) DEFAULT NULL,
  `q5` text DEFAULT NULL,
  `q6` varchar(50) DEFAULT NULL,
  `q7` text DEFAULT NULL,
  `q8` varchar(50) DEFAULT NULL,
  `q9` text DEFAULT NULL,
  `q10` varchar(50) DEFAULT NULL,
  `q11` text DEFAULT NULL,
  `q12` varchar(50) DEFAULT NULL,
  `q13` text DEFAULT NULL,
  `q14` varchar(50) DEFAULT NULL,
  `q15` text DEFAULT NULL,
  `q16` varchar(50) DEFAULT NULL,
  `q17` text DEFAULT NULL,
  `q18` varchar(50) DEFAULT NULL,
  `q19` text DEFAULT NULL,
  `q20` varchar(50) DEFAULT NULL,
  `q21` text DEFAULT NULL,
  `q22` text DEFAULT NULL,
  `q23` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `email`, `message`, `rating`, `image`, `is_approved`, `created_at`, `q1`, `q2`, `q3`, `q4`, `q5`, `q6`, `q7`, `q8`, `q9`, `q10`, `q11`, `q12`, `q13`, `q14`, `q15`, `q16`, `q17`, `q18`, `q19`, `q20`, `q21`, `q22`, `q23`) VALUES
(1, 'Rahmat Hidayat', 'rahmat@example.com', 'Saya merasa sangat terbantu dengan pelayanan Alfaruq Team sejak awal proses pendaftaran hingga kembali ke Tanah Air. Mulai dari penjelasan paket, manasik, hingga bimbingan ibadah semuanya terasa sangat profesional dan ramah. Saya benar-benar merasa dimudahkan dan dibimbing untuk menjalani ibadah dengan tenang.', 5, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Rahmat Hidayat', '081234567800', 'Berangkat bersama istri', 'Dari teman dekat yang sudah pernah berangkat sebelumnya', 'Saya memilih Alfaruq Team karena penjelasan paketnya sangat jelas, tidak bertele-tele, dan terasa transparan sejak awal.', 'Sangat puas', 'Admin sangat sabar menjelaskan seluruh hal yang saya tanyakan, termasuk hal-hal teknis keberangkatan.', 'Sangat puas', 'Penjelasan manasik dilakukan secara runtut, detail, dan sangat mudah dipahami.', 'Sangat puas', 'Tour berlangsung nyaman dan seluruh jamaah diarahkan dengan baik tanpa terburu-buru.', 'Sangat puas', 'Makanannya enak, bersih, dan porsinya cukup untuk perjalanan yang padat.', 'Sangat puas', 'Pembimbing ibadah sangat ramah dan selalu mengingatkan waktu ibadah.', 'Sangat puas', 'Muthawif sangat berpengalaman dan mampu memberikan penjelasan sejarah yang menarik.', 'Sangat puas', 'Itinerary sangat teratur dan tidak memberatkan jamaah.', 'Ya', 'Saya ingin kembali menggunakan Alfaruq Team karena pelayanannya sangat memuaskan.', 'Tidak ada saran, semuanya sudah berjalan dengan sangat baik.', 'Perjalanan ini memberikan banyak ketenangan dan pengalaman spiritual yang sulit dilupakan.'),
(2, 'Anisa Putri', 'anisa@example.com', 'Perjalanan umroh kali ini sangat memberikan pengalaman baru bagi saya. Saya merasa sangat diperhatikan oleh seluruh tim Alfaruq Team. Mereka bukan hanya sekadar mengatur teknis perjalanan, tetapi juga peduli terhadap kenyamanan jamaah. Saya sangat menghargai kesabaran pembimbing ibadah ketika membantu jamaah yang belum terbiasa dengan rangkaian ibadah tertentu.', 5, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Anisa Putri', '082233445566', 'Sendiri', 'Melihat konten di Instagram dan membaca banyak review positif', 'Saya memilih Alfaruq Team karena terlihat profesional dan banyak jamaah puas dengan pelayanannya.', 'Sangat puas', 'Semua informasi sebelum keberangkatan disampaikan secara rinci, termasuk hal-hal kecil yang ternyata sangat berguna.', 'Sangat puas', 'Manasik dibawakan dengan sangat lengkap, membuat saya jauh lebih siap.', 'Sangat puas', 'Tour sangat menyenangkan, terarah, dan tidak ada bagian yang membingungkan.', 'Puas', 'Makanan cukup enak dan tidak membuat perut bermasalah.', 'Sangat puas', 'Pembimbing ibadah sangat sopan dan komunikatif.', 'Sangat puas', 'Muthawif menjelaskan sejarah dengan cara yang sangat menarik dan mudah diikuti.', 'Sangat puas', 'Itinerary berjalan mulus tanpa ada perubahan mendadak.', 'Ya', 'Karena saya merasa sangat nyaman dari awal hingga akhir perjalanan.', 'Mungkin bisa menambah variasi menu makanan.', 'Sangat terkesan dengan pelayanan dan keramahan seluruh tim.'),
(3, 'Fajar Santoso', 'fajar@example.com', 'Saya tidak menyangka perjalanan umroh bisa terasa semenyenangkan dan setenang ini. Semua rangkaian ibadah berjalan sangat lancar karena bimbingan dari para pembimbing sangat jelas. Saya sangat menghargai disiplin dan perhatian yang diberikan kepada seluruh jamaah.', 4, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Fajar Santoso', '081255667788', 'Bersama rombongan kantor', 'Dari rekomendasi HRD', 'Karena travel ini sudah beberapa kali dipakai perusahaan dan hasilnya selalu memuaskan.', 'Puas', 'Informasi yang diberikan sudah cukup lengkap untuk persiapan keberangkatan.', 'Puas', 'Manasik diberikan dengan format yang mudah dipahami meskipun waktunya cukup padat.', 'Puas', 'Tour berjalan dengan ritme yang tepat sehingga tidak melelahkan.', 'Puas', 'Makanan cukup baik meskipun beberapa menu kurang saya sukai.', 'Puas', 'Tour Leader cukup komunikatif dan perhatian.', 'Puas', 'Muthawif informatif dan membantu banyak jamaah selama ibadah.', 'Puas', 'Itinerary cukup baik dan tidak menguras tenaga.', 'Ya', 'Karena pelayanannya stabil dan terpercaya.', 'Perlu peningkatan pada variasi makanan.', 'Secara keseluruhan perjalanan berjalan lancar dan menyenangkan.'),
(4, 'Mega Yuliani', 'mega@example.com', 'Saya sangat senang bisa berangkat umroh bersama Alfaruq Team. Timnya sangat ramah dan selalu membantu jamaah yang membutuhkan arahan. Pengalaman ini tidak hanya memberikan ketenangan spiritual tetapi juga menambah wawasan saya tentang sejarah Mekkah dan Madinah.', 5, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Mega Yuliani', '081288899900', 'Bersama kakak', 'Dari teman satu pengajian', 'Karena banyak rekomendasi dari jamaah pengajian yang sudah memakai jasa Alfaruq Team.', 'Sangat puas', 'Admin menjelaskan dengan sangat rinci dan detail tentang apa saja yang harus dibawa.', 'Sangat puas', 'Penjelasan manasik sangat lengkap dan tidak membingungkan.', 'Sangat puas', 'Tour sangat teratur dan wisata religi sangat menginspirasi.', 'Sangat puas', 'Makanan sangat cocok dengan selera saya.', 'Sangat puas', 'Tour Leader sangat sabar dan membantu jamaah lansia.', 'Sangat puas', 'Muthawif sangat baik dan sangat menghormati jamaah.', 'Sangat puas', 'Itinerary disusun dengan sangat baik dan nyaman.', 'Ya', 'Saya ingin kembali berangkat bersama travel ini.', 'Tidak ada saran tambahan.', 'Saya merasa perjalanan ini membawa banyak perubahan positif dalam diri saya.'),
(5, 'Tomi Prasetyo', 'tomi@example.com', 'Perjalanan umroh bersama Alfaruq Team memberikan pengalaman spiritual yang mendalam bagi saya. Para pembimbing sangat profesional dan selalu mengawasi jamaah sehingga seluruh kegiatan ibadah berjalan lancar.', 4, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Tomi Prasetyo', '081233220011', 'Sendiri', 'Dari YouTube review travel', 'Karena ulasan dari jamaah sebelumnya terlihat sangat jujur dan positif.', 'Puas', 'Informasi cukup lengkap meskipun penyampaiannya agak cepat.', 'Sangat puas', 'Manasik sangat membantu saya memahami alur ibadah.', 'Puas', 'Tour berjalan baik dan sangat terkoordinasi.', 'Puas', 'Makanan cukup enak meskipun tidak terlalu variatif.', 'Puas', 'Pembimbing ibadah sangat jelas dalam memberikan arahan.', 'Puas', 'Muthawif sangat informatif dan ramah.', 'Puas', 'Itinerary cukup baik.', 'Ya', 'Karena saya puas dengan keseluruhan pelayanan.', 'Mungkin bisa ditambahkan sesi tanya jawab lebih panjang saat manasik.', 'Perjalanan ibadah yang sangat bermakna bagi saya.'),
(6, 'Widya Amelia', 'widya@example.com', 'Ini adalah perjalanan umroh pertama saya dan saya merasa sangat terbantu oleh Alfaruq Team. Semua tim sangat sabar, baik hati, dan benar-benar peduli terhadap jamaah. Saya merasa sangat aman dan nyaman selama menjalani ibadah.', 5, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Widya Amelia', '082277889900', 'Bersama ibu', 'Dari postingan TikTok dan ulasan di komentar', 'Karena banyak testimoni baik terutama terkait pelayanan ramah dan profesional.', 'Sangat puas', 'Informasi disiapkan dengan sangat lengkap mulai dari pakaian hingga logistik.', 'Sangat puas', 'Manasik sangat nyaman diikuti dan pembimbing menjelaskan dengan sabar.', 'Sangat puas', 'Tour menyenangkan karena tidak terlalu padat.', 'Sangat puas', 'Makanannya cocok dengan selera ibu saya.', 'Sangat puas', 'Pembimbing ibadah sangat perhatian dan peduli dengan jamaah lansia.', 'Sangat puas', 'Muthawif sangat lembut dalam berbicara dan informatif.', 'Sangat puas', 'Itinerary sangat nyaman dan tidak melelahkan.', 'Ya', 'Saya merasa mendapatkan pengalaman ibadah terbaik.', 'Tidak ada saran tambahan.', 'Pengalaman yang sangat mengharukan bagi saya dan ibu.'),
(7, 'Hafiz Ramadhan', 'hafiz@example.com', 'Perjalanan umroh ini memberikan banyak pengalaman yang tidak hanya tentang ibadah, tetapi juga tentang kebersamaan dan kekeluargaan. Alfaruq Team mampu mengatur semuanya dengan sangat baik.', 5, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Hafiz Ramadhan', '081377744221', 'Sendiri', 'Dari postingan Facebook komunitas umroh', 'Karena banyak yang merekomendasikan pelayanan mereka.', 'Sangat puas', 'Informasi diberikan dengan sangat rapi dan mudah diikuti.', 'Sangat puas', 'Manasik sangat jelas dan membuat saya siap menjalankan ibadah.', 'Sangat puas', 'Tour sangat nyaman dan pemandu selalu menjaga ritme perjalanan.', 'Sangat puas', 'Makanan sangat enak dan tidak membuat saya sakit perut.', 'Sangat puas', 'Tour Leader sangat disiplin dan friendly.', 'Sangat puas', 'Muthawif sangat pintar dan penjelasannya sangat menyentuh.', 'Sangat puas', 'Itinerary berjalan sempurna dari awal hingga akhir.', 'Ya', 'Saya ingin kembali lagi karena merasakan kenyamanan luar biasa.', 'Tidak ada kritik, semuanya sangat baik.', 'Perjalanan ini memberi ketenangan yang sulit digambarkan.'),
(8, 'Siti Humairah', 'humairah@example.com', 'Saya sangat bersyukur memilih Alfaruq Team sebagai penyedia perjalanan umroh saya. Timnya sangat ramah dan profesional. Mereka memperlakukan jamaah seperti keluarga sendiri.', 5, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Siti Humairah', '082311119900', 'Bersama suami', 'Dari teman yang sudah 2 kali berangkat', 'Karena banyak mendapatkan rekomendasi dari orang-orang yang saya percaya.', 'Sangat puas', 'Informasi yang disampaikan sangat lengkap, detail, dan mudah dipahami.', 'Sangat puas', 'Manasik sangat membantu saya memahami setiap rukun dan sunnah umroh.', 'Sangat puas', 'Tour sangat nyaman dan edukatif.', 'Sangat puas', 'Makanan enak dan sangat cocok dengan selera saya.', 'Sangat puas', 'Pembimbing ibadah sangat sabar dan penyayang.', 'Sangat puas', 'Muthawif sangat menyenangkan dan selalu menghibur jamaah.', 'Sangat puas', 'Itinerary sangat terarah dan tidak membuat saya lelah berlebihan.', 'Ya', 'Karena semuanya benar-benar memuaskan.', 'Saya berharap perjalanan selanjutnya tetap seperti ini.', 'Saya merasa sangat diberkahi selama perjalanan.'),
(9, 'Doni Saputra', 'doni@example.com', 'Alfaruq Team memberikan pengalaman umroh yang luar biasa bagi saya. Seluruh rangkaian ibadah terasa sangat terarah dan mendalam karena pembimbing ibadah memberikan penjelasan yang sangat membantu.', 4, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Doni Saputra', '081200334455', 'Sendiri', 'Dari website + rating Google Maps', 'Karena reputasinya yang baik dan banyak rating positif.', 'Puas', 'Informasi cukup jelas meskipun penyampaian agak cepat.', 'Puas', 'Manasik cukup lengkap dan mendetail.', 'Puas', 'Tour berjalan lancar tanpa ada masalah berarti.', 'Puas', 'Makanan cukup enak meskipun ada beberapa menu yang kurang cocok.', 'Puas', 'Tour Leader cukup baik dan membantu.', 'Puas', 'Muthawif informatif namun terkadang terlalu cepat menjelaskan.', 'Puas', 'Itinerary cukup baik.', 'Ya', 'Karena saya puas dengan sebagian besar pelayanannya.', 'Perlu sedikit peningkatan pada makanan.', 'Secara keseluruhan perjalanan ini membawa ketenangan.'),
(10, 'Karina Zahra', 'karina@example.com', 'Saya benar-benar merasa dimudahkan dan dibimbing sepanjang perjalanan umroh bersama Alfaruq Team. Tim sangat ramah dan sabar dalam menjawab pertanyaan jamaah. Saya sebagai jamaah perempuan merasa sangat aman dan nyaman.', 5, 'assets/img/comment-photo.jpg', 1, '2025-12-01 02:35:47', 'Karina Zahra', '082278889900', 'Sendiri', 'Dari TikTok dan rekomendasi saudara', 'Karena terlihat profesional dan komunikatif sejak awal saya menghubungi admin.', 'Sangat puas', 'Informasi yang diberikan sangat lengkap dan disusun dengan rapi.', 'Sangat puas', 'Manasik sangat mudah diikuti dan disampaikan dengan lembut.', 'Sangat puas', 'Tour terasa sangat menyenangkan, tidak melelahkan, dan penuh edukasi.', 'Sangat puas', 'Makanannya sangat cocok dan tidak membuat perut bermasalah.', 'Sangat puas', 'Pembimbing ibadah sangat perhatian, terutama pada jamaah perempuan.', 'Sangat puas', 'Muthawif sangat ramah dan memiliki suara yang menenangkan.', 'Sangat puas', 'Itinerary berjalan dengan sempurna tanpa hambatan.', 'Ya', 'Saya ingin kembali menggunakan travel ini karena sangat nyaman.', 'Tidak ada saran tambahan.', 'Perjalanan ini sangat berkesan dan memberikan banyak ketenangan spiritual.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `galleries`
--
ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `galleries`
--
ALTER TABLE `galleries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
