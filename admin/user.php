<?php
require_once 'includes/auth.php';

// Hanya master_admin yang bisa akses
if ($_SESSION['admin_role'] !== 'master_admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Default action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Process actions
switch ($action) {
    case 'create':
        $process = processCreateUser();
        if ($process['success']) {
            header('Location: user.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
        
    case 'update':
        $process = processUpdateUser($id);
        if ($process['success']) {
            header('Location: user.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
        
    case 'reset_password':
        $process = processResetPassword($id);
        if ($process['success']) {
            header('Location: user.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
        
    case 'delete':
        $process = processDeleteUser($id);
        if ($process['success']) {
            header('Location: user.php?msg=' . urlencode($process['message']));
            exit;
        }
        $error = $process['message'];
        break;
}

// Get message from URL
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

// Get user data for edit/reset
$user = null;
if ($id > 0 && ($action == 'edit' || $action == 'reset')) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: user.php');
        exit;
    }
}

// Get all users for list
$stmt = $pdo->query("SELECT u.*, creator.username as created_by_name FROM users u LEFT JOIN users creator ON u.created_by = creator.id ORDER BY u.id DESC");
$users = $stmt->fetchAll();

// Function: Create User
function processCreateUser() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    
    // Validation
    if (empty($username) || empty($password) || empty($full_name)) {
        return ['success' => false, 'message' => 'Username, password, dan nama lengkap harus diisi'];
    }
    
    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Password dan konfirmasi password tidak cocok'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password minimal 6 karakter'];
    }
    
    // Check duplicate username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insert user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $full_name, $email, $phone, $role, $_SESSION['admin_id']]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'ADD_USER', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menambahkan user baru: $username"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'User berhasil ditambahkan'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Update User
function processUpdateUser($id) {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($username) || empty($full_name)) {
        return ['success' => false, 'message' => 'Username dan nama lengkap harus diisi'];
    }
    
    // Check duplicate username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$username, $full_name, $email, $phone, $role, $is_active, $id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'UPDATE_USER', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Memperbarui user: $username"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'User berhasil diperbarui'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Reset Password
function processResetPassword($id) {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($new_password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'Password baru dan konfirmasi harus diisi'];
    }
    
    if ($new_password !== $confirm_password) {
        return ['success' => false, 'message' => 'Password baru dan konfirmasi tidak cocok'];
    }
    
    if (strlen($new_password) < 6) {
        return ['success' => false, 'message' => 'Password minimal 6 karakter'];
    }
    
    // Get user info for log
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    try {
        $pdo->beginTransaction();
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'CHANGE_PASSWORD', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Mengubah password user: {$user['username']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Password berhasil direset'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

// Function: Delete User
function processDeleteUser($id) {
    global $pdo;
    
    // Prevent self-deletion
    if ($id == $_SESSION['admin_id']) {
        return ['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri'];
    }
    
    // Get user info for log
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'User tidak ditemukan'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'DELETE_USER', ?)");
        $log_stmt->execute([$_SESSION['admin_id'], "Menghapus user: {$user['username']}"]);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'User berhasil dihapus'];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Users - Admin ALFARUQ TEAM</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid mt-4">
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($action == 'list'): ?>
        <!-- LIST USERS -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Users</h5>
                    <p class="text-white mb-0 opacity-75">Total <?php echo count($users); ?> user terdaftar</p>
                </div>
                <a href="?action=create" class="btn-admin-primary">
                    <i class="fas fa-plus me-2"></i>Tambah User Baru
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-4"></i>
                        <h5 class="text-muted">Belum ada user</h5>
                        <p class="text-muted mb-4">Mulai dengan menambahkan user baru</p>
                        <a href="?action=create" class="btn-admin-primary">
                            <i class="fas fa-plus me-2"></i>Tambah User Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Terakhir Login</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user_item): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light-green text-dark">#<?php echo $user_item['id']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-3">
                                                <?php echo strtoupper(substr($user_item['full_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-medium">
                                                    <?php echo htmlspecialchars($user_item['username']); ?>
                                                    <?php if ($user_item['id'] == $_SESSION['admin_id']): ?>
                                                        <span class="badge bg-warning text-dark ms-2">Anda</span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><?php echo htmlspecialchars($user_item['email'] ?: 'Tidak ada email'); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user_item['full_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user_item['role'] == 'master_admin' ? 'bg-warning text-dark' : 'bg-light-green text-dark'; ?>">
                                            <i class="fas fa-<?php echo $user_item['role'] == 'master_admin' ? 'crown' : 'user'; ?> me-1"></i>
                                            <?php echo $user_item['role'] == 'master_admin' ? 'Master Admin' : 'Admin'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $user_item['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fas fa-<?php echo $user_item['is_active'] ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                            <?php echo $user_item['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($user_item['created_by_name'] ?? 'System'); ?>
                                            <br>
                                            <?php echo date('d M Y', strtotime($user_item['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($user_item['last_login']): ?>
                                            <small class="text-muted">
                                                <?php echo date('d M Y', strtotime($user_item['last_login'])); ?>
                                                <br>
                                                <?php echo date('H:i', strtotime($user_item['last_login'])); ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">Belum pernah login</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $user_item['id']; ?>" 
                                               class="btn btn-outline-warning" 
                                               data-bs-toggle="tooltip" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=reset&id=<?php echo $user_item['id']; ?>" 
                                               class="btn btn-outline-info" 
                                               data-bs-toggle="tooltip" 
                                               title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </a>
                                            <?php if ($user_item['id'] != $_SESSION['admin_id']): ?>
                                            <button type="button" 
                                                    onclick="confirmDelete(<?php echo $user_item['id']; ?>, '<?php echo addslashes($user_item['username']); ?>')"
                                                    class="btn btn-outline-danger" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php elseif ($action == 'create'): ?>
        <!-- CREATE USER FORM -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Tambah User Baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=create">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-control" id="role" name="role">
                                    <option value="admin">Admin</option>
                                    <option value="master_admin">Master Admin</option>
                                </select>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="user.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn-admin-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php elseif ($action == 'edit'): ?>
        <!-- EDIT USER FORM -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit User: <?php echo htmlspecialchars($user['username']); ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=update&id=<?php echo $id; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-control" id="role" name="role">
                                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="master_admin" <?php echo $user['role'] == 'master_admin' ? 'selected' : ''; ?>>Master Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="user.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn-admin-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php elseif ($action == 'reset'): ?>
        <!-- RESET PASSWORD FORM -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Reset Password untuk: <?php echo htmlspecialchars($user['username']); ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=reset_password&id=<?php echo $id; ?>">
                            <div class="mb-4">
                                <label for="new_password" class="form-label">Password Baru *</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="user.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn-admin-primary">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Confirm delete function
        function confirmDelete(userId, username) {
            Swal.fire({
                title: 'Hapus User?',
                html: `Apakah Anda yakin ingin menghapus user <strong>${username}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?action=delete&id=' + userId;
                }
            });
        }
    </script>
</body>
</html>