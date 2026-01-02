<?php
require_once 'includes/auth.php';
$pageTitle = 'Profile Admin';
require_once 'includes/header.php';

$csrfToken = generateCsrfToken();

// Get current user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    
    // Get form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Change password if provided
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = 'Nama lengkap harus diisi';
    }
    
    // If changing password
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Password saat ini harus diisi';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Password saat ini salah';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Password baru minimal 6 karakter';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }
    }
    
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Update with new password
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$full_name, $email, $phone, $hashedPassword, $_SESSION['admin_id']]);
            } else {
                // Update without password
                $query = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$full_name, $email, $phone, $_SESSION['admin_id']]);
            }
            
            // Update session
            $_SESSION['admin_fullname'] = $full_name;
            
            // Log activity
            logActivity('UPDATE_PROFILE', "Memperbarui profile");
            
            $_SESSION['success_message'] = 'Profile berhasil diperbarui!';
            header('Location: profile.php');
            exit;
            
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui profile: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Profile Admin</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div class="avatar-profile mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($user['role']); ?></span>
                            <p class="text-muted small mt-2">Bergabung: <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="full_name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Password Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-lock me-2"></i>Ubah Password</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="current_password" class="form-label">Password Saat Ini</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Perubahan
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                        </div>
                        <div>
                            <a href="dashboard.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>