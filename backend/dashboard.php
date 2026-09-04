<?php
// admin/dashboard.php
session_start();
require_once __DIR__ . '/../backend/db.php';   // your PDO connection

// check admin auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

global $conn;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$message = $error = '';

// ========== HELPER FUNCTIONS ==========

// upload image for menu items
function uploadMenuItemImage($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['jpg','jpeg','png','webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return null;
    $dir = __DIR__ . '/../assets/menu/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $filename = time() . '_' . uniqid() . '.' . $ext;
    $dest = $dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return '/hungry-food/assets/menu/' . $filename;
    }
    return null;
}

// ========== HANDLERS ==========

// --- Update Order Status ---
if (isset($_POST['update_order_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $valid = ['pending','processing','delivered','cancelled'];
    if (in_array($status, $valid)) {
        $s = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $s->execute([':status' => $status, ':id' => $order_id]);
        $message = "Order #$order_id updated.";
    }
}

// --- Update Reservation Status ---
if (isset($_POST['update_reservation_status'])) {
    $res_id = (int)$_POST['reservation_id'];
    $status = $_POST['status'];
    $valid = ['pending','confirmed','cancelled','completed'];
    if (in_array($status, $valid)) {
        $s = $conn->prepare("UPDATE reservations SET status = :status WHERE id = :id");
        $s->execute([':status' => $status, ':id' => $res_id]);
        $message = "Reservation #$res_id updated.";
    }
}

// --- Update Contact Status ---
if (isset($_POST['update_contact_status'])) {
    $contact_id = (int)$_POST['contact_id'];
    $status = $_POST['status'];
    $valid = ['pending','in_progress','resolved','closed'];
    if (in_array($status, $valid)) {
        $s = $conn->prepare("UPDATE contacts SET status = :status WHERE id = :id");
        $s->execute([':status' => $status, ':id' => $contact_id]);
        $message = "Contact #$contact_id updated.";
    }
}

// --- Save Menu Item (insert or update) ---
if (isset($_POST['save_menu_item'])) {
    $item_id = $_POST['item_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)$_POST['price'];
    $description = trim($_POST['description'] ?? '');
    $stock = (int)$_POST['stock_quantity'];

    if ($name === '' || $price <= 0) {
        $error = "Name and price are required.";
    } else {
        // handle image upload
        $image_path = null;
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadMenuItemImage($_FILES['item_image']);
        }
        // fallback to existing image if no new one
        $existing_image = $_POST['existing_image'] ?? '';
        if (!$image_path && $existing_image) {
            $image_path = $existing_image;
        }

        if ($item_id) {
            $sql = "UPDATE food_items SET name=:name, category=:cat, price=:price, description=:desc, stock_quantity=:stock, image=:img WHERE id=:id";
            $params = [':name'=>$name,':cat'=>$category,':price'=>$price,':desc'=>$description,':stock'=>$stock,':img'=>$image_path,':id'=>$item_id];
        } else {
            $sql = "INSERT INTO food_items (name, category, price, description, stock_quantity, image) VALUES (:name,:cat,:price,:desc,:stock,:img)";
            $params = [':name'=>$name,':cat'=>$category,':price'=>$price,':desc'=>$description,':stock'=>$stock,':img'=>$image_path];
        }
        $s = $conn->prepare($sql);
        $s->execute($params);
        header('Location: dashboard.php?msg=' . urlencode('Menu item saved.'));
        exit;
    }
}

// --- Delete Menu Item ---
if (isset($_GET['delete_item'])) {
    $id = (int)$_GET['delete_item'];
    $conn->prepare("DELETE FROM food_items WHERE id = :id")->execute([':id'=>$id]);
    header('Location: dashboard.php?msg=' . urlencode('Item deleted.'));
    exit;
}

// ========== DATA FETCHING ==========

// Stats
$stats = [];
$stats['total_revenue'] = $conn->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$stats['total_orders'] = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$stats['pending_orders'] = $conn->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','processing')")->fetchColumn();
$stats['today_revenue'] = $conn->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(order_date) = CURDATE()")->fetchColumn();
$stats['total_items'] = $conn->query("SELECT COUNT(*) FROM food_items")->fetchColumn();
$stats['total_contacts'] = $conn->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$stats['pending_contacts'] = $conn->query("SELECT COUNT(*) FROM contacts WHERE status='pending'")->fetchColumn();
$stats['today_reservations'] = $conn->query("SELECT COUNT(*) FROM reservations WHERE DATE(reservation_date) = CURDATE()")->fetchColumn();

// Recent Orders
$orders = $conn->query("SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN SignUp u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 50")->fetchAll();

// Food Items
$items = $conn->query("SELECT * FROM food_items ORDER BY name")->fetchAll();

// Contacts
$contacts = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 100")->fetchAll();

// Reservations
$reservations = $conn->query("SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC LIMIT 100")->fetchAll();

// Monthly revenue (last 6 months) for chart
$monthly = $conn->query("SELECT DATE_FORMAT(order_date,'%b') as month, COALESCE(SUM(total),0) as revenue FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY YEAR(order_date),MONTH(order_date),DATE_FORMAT(order_date,'%b') ORDER BY MAX(order_date) ASC")->fetchAll();

// Top selling items
$top_items = $conn->query("SELECT oi.product_name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_rev FROM order_items oi GROUP BY oi.product_name ORDER BY total_qty DESC LIMIT 5")->fetchAll();

// Customers
$customers = $conn->query("SELECT id, full_name, email, created_at, (SELECT COUNT(*) FROM orders WHERE user_id = s.id) as orders_count FROM SignUp s WHERE role='user' OR role IS NULL ORDER BY created_at DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Hungry Food</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        /* EXACT SAME DARK THEME AS CLICKNTYPE, ADAPTED COLORS TO ORANGE */
        :root {
            --bg: #121212;
            --primary: #FF6B35;
            --primary-dark: #e55a2b;
            --primary-light: #ff8c5a;
            --accent: #F7931E;
            --accent-dark: #d9801a;
            --accent-light: #ffb347;
            --accent-glow: rgba(247,147,30,0.3);
            --white: #FFFFFF;
            --text-muted: #BBBBBB;
            --card-bg: #1E1E1E;
            --danger: #E74C3C;
            --warning: #F39C12;
            --info: #3498DB;
            --dark-gray: #1a1a1a;
            --light-gray: #2a2a2a;
            --border-color: rgba(255, 255, 255, 0.1);
            --gradient-primary: linear-gradient(135deg, var(--primary), var(--accent));
            --gradient-accent: linear-gradient(135deg, var(--accent), var(--accent-dark));
            --gradient-danger: linear-gradient(135deg, #E74C3C, #C0392B);
            --gradient-warning: linear-gradient(135deg, #F39C12, #E67E22);
            --gradient-info: linear-gradient(135deg, #3498DB, #2980B9);
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 8px 25px rgba(0, 0, 0, 0.2);
            --radius: 20px;
            --radius-sm: 12px;
            --radius-full: 999px;
            --transition: 0.2s ease;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--white);display:flex;min-height:100vh;overflow-x:hidden}
        /* Scrollbar */
        ::-webkit-scrollbar{width:6px}
        ::-webkit-scrollbar-track{background:var(--dark-gray)}
        ::-webkit-scrollbar-thumb{background:var(--gradient-primary);border-radius:10px}

        /* Sidebar */
        .sidebar{width:260px;background:var(--card-bg);border-right:1px solid var(--border-color);display:flex;flex-direction:column;position:fixed;height:100vh;z-index:100;overflow-y:auto}
        .sb-logo{padding:1.5rem;font-size:1.5rem;font-weight:900;background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .sb-user{padding:1rem 1.5rem;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--border-color)}
        .sb-avatar{width:36px;height:36px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700}
        .sb-name{font-weight:600}.sb-role{font-size:0.7rem;color:var(--text-muted)}
        .sb-links{flex:1;padding:1rem}
        .sb-links a{display:flex;align-items:center;gap:12px;padding:0.8rem 1rem;color:var(--text-muted);text-decoration:none;border-radius:var(--radius-sm);transition:all var(--transition);font-weight:500}
        .sb-links a:hover,.sb-links a.active{background:rgba(255,107,53,0.1);color:var(--white)}
        .sb-links a.active{color:var(--primary)}
        .sb-links .sep{height:1px;background:var(--border-color);margin:0.5rem 0}
        .sb-foot{padding:1rem;border-top:1px solid var(--border-color)}
        .logout-btn{display:flex;align-items:center;gap:12px;padding:0.8rem 1rem;color:var(--danger);text-decoration:none;border-radius:var(--radius-sm);transition:var(--transition);font-weight:500}
        .logout-btn:hover{background:rgba(231,76,60,0.1)}

        /* Main */
        .main{margin-left:260px;flex:1;display:flex;flex-direction:column}
        .topbar{padding:1rem 1.5rem;background:var(--card-bg);border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:1rem;position:sticky;top:0;z-index:50}
        .topbar h1{font-size:1.1rem;font-weight:600}
        .topbar .breadcrumb{font-size:0.8rem;color:var(--text-muted)}
        .date-time{font-family:monospace;color:var(--text-muted);font-size:0.8rem}
        .content{padding:1.5rem;display:flex;flex-direction:column;gap:1.5rem}
        .mobile-menu-btn{display:none;background:none;border:none;color:var(--white);font-size:1.3rem;cursor:pointer}

        /* Cards */
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
        .stat-card{background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--radius);padding:1.2rem;display:flex;align-items:center;gap:1rem;transition:all var(--transition)}
        .stat-card:hover{transform:translateY(-3px);border-color:var(--primary);box-shadow:0 0 20px var(--accent-glow)}
        .stat-icon{width:50px;height:50px;background:var(--gradient-primary);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff}
        .stat-value{font-size:1.6rem;font-weight:700;line-height:1.2}
        .stat-label{font-size:0.8rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px}

        /* Tabs */
        .tabs{display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem}
        .tab-btn{padding:0.6rem 1.2rem;border-radius:var(--radius-full);background:transparent;color:var(--text-muted);border:none;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;transition:var(--transition)}
        .tab-btn:hover{background:rgba(255,255,255,0.05);color:var(--white)}
        .tab-btn.active{background:var(--gradient-primary);color:#fff;box-shadow:0 0 15px var(--accent-glow)}
        .tab-pane{display:none;animation:fadeIn 0.5s}
        .tab-pane.active{display:block}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}

        /* Tables */
        .table-responsive{overflow-x:auto;border-radius:var(--radius);border:1px solid var(--border-color);background:var(--card-bg)}
        table{width:100%;border-collapse:collapse}
        th,td{padding:0.8rem 1rem;text-align:left;border-bottom:1px solid var(--border-color)}
        th{color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:1px}
        tr:hover{background:rgba(255,255,255,0.02)}
        .badge{padding:0.2rem 0.7rem;border-radius:20px;font-size:0.7rem;font-weight:600;display:inline-flex;align-items:center;gap:4px}
        .bg-warning{background:rgba(243,156,18,0.2);color:#F39C12}
        .bg-success{background:rgba(39,174,96,0.2);color:#27ae60}
        .bg-danger{background:rgba(231,76,60,0.2);color:#E74C3C}
        .bg-info{background:rgba(52,152,219,0.2);color:#3498DB}
        .btn{padding:0.45rem 1rem;border-radius:var(--radius-full);font-weight:600;font-size:0.85rem;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:var(--transition);text-decoration:none}
        .btn-primary{background:var(--gradient-primary);color:#fff}
        .btn-primary:hover{filter:brightness(1.1)}
        .btn-sm{padding:0.3rem 0.8rem;font-size:0.75rem}
        .btn-danger{background:var(--gradient-danger);color:#fff}
        .btn-info{background:var(--gradient-info);color:#fff}
        .stock-input{width:80px;padding:0.4rem;border-radius:8px;border:1px solid var(--border-color);background:rgba(255,255,255,0.05);color:#fff}

        /* Forms */
        .card{border:1px solid var(--border-color);border-radius:var(--radius);background:var(--card-bg)}
        .card-header{padding:1rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:10px}
        .card-body{padding:1.2rem}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        .form-group label{display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem}
        .form-control{width:100%;padding:0.7rem;background:var(--light-gray);border:1px solid var(--border-color);border-radius:var(--radius-sm);color:#fff;font-family:inherit}
        .form-control:focus{border-color:var(--primary)}

        /* Charts */
        .chart-card{background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--radius);padding:1.2rem}
        .chart-card h3{margin-bottom:1rem;font-size:1rem;display:flex;align-items:center;gap:10px}
        canvas{width:100%!important;height:300px!important}

        /* Responsive */
        @media(max-width:1000px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.open{transform:translateX(0)}
            .main{margin-left:0}
            .mobile-menu-btn{display:block}
            .form-grid{grid-template-columns:1fr}
            .stats-grid{grid-template-columns:1fr 1fr}
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sb-logo">🍔 Hungry Food</div>
        <div class="sb-user">
            <div class="sb-avatar"><?php echo strtoupper(substr($admin_name,0,2)); ?></div>
            <div>
                <div class="sb-name"><?php echo htmlspecialchars($admin_name); ?></div>
                <div class="sb-role">Administrator</div>
            </div>
        </div>
        <div class="sb-links">
            <a href="#" class="nav-link active" data-tab="overview"><i class="fas fa-chart-line"></i> Overview</a>
            <a href="#" class="nav-link" data-tab="orders"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="#" class="nav-link" data-tab="menu"><i class="fas fa-utensils"></i> Menu</a>
            <a href="#" class="nav-link" data-tab="contacts"><i class="fas fa-envelope"></i> Messages</a>
            <a href="#" class="nav-link" data-tab="reservations"><i class="fas fa-calendar-check"></i> Reservations</a>
            <a href="#" class="nav-link" data-tab="analytics"><i class="fas fa-chart-pie"></i> Analytics</a>
            <a href="#" class="nav-link" data-tab="customers"><i class="fas fa-users"></i> Customers</a>
            <div class="sep"></div>
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
            <a href="../menu.php"><i class="fas fa-book-open"></i> Menu</a>
        </div>
        <div class="sb-foot">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main">
        <div class="topbar">
            <button class="mobile-menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>
            <div>
                <div class="breadcrumb">Admin / Dashboard</div>
                <h1>Welcome, <?php echo htmlspecialchars($admin_name); ?></h1>
            </div>
            <div style="flex:1"></div>
            <div class="date-time">
                <i class="far fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?>
            </div>
        </div>

        <div class="content">
            <?php if ($message): ?><div class="alert success"><?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>

            <!-- Tab buttons -->
            <div class="tabs" id="tabButtons">
                <button class="tab-btn active" data-tab="overview"><i class="fas fa-chart-line"></i> Overview</button>
                <button class="tab-btn" data-tab="orders"><i class="fas fa-shopping-cart"></i> Orders</button>
                <button class="tab-btn" data-tab="menu"><i class="fas fa-utensils"></i> Menu</button>
                <button class="tab-btn" data-tab="contacts"><i class="fas fa-envelope"></i> Messages</button>
                <button class="tab-btn" data-tab="reservations"><i class="fas fa-calendar-check"></i> Reservations</button>
                <button class="tab-btn" data-tab="analytics"><i class="fas fa-chart-pie"></i> Analytics</button>
                <button class="tab-btn" data-tab="customers"><i class="fas fa-users"></i> Customers</button>
            </div>

            <!-- Overview Tab -->
            <div class="tab-pane active" id="overview">
                <div class="stats-grid">
                    <div class="stat-card"><div class="stat-icon"><i class="fas fa-dollar-sign"></i></div><div><div class="stat-value">$<?php echo number_format($stats['total_revenue'],2);?></div><div class="stat-label">Total Revenue</div></div></div>
                    <div class="stat-card"><div class="stat-icon"><i class="fas fa-shopping-bag"></i></div><div><div class="stat-value"><?php echo $stats['total_orders'];?></div><div class="stat-label">Orders</div><div style="font-size:0.75rem"><?php echo $stats['pending_orders'];?> pending</div></div></div>
                    <div class="stat-card"><div class="stat-icon"><i class="fas fa-calendar-day"></i></div><div><div class="stat-value">$<?php echo number_format($stats['today_revenue'],2);?></div><div class="stat-label">Today Revenue</div></div></div>
                    <div class="stat-card"><div class="stat-icon"><i class="fas fa-utensils"></i></div><div><div class="stat-value"><?php echo $stats['total_items'];?></div><div class="stat-label">Menu Items</div></div></div>
                    <div class="stat-card"><div class="stat-icon"><i class="fas fa-envelope"></i></div><div><div class="stat-value"><?php echo $stats['pending_contacts'];?></div><div class="stat-label">Pending Messages</div></div></div>
                    <div class="stat-card"><div class="stat-icon"><i class="fas fa-calendar-check"></i></div><div><div class="stat-value"><?php echo $stats['today_reservations'];?></div><div class="stat-label">Today Reservations</div></div></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1rem">
                    <div class="chart-card"><h3><i class="fas fa-chart-bar"></i> Monthly Revenue</h3><canvas id="monthlyChart"></canvas></div>
                    <div class="chart-card"><h3><i class="fas fa-star"></i> Top 5 Items</h3>
                        <ul style="list-style:none">
                            <?php foreach($top_items as $item): ?>
                            <li style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid var(--border-color)">
                                <span><?php echo htmlspecialchars($item['product_name']);?></span>
                                <span style="color:var(--accent)"><?php echo $item['total_qty'];?> sold ($<?php echo number_format($item['total_rev']);?>)</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Orders Tab -->
            <div class="tab-pane" id="orders">
                <div class="table-responsive">
                    <table><thead><tr><th>ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id'];?></td>
                            <td><?php echo htmlspecialchars($order['full_name']??'Guest');?><br><small><?php echo $order['email']??'';?></small></td>
                            <td><?php echo date('M d, Y',strtotime($order['order_date']));?></td>
                            <td>$1($order['total'],2);?></td>
                            <td><span class="badge bg-<?php echo $order['status']=='delivered'?'success':($order['status']=='cancelled'?'danger':'warning');?>"><?php echo ucfirst($order['status']);?></span></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id'];?>">
                                    <select name="status" onchange="this.form.submit()" style="padding:0.3rem;background:#2a2a2a;color:#fff;border:1px solid var(--border-color);border-radius:8px">
                                        <option <?php echo $order['status']=='pending'?'selected':'';?>>pending</option>
                                        <option <?php echo $order['status']=='processing'?'selected':'';?>>processing</option>
                                        <option <?php echo $order['status']=='delivered'?'selected':'';?>>delivered</option>
                                        <option <?php echo $order['status']=='cancelled'?'selected':'';?>>cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_order_status" value="1">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>

            <!-- Menu Tab -->
            <div class="tab-pane" id="menu">
                <div class="card">
                    <div class="card-header"><i class="fas fa-utensils"></i> Add / Edit Menu Item</div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="item_id" id="edit_id">
                            <input type="hidden" name="existing_image" id="existing_img">
                            <div class="form-grid">
                                <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required id="item_name"></div>
                                <div class="form-group"><label>Category</label><input type="text" name="category" class="form-control" id="item_cat"></div>
                                <div class="form-group"><label>Price *</label><input type="number" step="0.01" name="price" class="form-control" required id="item_price"></div>
                                <div class="form-group"><label>Stock</label><input type="number" name="stock_quantity" class="form-control" value="100" id="item_stock"></div>
                                <div class="form-group" style="grid-column:1/-1"><label>Description</label><textarea name="description" class="form-control" id="item_desc"></textarea></div>
                                <div class="form-group"><label>Image</label><input type="file" name="item_image" class="form-control" accept="image/*"></div>
                                <div><button type="submit" name="save_menu_item" class="btn btn-primary">Save Item</button></div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive" style="margin-top:1rem">
                    <table><thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($item['image']??'');?>" style="width:40px;height:40px;object-fit:cover;border-radius:8px" onerror="this.style.display='none'"></td>
                            <td><?php echo htmlspecialchars($item['name']);?></td>
                            <td><?php echo htmlspecialchars($item['category']);?></td>
                            <td>$1($item['price'],2);?></td>
                            <td><?php echo $item['stock_quantity'];?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editItem(<?php echo htmlspecialchars(json_encode($item));?>)"><i class="fas fa-edit"></i></button>
                                <a href="?delete_item=<?php echo $item['id'];?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>

            <!-- Contacts Tab -->
            <div class="tab-pane" id="contacts">
                <div class="table-responsive">
                    <table><thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Subject</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($contacts as $contact): ?>
                        <tr>
                            <td>#<?php echo $contact['id'];?></td>
                            <td><?php echo htmlspecialchars($contact['name']);?></td>
                            <td><?php echo $contact['phone'];?></td>
                            <td><?php echo htmlspecialchars($contact['subject']);?></td>
                            <td><?php echo date('M d',strtotime($contact['created_at']));?></td>
                            <td><span class="badge bg-<?php echo $contact['status']=='resolved'?'success':'warning';?>"><?php echo $contact['status'];?></span></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="contact_id" value="<?php echo $contact['id'];?>">
                                    <select name="status" onchange="this.form.submit()" style="padding:0.3rem;background:#2a2a2a;color:#fff;border:1px solid var(--border-color);border-radius:8px">
                                        <option <?php echo $contact['status']=='pending'?'selected':'';?>>pending</option>
                                        <option <?php echo $contact['status']=='in_progress'?'selected':'';?>>in_progress</option>
                                        <option <?php echo $contact['status']=='resolved'?'selected':'';?>>resolved</option>
                                        <option <?php echo $contact['status']=='closed'?'selected':'';?>>closed</option>
                                    </select>
                                    <input type="hidden" name="update_contact_status" value="1">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>

            <!-- Reservations Tab -->
            <div class="tab-pane" id="reservations">
                <div class="table-responsive">
                    <table><thead><tr><th>ID</th><th>Name</th><th>Date</th><th>Time</th><th>Guests</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($reservations as $res): ?>
                        <tr>
                            <td>#<?php echo $res['id'];?></td>
                            <td><?php echo htmlspecialchars($res['name']);?></td>
                            <td><?php echo $res['reservation_date'];?></td>
                            <td><?php echo $res['reservation_time'];?></td>
                            <td><?php echo $res['guests'];?></td>
                            <td><span class="badge bg-<?php echo $res['status']=='confirmed'?'success':'warning';?>"><?php echo $res['status'];?></span></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="reservation_id" value="<?php echo $res['id'];?>">
                                    <select name="status" onchange="this.form.submit()" style="padding:0.3rem;background:#2a2a2a;color:#fff;border:1px solid var(--border-color);border-radius:8px">
                                        <option <?php echo $res['status']=='pending'?'selected':'';?>>pending</option>
                                        <option <?php echo $res['status']=='confirmed'?'selected':'';?>>confirmed</option>
                                        <option <?php echo $res['status']=='completed'?'selected':'';?>>completed</option>
                                        <option <?php echo $res['status']=='cancelled'?'selected':'';?>>cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_reservation_status" value="1">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div class="tab-pane" id="analytics">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div class="chart-card"><h3><i class="fas fa-chart-line"></i> Monthly Revenue</h3><canvas id="analyticsMonthly"></canvas></div>
                    <div class="chart-card"><h3><i class="fas fa-chart-pie"></i> Order Status</h3><canvas id="orderPie"></canvas></div>
                </div>
            </div>

            <!-- Customers Tab -->
            <div class="tab-pane" id="customers">
                <div class="table-responsive">
                    <table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Joined</th><th>Orders</th></tr></thead>
                    <tbody>
                        <?php foreach($customers as $cust): ?>
                        <tr>
                            <td>#<?php echo $cust['id'];?></td>
                            <td><?php echo htmlspecialchars($cust['full_name']);?></td>
                            <td><?php echo $cust['email'];?></td>
                            <td><?php echo date('M d, Y',strtotime($cust['created_at']));?></td>
                            <td><?php echo $cust['orders_count'];?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js scripts -->
    <script>
        // Monthly chart
        const monthlyData = <?php echo json_encode($monthly); ?>;
        const ctx1 = document.getElementById('monthlyChart')?.getContext('2d');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: monthlyData.map(m => m.month),
                    datasets: [{
                        label: 'Revenue ($)',
                        data: monthlyData.map(m => m.revenue),
                        borderColor: '#FF6B35',
                        backgroundColor: 'rgba(255,107,53,0.1)',
                        tension: 0.4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { grid: { color: 'rgba(255,255,255,0.1)' } }, x: { grid: { color: 'rgba(255,255,255,0.1)' } } } }
            });
        }

        // Analytics charts
        const ctx2 = document.getElementById('analyticsMonthly')?.getContext('2d');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: monthlyData.map(m => m.month),
                    datasets: [{ label: 'Revenue', data: monthlyData.map(m => m.revenue), backgroundColor: '#FF6B35' }]
                },
                options: { scales: { y: { grid: { color: 'rgba(255,255,255,0.1)' } }, x: { grid: { color: 'rgba(255,255,255,0.1)' } } } }
            });
        }
        const orderStatus = { pending: <?php echo $stats['pending_orders'];?>, delivered: <?php echo $stats['total_orders']-$stats['pending_orders'];?> };
        const ctx3 = document.getElementById('orderPie')?.getContext('2d');
        if (ctx3) {
            new Chart(ctx3, {
                type: 'doughnut',
                data: {
                    labels: ['Pending/Processing','Delivered'],
                    datasets: [{ data: [orderStatus.pending,orderStatus.delivered], backgroundColor: ['#F39C12','#27ae60'] }]
                }
            });
        }

        // Tab switching
        document.querySelectorAll('.tab-btn, .nav-link[data-tab]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const tab = this.dataset.tab;
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                document.getElementById(tab).classList.add('active');
                document.querySelectorAll('.tab-btn,.nav-link').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Edit menu item (pre-fill form)
        function editItem(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('item_name').value = item.name;
            document.getElementById('item_cat').value = item.category || '';
            document.getElementById('item_price').value = item.price;
            document.getElementById('item_stock').value = item.stock_quantity || 0;
            document.getElementById('item_desc').value = item.description || '';
            document.getElementById('existing_img').value = item.image || '';
            // switch to menu tab
            document.querySelector('.tab-btn[data-tab="menu"]').click();
            window.scrollTo(0,0);
        }
    </script>
</body>
</html>