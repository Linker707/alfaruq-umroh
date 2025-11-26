<?php
// views/header.php - File header untuk navbar dan meta tags
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ALFARUQ TEAM - Travel Umroh Terpercaya</title>
    <meta name="description" content="ALFARUQ TEAM, travel umroh harga hemat dan fasilitas terhormat." />
    <meta name="author" content="ALFARUQ TEAM" />
    <!-- Bootstrap 5 CSS - Framework untuk styling responsif -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Google Fonts: Poppins untuk body, Montserrat untuk heading -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <!-- Custom Stylesheet - Styling khusus untuk brand ALFARUQ TEAM -->
    <link rel="stylesheet" href="assets/css/main.css" />
</head>
<body>

<!-- Navbar - Awal dengan background putih, akan hijau solid saat scroll -->
<!-- ID 'mainNavbar' digunakan untuk manipulasi JS saat scroll -->
<nav id="mainNavbar" class="navbar navbar-expand-lg navbar-light-cstm sticky-top shadow-sm">
    <div class="container">
        <!-- Brand/Logo navbar dengan gambar logo dan teks -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <!-- Gambar logo SVG (ganti path jika perlu) -->
            <img src="assets/img/logo.svg" alt="Logo Alfaruq" width="60" height="60" class="me-2">
            <!-- Teks brand dengan class khusus untuk styling warna -->
            <span class="fw-bold fs-4 mt-1 navbar-brand-text">ALFARUQ TEAM</span>
        </a>
        <!-- Tombol toggle untuk mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Menu navbar -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <!-- Link menu dengan class active untuk halaman saat ini -->
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="services.php">Testimonials</a></li>
                <li class="nav-item"><a class="nav-link" href="packages.php">Packages</a></li>
                <!-- Dropdown menu untuk halaman tambahan -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                        Pages
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="pagesDropdown">
                        <li><a class="dropdown-item" href="contact.php">Contact</a></li>
                        <li><a class="dropdown-item" href="gallery.php">Gallery</a></li>
                        <li><a class="dropdown-item" href="register.php">Register</a></li>
                    </ul>
                </li>
                <!-- Tombol "Book Now" dengan outline hijau di awal, akan berubah saat scroll -->
                <li class="nav-item ms-lg-3">
                    <a href="contact.php" class="btn btn-outline-success rounded-pill px-4 py-2">Book Now</a>
                </li>
            </ul>
        </div>
    </div>
</nav>