<?php
// admin/admin-contact.php - SECURE VERSION

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Include database connection 
require_once __DIR__ . '/db.php';

global $conn;

// Check if connection exists
if (!isset($conn) || !$conn) {
    die("<div class='alert alert-danger'>Database connection failed. Please check your database configuration.</div>");
}

// Ensure admin_notes column exists (migration for older tables)
try {
    $col_check = $conn->query("SHOW COLUMNS FROM contacts LIKE 'admin_notes'");
    if ($col_check->rowCount() == 0) {
        $conn->exec("ALTER TABLE contacts ADD COLUMN admin_notes TEXT AFTER priority");
    }
} catch (PDOException $e) {
    // Table might not exist yet; contact_process will create it
}

// Handle status update with prepared statement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $contact_id = (int)$_POST['contact_id'];
    $status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    // Validate status value
    $valid_statuses = ['pending', 'in_progress', 'resolved', 'closed'];
    if (in_array($status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE contacts SET status = :status, admin_notes = :admin_notes WHERE id = :id");
        $stmt->execute([
            ':status' => $status,
            ':admin_notes' => htmlspecialchars($admin_notes, ENT_QUOTES, 'UTF-8'),
            ':id' => $contact_id
        ]);
        $_SESSION['success_message'] = "Contact #$contact_id updated successfully!";
    } else {
        $_SESSION['error_message'] = "Invalid status value.";
    }
    
    header('Location: admin-contact.php');
    exit;
}

// Get all contacts with prepared statements
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "SELECT * FROM contacts WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR message LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($status_filter !== 'all') {
    $sql .= " AND status = :status_filter";
    $params[':status_filter'] = $status_filter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll();
$contacts = $result ?: [];

// Get counts
$count_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
    FROM contacts";
$count_result = $conn->query($count_sql);
$counts = $count_result ? $count_result->fetch() : ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <span class="navbar-brand">Hungry Food Admin</span>
            <div>
                <a href="admin-contact.php" class="btn btn-light btn-sm me-2">Messages</a>
                <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h2>Contact Messages</h2>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total</h5>
                        <h3><?php echo $counts['total']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Pending</h5>
                        <h3><?php echo $counts['pending']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">In Progress</h5>
                        <h3><?php echo $counts['in_progress']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Resolved</h5>
                        <h3><?php echo $counts['resolved']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-secondary">
                    <div class="card-body">
                        <h5 class="card-title">Closed</h5>
                        <h3><?php echo $counts['closed']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="card">
            <div class="card-body">
                <?php if (!empty($contacts)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Contact ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $row): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td><small><?php echo $row['contact_id']; ?></small></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>
                                            <small>
                                                <?php echo htmlspecialchars($row['phone']); ?><br>
                                                <?php echo htmlspecialchars($row['email'] ?: 'No email'); ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                        <td>
                                            <?php
                                            $status_class = 'secondary';
                                            if ($row['status'] == 'pending') $status_class = 'warning';
                                            if ($row['status'] == 'in_progress') $status_class = 'info';
                                            if ($row['status'] == 'resolved') $status_class = 'success';
                                            ?>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['id']; ?>">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted my-5">No messages found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php if (!empty($contacts)): ?>
        <?php foreach ($contacts as $row): ?>
            <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Message #<?php echo $row['id']; ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="contact_id" value="<?php echo $row['id']; ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6>Customer Information</h6>
                                        <p>
                                            <strong>Name:</strong> <?php echo htmlspecialchars($row['name']); ?><br>
                                            <strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?><br>
                                            <strong>Email:</strong> <?php echo htmlspecialchars($row['email'] ?: 'N/A'); ?><br>
                                            <strong>Contact ID:</strong> <?php echo $row['contact_id']; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Message Details</h6>
                                        <p>
                                            <strong>Subject:</strong> <?php echo htmlspecialchars($row['subject']); ?><br>
                                            <strong>Status:</strong> 
                                            <select name="status" class="form-select form-select-sm d-inline-block w-auto ms-2">
                                                <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_progress" <?php echo $row['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="resolved" <?php echo $row['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                <option value="closed" <?php echo $row['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                            </select>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>Message</h6>
                                    <div class="border p-3 bg-light rounded">
                                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Admin Notes</label>
                                    <input type="text" name="admin_notes" class="form-control" value="<?php echo htmlspecialchars($row['admin_notes'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
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