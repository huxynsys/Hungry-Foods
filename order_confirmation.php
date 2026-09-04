<?php
session_start();

// Check if there's a last order
if (!isset($_SESSION['last_order'])) {
    header('Location: menu.php');
    exit();
}

$order = $_SESSION['last_order'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | Hungry Food</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #4ECDC4;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .confirmation-card {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .confirmation-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .confirmation-icon {
            font-size: 64px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        
        .confirmation-body {
            padding: 40px 30px;
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .order-id {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
        }

        @media (max-width: 575.98px) {
            body { padding: 10px; }
            .confirmation-header { padding: 30px 16px; }
            .confirmation-icon { font-size: 48px; }
            .confirmation-body { padding: 24px 16px; }
            .order-details { padding: 14px; margin: 16px 0; }
            .detail-item {
                flex-direction: column;
                gap: 2px;
            }
            .detail-item span:last-child {
                word-break: break-word;
            }
            .total-amount { font-size: 1.25rem; }
            .display-5 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <div class="confirmation-header">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="display-5 fw-bold mb-3">Order Confirmed!</h1>
            <p class="lead mb-4">Thank you for your order. We're preparing it now.</p>
            <div class="order-id"><?php echo $order['order_id']; ?></div>
        </div>
        
        <div class="confirmation-body">
            <h3 class="mb-4">Order Details</h3>
            
            <div class="order-details">
                <div class="detail-item">
                    <span>Order ID:</span>
                    <span class="fw-bold"><?php echo $order['order_id']; ?></span>
                </div>
                <div class="detail-item">
                    <span>Order Date:</span>
                    <span><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <span>Customer:</span>
                    <span><?php echo $order['customer']['first_name'] . ' ' . $order['customer']['last_name']; ?></span>
                </div>
                <div class="detail-item">
                    <span>Delivery to:</span>
                    <span><?php echo $order['customer']['address']; ?></span>
                </div>
                <div class="detail-item">
                    <span>Payment Method:</span>
                    <span class="text-capitalize"><?php echo $order['payment_method']; ?></span>
                </div>
                <div class="detail-item">
                    <span>Order Type:</span>
                    <span class="text-capitalize"><?php echo $order['order_type']; ?></span>
                </div>
            </div>
            
            <h4 class="mb-3">Order Summary</h4>
            <div class="order-details">
                <div class="detail-item">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="detail-item">
                    <span>Tax:</span>
                    <span>$<?php echo number_format($order['tax'], 2); ?></span>
                </div>
                <div class="detail-item">
                    <span>Delivery Fee:</span>
                    <span>$<?php echo number_format($order['delivery_fee'], 2); ?></span>
                </div>
                <div class="detail-item total-amount">
                    <span>Total:</span>
                    <span>$<?php echo number_format($order['total'], 2); ?></span>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Estimated Delivery:</strong> 30-45 minutes
            </div>
            
            <div class="text-center mt-5">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="menu.php" class="btn btn-primary">
                        <i class="fas fa-utensils me-2"></i>Order More
                    </a>
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
                <p class="text-muted mt-3 mb-0">
                    Need help? Call us at <strong>+1 (555) 123-4567</strong>
                </p>
            </div>
        </div>
    </div>
</body>
</html>