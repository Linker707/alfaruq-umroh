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
    <style>
        :root {
            --primary-green: #4CAF50;
            --dark-green: #2E7D32;
            --light-green: #E8F5E9;
            --accent-yellow: #FFEB3B;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(180deg, var(--dark-green) 0%, var(--primary-green) 100%);
            color: white;
            padding-top: 20px;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
            z-index: 1050;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-collapsed {
            width: 70px;
        }
        
        .sidebar-collapsed .sidebar-brand h4,
        .sidebar-collapsed .sidebar-brand p,
        .sidebar-collapsed .nav-link span {
            opacity: 0;
            visibility: hidden;
            width: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .sidebar-collapsed .nav-link {
            padding: 12px;
            justify-content: center;
        }
        
        .sidebar-collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.3rem;
        }
        
        .sidebar-brand {
            padding: 0 20px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s;
        }
        
        .sidebar-brand h4 {
            font-weight: 700;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .sidebar-brand p {
            font-size: 0.8rem;
            opacity: 0.8;
            transition: all 0.3s;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 25px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 25px;
            font-size: 1.1rem;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content-expanded {
            margin-left: 70px;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 25px;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1040;
        }
        
        .sidebar-toggle {
            background: transparent;
            border: none;
            color: var(--primary-green);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s;
            display: none;
        }
        
        .sidebar-toggle:hover {
            background: var(--light-green);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .user-details small {
            color: #666;
        }
        
        .role-badge {
            background: var(--accent-yellow);
            color: #333;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        /* Cards */
        .admin-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
        }
        
        .admin-card .card-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 15px 20px;
            border: none;
        }
        
        .admin-card .card-body {
            padding: 20px;
        }
        
        /* Buttons */
        .btn-admin-primary {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .btn-admin-primary:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-admin-outline {
            background: transparent;
            color: var(--primary-green);
            border: 2px solid var(--primary-green);
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .btn-admin-outline:hover {
            background: var(--primary-green);
            color: white;
        }
        
        /* Tables */
        .admin-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .admin-table thead {
            background: var(--light-green);
        }
        
        .admin-table th {
            font-weight: 600;
            color: var(--dark-green);
            border-bottom: 2px solid var(--primary-green);
            padding: 15px;
            white-space: nowrap;
        }
        
        .admin-table td {
            padding: 12px 15px;
            vertical-align: middle;
            word-break: break-word;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            white-space: nowrap;
        }
        
        .status-active {
            background: rgba(76, 175, 80, 0.1);
            color: var(--dark-green);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .status-inactive {
            background: rgba(158, 158, 158, 0.1);
            color: #666;
            border: 1px solid rgba(158, 158, 158, 0.3);
        }
        
        /* Mobile Menu Overlay */
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar:not(.sidebar-collapsed) {
                width: 250px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .sidebar:not(.sidebar-collapsed) + .main-content {
                margin-left: 250px;
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .mobile-menu-overlay.active {
                display: block;
            }
        }
        
        @media (max-width: 992px) {
            .admin-table {
                font-size: 0.9rem;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 10px;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 5px;
            }
            
            .user-details {
                text-align: center;
            }
            
            .top-navbar {
                padding: 10px 15px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 15px;
            }
            
            .mobile-menu-overlay.active {
                display: block;
            }
            
            .admin-card {
                margin-bottom: 15px;
            }
            
            .admin-card .card-body {
                padding: 15px;
            }
            
            .btn-admin-primary,
            .btn-admin-outline {
                padding: 6px 15px;
                font-size: 0.9rem;
            }
            
            .table-responsive {
                border: none;
            }
        }
        
        @media (max-width: 576px) {
            .container-fluid {
                padding: 0 10px;
            }
            
            .top-navbar {
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }
            
            .top-navbar > div {
                width: 100%;
                text-align: center;
            }
            
            .admin-table {
                font-size: 0.85rem;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 8px;
            }
            
            .nav-link {
                padding: 10px 15px;
                margin: 3px 10px;
            }
            
            .sidebar-brand {
                padding: 0 15px 20px;
            }
            
            .main-content {
                padding: 10px;
            }
        }
        
        /* Print Styles */
        @media print {
            .sidebar,
            .top-navbar,
            .sidebar-toggle,
            .btn-admin-primary,
            .btn-admin-outline {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 0;
            }
            
            .admin-card {
                box-shadow: none;
                border: 1px solid #ddd;
                margin-bottom: 10px;
            }
        }

        .bg-brown {
            background: #8B4513 !important;
            color: white !important;
        }

        .bg-light {
            background: #f8f9fa !important;
            color: #212529 !important;
        }
        
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-green);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--dark-green);
        }
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(76, 175, 80, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-green);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1060;
            max-width: 350px;
        }
        
        .toast {
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border-left: 4px solid var(--primary-green);
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Badge Responsive */
        .badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
            display: inline-block;
        }
        
        /* Grid System Responsive */
        .row-responsive {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        /* Form Controls Responsive */
        .form-control {
            width: 100%;
        }
        
        /* Image Responsive */
        .img-responsive {
            max-width: 100%;
            height: auto;
        }
        
        /* Modal Responsive */
        .modal-dialog {
            max-width: 95%;
            margin: 1.75rem auto;
        }
        
        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 500px;
            }
        }
        
        @media (min-width: 768px) {
            .modal-dialog {
                max-width: 700px;
            }
        }
        
        @media (min-width: 992px) {
            .modal-dialog {
                max-width: 900px;
            }
        }
    </style>
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