<?php
require_once '../../includes/auth.php';
checkAccess('admin');
$pageTitle = 'Kelola Jadwal Keberangkatan';
require_once '../../includes/header.php';

$csrfToken = generateCsrfToken();

// Handle search and filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Build query
$query = "SELECT * FROM schedules WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (airline LIKE ? OR route LIKE ? OR departure_day LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

if (!empty($startDate)) {
    $query .= " AND departure_date >= ?";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $query .= " AND departure_date <= ?";
    $params[] = $endDate;
}

$query .= " ORDER BY departure_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schedules = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="fas fa-calendar-alt me-2"></i>Kelola Jadwal Keberangkatan</h4>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Jadwal
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari maskapai/rute..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="available" <?php echo $status == 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="full" <?php echo $status == 'full' ? 'selected' : ''; ?>>Full</option>
                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" placeholder="Dari tanggal" 
                       value="<?php echo htmlspecialchars($startDate); ?>">
            </div>
            
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" placeholder="Sampai tanggal" 
                       value="<?php echo htmlspecialchars($endDate); ?>">
            </div>
            
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Schedule List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($schedules)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada jadwal ditemukan</h5>
                <p class="text-muted">Mulai dengan menambahkan jadwal baru</p>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Tambah Jadwal
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>Tanggal Keberangkatan</th>
                            <th>Tanggal Kembali</th>
                            <th>Maskapai</th>
                            <th>Rute</th>
                            <th>Hari</th>
                            <th>Slot</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td>
                                    <?php echo date('d M Y', strtotime($schedule['departure_date'])); ?>
                                </td>
                                <td>
                                    <?php echo date('d M Y', strtotime($schedule['return_date'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($schedule['airline']); ?></span>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($schedule['route']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($schedule['departure_day']); ?></td>
                                <td>
                                    <span class="badge <?php echo $schedule['available_slots'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $schedule['available_slots']; ?> slot
                                    </span>
                                </td>
                                <td><?php echo $schedule['duration_days'] ?? '12'; ?> hari</td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $schedule['status'] == 'available' ? 'success' : 
                                               ($schedule['status'] == 'full' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($schedule['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?php echo $schedule['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $schedule['id']; ?>&token=<?php echo $csrfToken; ?>" 
                                           class="btn btn-sm btn-outline-danger confirm-delete" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<?php require_once '../../includes/footer.php'; ?>