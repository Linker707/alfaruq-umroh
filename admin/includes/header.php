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
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar-brand {
            padding: 0 20px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h4 {
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .sidebar-brand p {
            font-size: 0.8rem;
            opacity: 0.8;
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
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 25px;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        }
        
        /* Cards */
        .admin-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
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
        }
        
        .admin-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar:hover {
                width: 250px;
            }
            
            .sidebar-brand h4, 
            .sidebar-brand p,
            .nav-link span {
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .sidebar:hover .sidebar-brand h4,
            .sidebar:hover .sidebar-brand p,
            .sidebar:hover .nav-link span {
                opacity: 1;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .sidebar:hover + .main-content {
                margin-left: 250px;
            }
        }
        
        @media (max-width: 576px) {
            .sidebar {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
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
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>" href="user.php">
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
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark">
                            <?php 
                            $page_titles = [
                                'index.php' => 'Dashboard',
                                'login.php' => 'Login',
                                'users/index.php' => 'Kelola Users',
                                'users/create.php' => 'Tambah User',
                                'users/edit.php' => 'Edit User',
                                'users/reset-password.php' => 'Reset Password'
                            ];
                            echo $page_titles[$current_page] ?? 'Admin Panel';
                            ?>
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <?php echo date('l, d F Y'); ?>
                        </small>
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