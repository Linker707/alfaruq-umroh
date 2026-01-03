<?php
// Pastikan auth.php sudah diinclude di file utama
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ALFARUQ TEAM</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Admin CSS -->
    <link href="assets/admin.css" rel="stylesheet">
    <link href="assets/admin-responsive.css" rel="stylesheet">
</head>
<body>
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-cog fa-2x mb-3"></i>
            <h4>Admin Panel</h4>
            <p>ALFARUQ TEAM</p>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if ($_SESSION['admin_role'] === 'master_admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false || strpos($_SERVER['PHP_SELF'], 'user.php') !== false ? 'active' : ''; ?>" href="user.php">
                        <i class="fas fa-users"></i>
                        <span>Kelola Users</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'package') !== false ? 'active' : ''; ?>" href="package.php">
                        <i class="fas fa-box-open"></i>
                        <span>Kelola Paket</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'schedule') !== false ? 'active' : ''; ?>" href="schedule.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Kelola Jadwal</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'price') !== false ? 'active' : ''; ?>" href="price.php">
                        <i class="fas fa-tags"></i>
                        <span>Kelola Harga</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'gallery') !== false ? 'active' : ''; ?>" href="gallery.php">
                        <i class="fas fa-images"></i>
                        <span>Kelola Galeri</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'testimonial') !== false ? 'active' : ''; ?>" href="testimonial.php">
                        <i class="fas fa-comments"></i>
                        <span>Kelola Testimoni</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'profile') !== false ? 'active' : ''; ?>" href="profile.php">
                        <i class="fas fa-building"></i>
                        <span>Profil Perusahaan</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../index.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Lihat Website</span>
                    </a>
                </li>
                
                <li class="nav-item mt-4">
                    <a class="nav-link text-warning" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <button class="sidebar-toggle" id="sidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div>
                            <h5 class="mb-0 text-dark">
                                <?php 
                                $page_titles = [
                                    'index.php' => 'Dashboard',
                                    'login.php' => 'Login',
                                    'user.php' => 'Kelola Users',
                                    'users/index.php' => 'Kelola Users',
                                    'users/create.php' => 'Tambah User',
                                    'users/edit.php' => 'Edit User',
                                    'users/reset-password.php' => 'Reset Password',
                                    'package.php' => 'Kelola Paket',
                                    'schedule.php' => 'Kelola Jadwal',
                                    'price.php' => 'Kelola Harga',
                                    'gallery.php' => 'Kelola Galeri',
                                    'testimonial.php' => 'Kelola Testimoni',
                                    'profile.php' => 'Profil Perusahaan'
                                ];
                                echo $page_titles[$current_page] ?? 'Admin Panel';
                                ?>
                            </h5>
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo date('l, d F Y'); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_full_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_full_name']); ?></div>
                            <div class="role-badge">
                                <?php echo $_SESSION['admin_role'] == 'master_admin' ? 'Master Admin' : 'Admin'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>