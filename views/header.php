<?php
// views/header.php - File header untuk navbar dan meta tags

// Ambil settings untuk kontak
require_once 'config/database.php';
$querySettings = "SELECT key_name, value FROM settings WHERE key_name IN ('contact_phone', 'tagline1', 'tagline2')";
$stmtSettings = $pdo->prepare($querySettings);
$stmtSettings->execute();
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);

$tagline1 = $settings['tagline1'] ?? "LANGKAH AWAL MENUJU BAITULLAH";
$tagline2 = $settings['tagline2'] ?? "HARGA HEMAT FASILITAS TERHORMAT";
$whatsapp = $settings['contact_phone'] ?? "+6281234567890";
$waNumber = preg_replace('/[^0-9]/', '', $whatsapp);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover" />
    <title>ALFARUQ TEAM - Travel Umroh Terpercaya</title>
    <meta name="description" content="ALFARUQ TEAM, travel umroh harga hemat dan fasilitas terhormat. <?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?>" />
    <meta name="author" content="ALFARUQ TEAM" />
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="ALFARUQ TEAM - Travel Umroh Terpercaya">
    <meta property="og:description" content="<?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?>">
    <meta property="og:image" content="assets/img/og-image.jpg">
    <meta property="og:url" content="https://alfaruqteam.com">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ALFARUQ TEAM - Travel Umroh Terpercaya">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($tagline1); ?> - <?php echo htmlspecialchars($tagline2); ?>">
    <meta name="twitter:image" content="assets/img/og-image.jpg">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/img/favicon/site.webmanifest">
    <meta name="theme-color" content="#4CAF50">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    
    <!-- Modern Green Theme CSS -->
    <link rel="stylesheet" href="assets/css/modern-green.css" />
    
    <!-- Responsive Fixes -->
    <link rel="stylesheet" href="assets/css/responsive-fix.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/main.css" />
    
    <!-- Preload critical resources -->
    <link rel="preload" href="assets/css/modern-green.css" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body class="modern-green">

<!-- ============================================
    MODERN NAVBAR
============================================ -->
<nav class="navbar navbar-expand-lg navbar-modern-green sticky-top">
    <div class="container">
        <!-- Brand/Logo -->
        <a class="navbar-brand" href="index.php">
            <img src="assets/img/logo.svg" alt="Logo Alfaruq" width="60" height="60">
            <span class="navbar-brand-text ms-2">ALFARUQ TEAM</span>
        </a>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Nav Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="about.php">
                        <i class="fas fa-info-circle me-1"></i> About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'packages.php' ? 'active' : ''; ?>" href="packages.php">
                        <i class="fas fa-box-open me-1"></i> Packages
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-1"></i> Pages
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="pagesDropdown">
                        <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php"><i class="fas fa-phone me-2"></i> Contact</a></li>
                        <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>" href="gallery.php"><i class="fas fa-images me-2"></i> Gallery</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>" href="register.php"><i class="fas fa-user-plus me-2"></i> Register</a></li>
                    </ul>
                </li>
                <li class="nav-item ms-lg-3">
                    <a href="packages.php" class="btn-modern-green outline sm">
                        <i class="fas fa-calendar-check me-2"></i> Book Now
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>