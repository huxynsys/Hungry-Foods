<?php
// admin/admin-reservations.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once __DIR__ . '/../../backend/db.php';
global $conn;

// Ensure admin_notes column exists (migration for older tables)
try {
    $col_check = $conn->query("SHOW COLUMNS FROM reservations LIKE 'admin_notes'");
    if ($col_check->rowCount() == 0) {
        $conn->exec("ALTER TABLE reservations ADD COLUMN admin_notes TEXT AFTER status");
    }
} catch (PDOException $e) {
    // Table might not exist yet; reservation_process will create it
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['reservation_id'];
    $status = $_POST['status'];
    $notes = $_POST['admin_notes'] ?? '';

    // Validate status
    $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status value.";
        header('Location: admin-reservations.php');
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE reservations SET status = :status, admin_notes = :admin_notes WHERE id = :id");
        $stmt->execute([':status' => $status, ':admin_notes' => $notes, ':id' => $id]);

        // Log the action
        $log_stmt = $conn->prepare("INSERT INTO reservation_logs (reservation_id, action, details) VALUES (:reservation_id, 'status_updated', :details)");
        $log_stmt->execute([':reservation_id' => $id, ':details' => 'Status changed to ' . $status]);

        $_SESSION['success'] = "Reservation updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header('Location: admin-reservations.php');
    exit;
}

// Get all reservations (alias DB columns to match UI expectations)
$result = $conn->query("SELECT id, reservation_id, name AS customer_name, phone AS customer_phone, email AS customer_email, guests, reservation_date, reservation_time, table_type, occasion, CONCAT(COALESCE(table_type,''), CASE WHEN occasion != '' THEN CONCAT(' - ', occasion) ELSE '' END) AS preference, special_requests, status, admin_notes, created_at FROM reservations ORDER BY reservation_date DESC, reservation_time ASC");
$reservations = $result ? $result->fetchAll() : [];

// Get counts
$counts_stmt = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM reservations
");
$counts = $counts_stmt ? $counts_stmt->fetch() : ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'completed' => 0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-calendar-alt me-2"></i>Hungry Food - Reservations</span>
            <div>
                <a href="admin-contact.php" class="btn btn-light btn-sm me-2">Messages</a>
                <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h6 class="card-title">Total</h6>
                        <h3><?php echo $counts['total']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h6 class="card-title">Pending</h6>
                        <h3><?php echo $counts['pending']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h6 class="card-title">Confirmed</h6>
                        <h3><?php echo $counts['confirmed']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h6 class="card-title">Completed</h6>
                        <h3><?php echo $counts['completed']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h6 class="card-title">Cancelled</h6>
                        <h3><?php echo $counts['cancelled']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservations Table -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Reservations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reservation ID</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Date & Time</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reservations)): ?>
                                <?php foreach($reservations as $row): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><small class="text-muted"><?php echo $row['reservation_id']; ?></small></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td>
                                        <small>
                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($row['customer_phone']); ?><br>
                                            <?php if($row['customer_email']): ?>
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($row['customer_email']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo date('M d, Y', strtotime($row['reservation_date'])); ?></strong><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($row['reservation_time'])); ?></small>
                                    </td>
                                    <td><?php echo $row['guests']; ?></td>
                                    <td>
                                        <?php
                                        $badge_class = 'secondary';
                                        if($row['status'] == 'pending') $badge_class = 'warning';
                                        if($row['status'] == 'confirmed') $badge_class = 'success';
                                        if($row['status'] == 'cancelled') $badge_class = 'danger';
                                        if($row['status'] == 'completed') $badge_class = 'info';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo $row['status']; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?php echo $row['id']; ?>">
                                            <i class="fas fa-edit"></i> Update
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No reservations found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Modals -->
    <?php if (!empty($reservations)): ?>
        <?php foreach($reservations as $row): ?>
        <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-edit me-2"></i>Update Reservation #<?php echo $row['id']; ?>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Customer Details</label>
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($row['customer_name']); ?></p>
                                <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($row['customer_phone']); ?></p>
                                <?php if($row['customer_email']): ?>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($row['customer_email']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Reservation Details</label>
                                <p class="mb-1"><strong>Date:</strong> <?php echo date('F d, Y', strtotime($row['reservation_date'])); ?></p>
                                <p class="mb-1"><strong>Time:</strong> <?php echo date('h:i A', strtotime($row['reservation_time'])); ?></p>
                                <p class="mb-1"><strong>Guests:</strong> <?php echo $row['guests']; ?></p>
                                <p class="mb-1"><strong>Preference:</strong> <?php echo ucfirst($row['preference']); ?></p>
                                <?php if($row['special_requests']): ?>
                                <p class="mb-1"><strong>Requests:</strong> <?php echo nl2br(htmlspecialchars($row['special_requests'])); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $row['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="completed" <?php echo $row['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $row['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Admin Notes</label>
                                <textarea name="admin_notes" class="form-control" rows="3"><?php echo htmlspecialchars($row['admin_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>