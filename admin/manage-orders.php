<?php
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit();
}

// DB connection using PDO (secure)
require_once __DIR__ . '/../backend/db.php';
global $conn;

// Ensure orders table exists
$conn->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    customer_address TEXT,
    customer_city VARCHAR(50),
    items_json LONGTEXT,
    subtotal DECIMAL(10,2),
    tax DECIMAL(10,2),
    delivery_fee DECIMAL(10,2),
    total DECIMAL(10,2),
    payment_method VARCHAR(30),
    order_type VARCHAR(20),
    status VARCHAR(20) DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle status update with prepared statement (secure against SQL injection)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("Location: manage-orders.php?error=invalid_token");
        exit();
    }
    
    $oid    = $_POST['order_id'];
    $status = $_POST['status'];
    
    // Validate status value
    $valid_statuses = ['pending', 'processing', 'delivered', 'cancelled'];
    if (in_array($status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
        $stmt->execute([':status' => $status, ':order_id' => $oid]);
    }
    
    header("Location: manage-orders.php?updated=1");
    exit();
}

// Fetch all orders newest first
$stmt = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Stats
$totalOrders   = count($orders);
$pendingOrders = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$totalRevenue  = array_sum(array_column($orders, 'total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | Hungry Food Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            width: 240px; min-height: 100vh;
            background: #1a1a2e; position: fixed; top: 0; left: 0;
            padding-top: 1rem; z-index: 100;
        }
        .sidebar-brand {
            color: #FF6B35; font-size: 1.3rem; font-weight: 700;
            padding: 1rem 1.5rem 1.5rem; display: block;
            text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar a {
            display: flex; align-items: center; gap: 0.75rem;
            color: rgba(255,255,255,0.7); padding: 0.75rem 1.5rem;
            text-decoration: none; font-size: 0.9rem; transition: all 0.2s;
        }
        .sidebar a:hover, .sidebar a.active { color: white; background: rgba(255,107,53,0.2); border-left: 3px solid #FF6B35; }
        .main { margin-left: 240px; padding: 2rem; }
        .topbar {
            background: white; padding: 1rem 1.5rem; border-radius: 10px;
            margin-bottom: 1.5rem; display: flex; justify-content: space-between;
            align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card {
            background: white; border-radius: 12px; padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid;
        }
        .stat-card.orange { border-color: #FF6B35; }
        .stat-card.blue   { border-color: #4ECDC4; }
        .stat-card.green  { border-color: #28a745; }
        .stat-card .number { font-size: 2rem; font-weight: 700; }
        .stat-card .label  { color: #6c757d; font-size: 0.85rem; }

        .orders-table { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; }
        .orders-table .table { margin: 0; }
        .orders-table .table th { background: #f8f9fa; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border: none; padding: 1rem; }
        .orders-table .table td { padding: 1rem; vertical-align: middle; border-color: #f0f0f0; }
        .orders-table .table tr:hover td { background: #fafafa; }

        .badge-status { padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .status-pending    { background: #fff3cd; color: #856404; }
        .status-confirmed  { background: #d1ecf1; color: #0c5460; }
        .status-preparing  { background: #cce5ff; color: #004085; }
        .status-delivered  { background: #d4edda; color: #155724; }
        .status-cancelled  { background: #f8d7da; color: #721c24; }

        .order-id { font-family: monospace; font-size: 0.82rem; color: #FF6B35; font-weight: 600; }
        .items-preview { font-size: 0.82rem; color: #6c757d; max-width: 200px; }
        .total-amt { font-weight: 700; color: #28a745; }

        .modal-header { background: #1a1a2e; color: white; }
        .detail-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; }
        .detail-row:last-child { border: none; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: #6c757d; }
        .empty-state i { font-size: 4rem; opacity: 0.3; margin-bottom: 1rem; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="dashboard.php" class="sidebar-brand"><i class="fas fa-utensils me-2"></i>Hungry Food</a>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="manage-orders.php" class="active"><i class="fas fa-shopping-bag"></i> Orders</a>
    <a href="admin-reservations.php"><i class="fas fa-calendar-alt"></i> Reservations</a>
    <a href="manage-offers.php"><i class="fas fa-tags"></i> Offers</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="topbar">
        <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-bag me-2 text-warning"></i>Manage Orders</h5>
        <span class="text-muted small"><?php echo date('l, F j Y'); ?></span>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>Order status updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card orange">
                <div class="number"><?php echo $totalOrders; ?></div>
                <div class="label"><i class="fas fa-shopping-bag me-1"></i>Total Orders</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card blue">
                <div class="number text-warning"><?php echo $pendingOrders; ?></div>
                <div class="label"><i class="fas fa-clock me-1"></i>Pending Orders</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card green">
                <div class="number text-success">$1($totalRevenue, 0); ?></div>
                <div class="label"><i class="fas fa-coins me-1"></i>Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- ORDERS TABLE -->
    <div class="orders-table">
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag d-block"></i>
                <h5>No orders yet</h5>
                <p>Orders placed by customers will appear here.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order):
                        $items = json_decode($order['items_json'] ?? '[]', true);
                        $itemNames = array_map(fn($i) => ($i['name'] ?? 'Item') . ' x' . ($i['quantity'] ?? 1), array_slice($items ?: [], 0, 2));
                        $itemPreview = implode(', ', $itemNames) . (count($items) > 2 ? ' +' . (count($items) - 2) . ' more' : '');
                    ?>
                    <tr>
                        <td><span class="order-id"><?php echo htmlspecialchars($order['order_id']); ?></span></td>
                        <td>
                            <div class="fw-semibold" style="font-size:0.9rem"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            <div class="text-muted" style="font-size:0.78rem"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                        </td>
                        <td><div class="items-preview"><?php echo htmlspecialchars($itemPreview ?: 'See details'); ?></div></td>
                        <td><span class="total-amt">$1($order['total'], 0); ?></span></td>
                        <td><span class="text-capitalize"><?php echo htmlspecialchars($order['payment_method']); ?></span></td>
                        <td><span class="badge bg-secondary text-capitalize"><?php echo htmlspecialchars($order['order_type']); ?></span></td>
                        <td>
                            <span class="badge-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td style="font-size:0.82rem"><?php echo date('d M Y', strtotime($order['created_at'])); ?><br>
                            <span class="text-muted"><?php echo date('h:i A', strtotime($order['created_at'])); ?></span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" onclick='showDetail(<?php echo json_encode($order); ?>)'>
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ORDER DETAIL MODAL -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Order Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <form method="POST" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="order_id" id="modal_order_id">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                    <select name="status" class="form-select form-select-sm" style="width:auto">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="preparing">Preparing</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-sm btn-warning fw-bold">Update Status</button>
                </form>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// XSS Prevention: Escape HTML entities
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showDetail(order) {
    document.getElementById('modal_order_id').value = escapeHtml(order.order_id);

    let items = [];
    try { items = JSON.parse(order.items_json || '[]'); } catch(e) {}

    // Escape item data to prevent XSS
    let itemsHtml = items.map(i =>
        `<tr><td>${escapeHtml(i.name)||'Item'}</td><td>${escapeHtml(i.quantity)||1}</td><td>$1parseFloat(i.price||0).toLocaleString()}</td></tr>`
    ).join('');

    // Escape all user data before inserting into HTML
    document.getElementById('modalBody').innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="fw-bold text-muted mb-2">CUSTOMER INFO</h6>
                <div class="detail-row"><span>Name</span><span class="fw-semibold">${escapeHtml(order.customer_name)||'-'}</span></div>
                <div class="detail-row"><span>Phone</span><span>${escapeHtml(order.customer_phone)||'-'}</span></div>
                <div class="detail-row"><span>Email</span><span>${escapeHtml(order.customer_email)||'-'}</span></div>
                <div class="detail-row"><span>Address</span><span>${escapeHtml(order.customer_address)||'-'}, ${escapeHtml(order.customer_city)||''}</span></div>
                <div class="detail-row"><span>Notes</span><span class="text-muted">${escapeHtml(order.notes)||'None'}</span></div>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-muted mb-2">ORDER INFO</h6>
                <div class="detail-row"><span>Order ID</span><span class="text-warning fw-bold">${escapeHtml(order.order_id)}</span></div>
                <div class="detail-row"><span>Type</span><span class="text-capitalize">${escapeHtml(order.order_type)||'-'}</span></div>
                <div class="detail-row"><span>Payment</span><span class="text-capitalize">${escapeHtml(order.payment_method)||'-'}</span></div>
                <div class="detail-row"><span>Status</span><span class="text-capitalize fw-bold">${escapeHtml(order.status)}</span></div>
                <div class="detail-row"><span>Date</span><span>${new Date(order.created_at).toLocaleString()}</span></div>
            </div>
        </div>
        <hr>
        <h6 class="fw-bold text-muted mb-2">ITEMS ORDERED</h6>
        <table class="table table-sm table-bordered">
            <thead class="table-light"><tr><th>Item</th><th>Qty</th><th>Price</th></tr></thead>
            <tbody>${itemsHtml || '<tr><td colspan="3" class="text-center text-muted">No item details</td></tr>'}</tbody>
        </table>
        <div class="d-flex flex-column align-items-end gap-1 mt-2">
            <div>Subtotal: <strong>$1parseFloat(order.subtotal||0).toLocaleString()}</strong></div>
            <div>Tax: <strong>$1parseFloat(order.tax||0).toLocaleString()}</strong></div>
            <div>Delivery Fee: <strong>$1parseFloat(order.delivery_fee||0).toLocaleString()}</strong></div>
            <div class="fs-5">Total: <strong class="text-success">$1parseFloat(order.total||0).toLocaleString()}</strong></div>
        </div>
    `;

    // Set current status in dropdown
    const sel = document.querySelector('#orderModal select[name=status]');
    sel.value = order.status;

    new bootstrap.Modal(document.getElementById('orderModal')).show();
}
</script>
</body>
</html>
