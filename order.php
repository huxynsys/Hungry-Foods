<?php
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Add cache control headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Use the central PDO database connection
require_once __DIR__ . '/backend/db.php';
global $conn;

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Initialize catering order if not exists
if (!isset($_SESSION['catering_order'])) {
    $_SESSION['catering_order'] = [];
}

// Menu items data for reference
$menu_items = [
    'spring_rolls_1' => [
        'name' => 'Vegetable Spring Rolls',
        'price' => 8.99,
        'image' => 'https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'appetizers'
    ],
    'garlic_bread_2' => [
        'name' => 'Garlic Breadsticks',
        'price' => 7.99,
        'image' => 'https://images.unsplash.com/photo-1608039755401-742074f0548d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'appetizers'
    ],
    'steak_3' => [
        'name' => 'Grilled Ribeye Steak',
        'price' => 28.99,
        'image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'main-course'
    ],
    'pizza_margherita_4' => [
        'name' => 'Margherita Pizza',
        'price' => 14.99,
        'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'pizza'
    ],
    'burger_classic_5' => [
        'name' => 'Classic Cheeseburger',
        'price' => 12.99,
        'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'burgers'
    ],
    'cake_chocolate_6' => [
        'name' => 'Chocolate Lava Cake',
        'price' => 8.99,
        'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'desserts'
    ],
    'juice_orange_7' => [
        'name' => 'Fresh Orange Juice',
        'price' => 4.99,
        'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'drinks'
    ],
    'salmon_8' => [
        'name' => 'Grilled Salmon',
        'price' => 22.99,
        'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'main-course'
    ],
    'pasta_alfredo_9' => [
        'name' => 'Fettuccine Alfredo',
        'price' => 16.99,
        'image' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'main-course'
    ],
    'caesar_salad_10' => [
        'name' => 'Caesar Salad',
        'price' => 9.99,
        'image' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'category' => 'appetizers'
    ]
];

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token. Please refresh the page and try again.';
        header('Location: order.php');
        exit();
    }
    
    // Update regular item quantity
    if (isset($_POST['update_quantity'])) {
        $item_id = $_POST['item_id'];
        $quantity = intval($_POST['quantity']);
        
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $item_id) {
                if ($quantity <= 0) {
                    // Remove item if quantity is 0 or less
                    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($i) use ($item_id) {
                        return $i['id'] != $item_id;
                    });
                    $_SESSION['success_message'] = "Item removed from cart";
                } else {
                    $item['quantity'] = $quantity;
                    $_SESSION['success_message'] = "Cart updated successfully";
                }
                break;
            }
        }
        // Re-index array to prevent gaps
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    
    // Remove regular item from cart
    if (isset($_POST['remove_item'])) {
        $item_id = $_POST['item_id'];
        
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($item_id) {
            return $item['id'] != $item_id;
        });
        // Re-index array
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        
        $_SESSION['success_message'] = "Item removed from cart";
    }
    
    // Remove catering package
    if (isset($_POST['remove_catering'])) {
        $_SESSION['catering_order'] = [];
        $_SESSION['success_message'] = "Catering package removed from cart";
    }
    
    // Update catering package
    if (isset($_POST['update_catering'])) {
        $persons = intval($_POST['persons']);
        $event_date = $_POST['event_date'];
        
        if ($persons >= 10 && $persons <= 500) {
            // Preserve existing catering data
            $_SESSION['catering_order']['persons'] = $persons;
            $_SESSION['catering_order']['event_date'] = $event_date;
            $_SESSION['success_message'] = "Catering package updated successfully";
        } else {
            $_SESSION['error_message'] = "Number of persons must be between 10 and 500";
        }
    }
    
    // Clear all items
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $_SESSION['catering_order'] = [];
        $_SESSION['success_message'] = "Cart cleared successfully";
    }
    
    // Handle order submission
    if (isset($_POST['place_order'])) {
        // Check if cart is empty
        if (empty($_SESSION['cart']) && empty($_SESSION['catering_order'])) {
            $_SESSION['error_message'] = "Your cart is empty. Add items before placing an order.";
            header('Location: order.php');
            exit();
        }
        
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'zipcode'];
        $is_valid = true;
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $is_valid = false;
                $_SESSION['error_message'] = "Please fill in all required fields";
                break;
            }
        }
        
        // Validate email
        if ($is_valid && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $is_valid = false;
            $_SESSION['error_message'] = "Please enter a valid email address";
        }
        
        // Validate phone (Pakistani format)
        if ($is_valid) {
            $phone = preg_replace('/\D/', '', $_POST['phone']);
            if (strlen($phone) !== 11 || !preg_match('/^03[0-9]{9}$/', $phone)) {
                $is_valid = false;
                $_SESSION['error_message'] = "Please enter a valid Pakistani phone number (03XXXXXXXXX)";
            }
        }
        
        if ($is_valid) {
            // Calculate totals
            $order_summary = calculateOrderSummary();
            
            // Calculate delivery time estimate
            $current_time = time();
            if (!empty($_SESSION['catering_order'])) {
                $delivery_time = date('F j, Y', strtotime($_SESSION['catering_order']['event_date'])) . " (Event Date)";
            } else {
                $order_type = $_POST['order_type'] ?? 'delivery';
                if ($order_type === 'pickup') {
                    $delivery_time = date('h:i A', $current_time + 30*60) . " (Ready for pickup)";
                } else {
                    $delivery_time = date('h:i A', $current_time + 45*60) . " (Estimated delivery)";
                }
            }
            
            // Save order details in session
            $order_id = 'HF' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            
            $order_details = [
                'order_id' => $order_id,
                'order_date' => date('Y-m-d H:i:s'),
                'delivery_time' => $delivery_time,
                'customer' => [
                    'first_name' => htmlspecialchars($_POST['first_name']),
                    'last_name' => htmlspecialchars($_POST['last_name']),
                    'email' => htmlspecialchars($_POST['email']),
                    'phone' => htmlspecialchars($_POST['phone']),
                    'address' => htmlspecialchars($_POST['address']),
                    'city' => htmlspecialchars($_POST['city']),
                    'state' => htmlspecialchars($_POST['state'] ?? 'Punjab'),
                    'zipcode' => htmlspecialchars($_POST['zipcode']),
                    'instructions' => htmlspecialchars($_POST['instructions'] ?? '')
                ],
                'items' => $_SESSION['cart'],
                'catering' => !empty($_SESSION['catering_order']) ? $_SESSION['catering_order'] : null,
                'subtotal' => $order_summary['subtotal'],
                'tax' => $order_summary['tax'],
                'delivery_fee' => $order_summary['delivery_fee'],
                'catering_delivery_fee' => $order_summary['catering_delivery_fee'],
                'total' => $order_summary['total'],
                'payment_method' => htmlspecialchars($_POST['payment_method'] ?? 'cash'),
                'order_type' => htmlspecialchars($_POST['order_type'] ?? 'delivery')
            ];
            
            // Save order in session
            $_SESSION['last_order'] = $order_details;
            
            // Also store in orders history
            if (!isset($_SESSION['orders_history'])) {
                $_SESSION['orders_history'] = [];
            }
            $_SESSION['orders_history'][] = $order_details;

            // ── SAVE ORDER TO DATABASE (PDO, matches central connection) ──
            try {
                // Create orders table if needed
                $conn->exec("CREATE TABLE IF NOT EXISTS orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id VARCHAR(50) UNIQUE NOT NULL,
                    user_id INT DEFAULT NULL,
                    customer_name VARCHAR(100),
                    customer_email VARCHAR(100),
                    customer_phone VARCHAR(20),
                    customer_address TEXT,
                    customer_city VARCHAR(50),
                    items_json LONGTEXT,
                    subtotal DECIMAL(10,2) DEFAULT 0,
                    tax DECIMAL(10,2) DEFAULT 0,
                    delivery_fee DECIMAL(10,2) DEFAULT 0,
                    total DECIMAL(10,2) DEFAULT 0,
                    payment_method VARCHAR(30),
                    order_type VARCHAR(20),
                    status VARCHAR(20) DEFAULT 'pending',
                    notes TEXT,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Create order_items table if needed
                $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    menu_item_id INT DEFAULT NULL,
                    item_name VARCHAR(200) NOT NULL,
                    item_code VARCHAR(20),
                    quantity INT DEFAULT 1,
                    price DECIMAL(10,2) DEFAULT 0,
                    subtotal DECIMAL(10,2) DEFAULT 0,
                    special_instructions TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    KEY order_id (order_id),
                    KEY menu_item_id (menu_item_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Insert order
                $_name  = $order_details['customer']['first_name'] . ' ' . $order_details['customer']['last_name'];
                $_notes = $order_details['customer']['instructions'] ?? '';
                $_uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

                $_st = $conn->prepare("INSERT INTO orders (order_id, user_id, customer_name, customer_email, customer_phone, customer_address, customer_city, payment_method, order_type, subtotal, tax, delivery_fee, total, notes, status) VALUES (:order_id, :user_id, :customer_name, :customer_email, :customer_phone, :customer_address, :customer_city, :payment_method, :order_type, :subtotal, :tax, :delivery_fee, :total, :notes, 'pending')");
                $_st->execute([
                    ':order_id'       => $order_details['order_id'],
                    ':user_id'        => $_uid,
                    ':customer_name'  => $_name,
                    ':customer_email' => $order_details['customer']['email'],
                    ':customer_phone' => $order_details['customer']['phone'],
                    ':customer_address' => $order_details['customer']['address'],
                    ':customer_city'  => $order_details['customer']['city'],
                    ':payment_method' => $order_details['payment_method'],
                    ':order_type'     => $order_details['order_type'],
                    ':subtotal'       => $order_details['subtotal'],
                    ':tax'            => $order_details['tax'],
                    ':delivery_fee'   => $order_details['delivery_fee'],
                    ':total'          => $order_details['total'],
                    ':notes'          => $_notes
                ]);
                $_inserted_id = (int)$conn->lastInsertId();

                // Persist line items as JSON so the admin order-detail view can render them
                if ($_inserted_id) {
                    $line_items = [];
                    foreach (($order_details['items'] ?? []) as $_item) {
                        $line_items[] = [
                            'name'     => $_item['name'] ?? 'Item',
                            'quantity' => (int)($_item['quantity'] ?? 1),
                            'price'    => (float)($_item['price'] ?? 0)
                        ];
                    }
                    if (!empty($order_details['catering'])) {
                        $_c = $order_details['catering'];
                        $line_items[] = [
                            'name'     => 'Catering: ' . (isset($_c['name']) ? $_c['name'] : 'Package'),
                            'quantity' => (int)($_c['persons'] ?? 1),
                            'price'    => (float)(($_c['price_per_person'] ?? 0) * ($_c['persons'] ?? 1))
                        ];
                    }
                    $_items_json = json_encode($line_items, JSON_UNESCAPED_UNICODE);
                    $_upd = $conn->prepare("UPDATE orders SET items_json = :items_json WHERE id = :id");
                    $_upd->execute([':items_json' => $_items_json, ':id' => $_inserted_id]);
                }

                // Insert order items
                if ($_inserted_id && !empty($order_details['items'])) {
                    $_ist = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, price) VALUES (:order_id, :item_name, :quantity, :price)");
                    foreach ($order_details['items'] as $_item) {
                        $_iname = $_item['name'] ?? 'Item';
                        $_iqty  = (int)($_item['quantity'] ?? 1);
                        $_iprice = (float)($_item['price'] ?? 0);
                        $_ist->execute([
                            ':order_id'  => $_inserted_id,
                            ':item_name' => $_iname,
                            ':quantity'  => $_iqty,
                            ':price'     => $_iprice
                        ]);
                    }
                }
            } catch (Exception $_e) { error_log('Order DB save: ' . $_e->getMessage()); }
            // ────────────────────────────────────────────────────────

            
            // Clear cart after successful order
            $_SESSION['cart'] = [];
            $_SESSION['catering_order'] = [];
            
            // Clear any saved form data
            if (isset($_SESSION['checkout_form_data'])) {
                unset($_SESSION['checkout_form_data']);
            }
            
            // Set success flag for popup
            $_SESSION['show_order_success'] = true;
            
            // Redirect to confirmation page
            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit();
        } else {
            // Save form data for repopulation
            $_SESSION['checkout_form_data'] = $_POST;
        }
    }
    
    // Redirect back to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Calculate order summary - MATHEMATICALLY CORRECT VERSION
function calculateOrderSummary() {
    $subtotal = 0;
    $item_count = 0;
    
    // Regular cart items
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $item_count += $item['quantity'];
    }
    
    // Catering package - FIXED: Make sure price_per_person exists
    $catering_total = 0;
    if (!empty($_SESSION['catering_order'])) {
        $catering = $_SESSION['catering_order'];
        if (isset($catering['price_per_person'])) {
            $catering_total = $catering['price_per_person'] * $catering['persons'];
            $subtotal += $catering_total;
        } else {
            // Default catering price if not set
                        $catering_total = 9.00 * $catering['persons']; // Default $9 per person
            $subtotal += $catering_total;
        }
    }
    
        $tax_rate = 0.0825; // 8.25% sales tax
    
    // Delivery fees calculation - FIXED LOGIC
    $delivery_fee = 0;
    $catering_delivery_fee = 0;
    
    // Regular delivery fee
    if ($item_count > 0) {
                $delivery_fee = 9.00;
    }
    
    // Catering delivery fee (adds to regular delivery, doesn't replace it)
    if (!empty($_SESSION['catering_order'])) {
                $catering_delivery_fee = 50.00;
    }
    
    // Calculate taxable amount (subtotal + delivery fees)
    $taxable_amount = $subtotal + $delivery_fee + $catering_delivery_fee;
    $tax = $taxable_amount * $tax_rate;
    
    $total = $subtotal + $delivery_fee + $catering_delivery_fee + $tax;
    
    return [
        'subtotal' => round($subtotal, 2),
        'tax' => round($tax, 2),
        'delivery_fee' => round($delivery_fee, 2),
        'catering_delivery_fee' => round($catering_delivery_fee, 2),
        'total' => round($total, 2),
        'item_count' => $item_count,
        'catering_total' => $catering_total,
        'taxable_amount' => round($taxable_amount, 2)
    ];
}

$order_summary = calculateOrderSummary();

// Get cart counts
$cart_count = count($_SESSION['cart']);
$has_catering = !empty($_SESSION['catering_order']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Hungry Food</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B35;
            --primary-light: #FF8B5C;
            --primary-dark: #E55A2B;
            --secondary: #4ECDC4;
            --dark: #292929;
            --light: #F8F9FA;
            --gray: #6C757D;
            --light-gray: #E9ECEF;
            --shadow: 0 10px 30px rgba(0,0,0,0.08);
            --shadow-hover: 0 15px 40px rgba(0,0,0,0.12);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --success: #28a745;
            --info: #17a2b8;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        body {
            background-color: #ffffff;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            box-shadow: 0 5px 20px rgba(255, 107, 53, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Cart Badge */
        .cart-badge {
            position: fixed;
            top: 100px;
            right: 30px;
            z-index: 1000;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 50px;
            padding: 12px 24px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: var(--transition);
        }

        .cart-badge:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            color: white;
            text-decoration: none;
        }

        .cart-badge .badge {
            background: white;
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.15) 0%, rgba(78, 205, 196, 0.1) 100%);
            padding: 100px 0 60px;
            margin-top: 76px;
        }

        /* Checkout Steps */
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--light-gray);
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
            max-width: 200px;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--light);
            border: 2px solid var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 10px;
            transition: var(--transition);
        }

        .step.active .step-icon {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-color: var(--primary);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }

        .step.completed .step-icon {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }

        /* Cart Items Container */
        .cart-items-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-gray);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Regular Cart Items */
        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            margin-right: 1.5rem;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .cart-item-price {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }

        /* Catering Package */
        .catering-package {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.05), rgba(78, 205, 196, 0.05));
            border: 2px solid var(--secondary);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .catering-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .catering-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .catering-title i {
            color: var(--secondary);
        }

        .catering-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-group {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--light-gray);
        }

        .detail-label {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }

        .catering-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* Quantity Selector */
        .quantity-selector {
            display: flex;
            align-items: center;
            border: 1px solid var(--light-gray);
            border-radius: 30px;
            padding: 2px;
            background: white;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background: var(--primary);
            color: white;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 500;
            font-size: 1rem;
        }

        /* Remove Buttons */
        .remove-btn {
            color: var(--gray);
            background: none;
            border: none;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
        }

        .remove-btn:hover {
            background: #fee;
            color: #dc3545;
        }

        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            position: sticky;
            top: 100px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .summary-item.catering {
            background: rgba(78, 205, 196, 0.05);
            padding: 1rem;
            border-radius: 8px;
            margin: 0.5rem 0;
        }

        .summary-item.total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            border-top: 2px solid var(--primary);
            border-bottom: none;
            margin-top: 0.5rem;
            padding-top: 1rem;
        }

        /* Form Styling */
        .form-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 1px solid var(--light-gray);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            background: white;
        }

        .payment-method:hover {
            border-color: var(--primary-light);
            transform: translateY(-2px);
        }

        .payment-method.selected {
            border-color: var(--primary);
            background: rgba(255, 107, 53, 0.05);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.1);
        }

        .payment-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .payment-method.cash .payment-icon {
            color: var(--success);
        }

        .payment-method.card .payment-icon {
            color: #0d6efd;
        }

        .payment-method h6 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .payment-method small {
            color: var(--gray);
            font-size: 0.875rem;
        }

        /* Security Badges */
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .security-badge {
            background: var(--light);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .security-badge i {
            color: var(--success);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            padding: 2rem 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }

        /* Empty State */
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-cart i {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 1rem;
        }

        /* Success/Error Messages */
        .alert-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                margin-top: 56px;
                padding: 80px 0 40px;
            }
            
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 1rem 0;
            }
            
            .cart-item-image {
                width: 100%;
                height: 200px;
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .cart-item-actions {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 1rem;
            }
            
            .catering-details {
                grid-template-columns: 1fr;
            }
            
            .checkout-steps {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .step {
                flex: 0 0 calc(50% - 0.5rem);
                margin-bottom: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
            
            .order-summary {
                position: static;
                margin-bottom: 2rem;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
            
            .back-to-top, .cart-badge {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .cart-badge {
                top: 90px;
                right: 15px;
            }
            
            .back-to-top {
                bottom: 15px;
                right: 15px;
                width: 50px;
                height: 50px;
            }
        }

        </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </a>

    <!-- Cart Badge -->
    <?php if ($cart_count > 0 || $has_catering): ?>
    <a href="order.php" class="cart-badge">
        <i class="fas fa-shopping-cart"></i>
        <span>Your Order</span>
        <span class="badge rounded-pill"><?php echo $cart_count + ($has_catering ? 1 : 0); ?></span>
    </a>
    <?php endif; ?>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert-notification">
        <div class="alert alert-success alert-dismissible fade show shadow-lg" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                <div>
                    <h6 class="mb-1">Success!</h6>
                    <p class="mb-0"><?php echo $_SESSION['success_message']; ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php 
    unset($_SESSION['success_message']);
    endif; 
    ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert-notification">
        <div class="alert alert-danger alert-dismissible fade show shadow-lg" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-2x me-3 text-danger"></i>
                <div>
                    <h6 class="mb-1">Error!</h6>
                    <p class="mb-0"><?php echo $_SESSION['error_message']; ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php 
    unset($_SESSION['error_message']);
    endif; 
    ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-5 fw-bold mb-3">Checkout</h1>
                    <p class="lead mb-4">Review your order and complete your purchase</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Steps -->
    <section class="py-4">
        <div class="container">
            <div class="checkout-steps">
                <div class="step active">
                    <div class="step-icon">1</div>
                    <div class="step-title">Cart Review</div>
                </div>
                <div class="step">
                    <div class="step-icon">2</div>
                    <div class="step-title">Information</div>
                </div>
                <div class="step">
                    <div class="step-icon">3</div>
                    <div class="step-title">Payment</div>
                </div>
                <div class="step">
                    <div class="step-icon">4</div>
                    <div class="step-title">Confirmation</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-4">
        <div class="container">
            <div class="row">
                <!-- Left Column: Cart Items & Forms -->
                <div class="col-lg-8">
                    <?php if ($cart_count > 0 || $has_catering): ?>
                    
                    <!-- Cart Items Container -->
                    <div class="cart-items-container animate-fade-in">
                        <div class="section-header">
                            <h3 class="section-title">Your Order</h3>
                            <form method="POST" action="order.php" onsubmit="return confirm('Are you sure you want to clear your entire order?');">
                                                            <input type="hidden" name="clear_cart" value="1">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-2"></i>Clear All
                                </button>
                            </form>
                        </div>
                        
                        <!-- Regular Menu Items -->
                        <?php if ($cart_count > 0): ?>
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-utensils me-2 text-primary"></i>Menu Items</h5>
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
                                <img src="<?php echo $item['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="cart-item-image">
                                
                                <div class="cart-item-details">
                                    <h4 class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <div class="text-muted mb-2"><?php echo ucfirst($item['category']); ?></div>
                                    <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?> each</div>
                                    
                                    <div class="cart-item-actions d-flex align-items-center justify-content-between mt-3">
                                        <form method="POST" action="order.php" class="quantity-form">
                                            <input type="hidden" name="update_quantity" value="1">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                                            
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="quantity-selector">
                                                    <button type="button" class="quantity-btn minus" 
                                                            onclick="updateQuantity(this, -1)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" class="quantity-input" 
                                                           name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                           min="1" max="10" readonly>
                                                    <button type="button" class="quantity-btn plus" 
                                                            onclick="updateQuantity(this, 1)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                
                                                <span class="fw-bold text-primary">
                                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                                </span>
                                            </div>
                                        </form>
                                        
                                        <form method="POST" action="order.php" 
                                              onsubmit="return confirm('Remove this item from cart?');">
                                            <input type="hidden" name="remove_item" value="1">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                                            <button type="submit" class="remove-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Catering Package -->
                        <?php if ($has_catering): 
                            $catering = $_SESSION['catering_order'];
                            // Ensure price_per_person exists
                            if (!isset($catering['price_per_person'])) {
                                                $catering['price_per_person'] = 9.00; // Default $9 per person
                            }
                            $catering_total = $catering['price_per_person'] * $catering['persons'];
                        ?>
                        <div class="catering-package">
                            <div class="catering-header">
                                <h4 class="catering-title">
                                    <i class="fas fa-glass-cheers"></i>
                                    <?php echo htmlspecialchars($catering['name'] ?? 'Premium'); ?> Catering Package
                                </h4>
                                <div class="catering-price">$<?php echo number_format($catering_total, 2); ?></div>
                            </div>
                            
                            <div class="catering-details">
                                <div class="detail-group">
                                    <div class="detail-label">Price per Person</div>
                                    <div class="detail-value">$<?php echo number_format($catering['price_per_person'], 2); ?></div>
                                </div>
                                
                                <div class="detail-group">
                                    <form method="POST" action="order.php" id="cateringUpdateForm">
                                        <input type="hidden" name="update_catering" value="1">
                                        <input type="hidden" name="event_date" value="<?php echo $catering['event_date']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                                        <div class="detail-label">Number of Persons</div>
                                        <input type="number" name="persons" 
                                               value="<?php echo $catering['persons']; ?>" 
                                               min="10" max="500" class="form-control"
                                               onchange="document.getElementById('cateringUpdateForm').submit()">
                                    </form>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="detail-label">Event Type</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($catering['event_type'] ?? 'Corporate'); ?></div>
                                </div>
                                
                                <div class="detail-group">
                                    <form method="POST" action="order.php" id="cateringDateForm">
                                        <input type="hidden" name="update_catering" value="1">
                                        <input type="hidden" name="persons" value="<?php echo $catering['persons']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                                        <div class="detail-label">Event Date</div>
                                        <input type="date" name="event_date" 
                                               value="<?php echo $catering['event_date']; ?>" 
                                               min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>"
                                               class="form-control"
                                               onchange="document.getElementById('cateringDateForm').submit()">
                                    </form>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Includes professional setup and service
                                </div>
                                <form method="POST" action="order.php" 
                                      onsubmit="return confirm('Remove catering package from order?');">
                                    <input type="hidden" name="remove_catering" value="1">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-times me-2"></i>Remove Catering
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Customer Information Form -->
                    <div class="form-section animate-fade-in">
                        <h3 class="mb-4">Customer Information</h3>
                        
                        <form method="POST" action="order.php" id="checkout-form">
                            <input type="hidden" name="place_order" value="1">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                            
                            <?php
                            // Load saved form data from session if validation failed
                            $saved_data = isset($_SESSION['checkout_form_data']) ? $_SESSION['checkout_form_data'] : [];
                            ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" required
                                           placeholder="Enter your first name"
                                           value="<?php echo isset($saved_data['first_name']) ? htmlspecialchars($saved_data['first_name']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" required
                                           placeholder="Enter your last name"
                                           value="<?php echo isset($saved_data['last_name']) ? htmlspecialchars($saved_data['last_name']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="email" required
                                           placeholder="your@email.com"
                                           value="<?php echo isset($saved_data['email']) ? htmlspecialchars($saved_data['email']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" name="phone" required
                                           pattern="03[0-9]{9}" 
                                           placeholder="03001234567"
                                           title="Please enter a valid Pakistani phone number (03XXXXXXXXX)"
                                           value="<?php echo isset($saved_data['phone']) ? htmlspecialchars($saved_data['phone']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Delivery Address *</label>
                                <input type="text" class="form-control" name="address" 
                                       placeholder="House #, Street, Area" required
                                       value="<?php echo isset($saved_data['address']) ? htmlspecialchars($saved_data['address']) : ''; ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City *</label>
                                    <input type="text" class="form-control" name="city" required
                                           value="<?php echo isset($saved_data['city']) ? htmlspecialchars($saved_data['city']) : 'Lahore'; ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state"
                                           value="<?php echo isset($saved_data['state']) ? htmlspecialchars($saved_data['state']) : 'Punjab'; ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ZIP Code *</label>
                                    <input type="text" class="form-control" name="zipcode" required
                                           placeholder="54000"
                                           value="<?php echo isset($saved_data['zipcode']) ? htmlspecialchars($saved_data['zipcode']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Delivery Instructions (Optional)</label>
                                <textarea class="form-control" name="instructions" rows="3" 
                                          placeholder="Gate code, building instructions, landmarks, etc."><?php echo isset($saved_data['instructions']) ? htmlspecialchars($saved_data['instructions']) : ''; ?></textarea>
                            </div>
                            
                            <!-- Order Type -->
                            <div class="mb-4">
                                <label class="form-label mb-3">Order Type</label>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="order_type" 
                                                   id="delivery" value="delivery" 
                                                   <?php echo (!isset($saved_data['order_type']) || $saved_data['order_type'] == 'delivery') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="delivery">
                                                <i class="fas fa-motorcycle me-2"></i>Delivery
                                                <small class="d-block text-muted">Estimated 30-45 minutes</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="order_type" 
                                                   id="pickup" value="pickup"
                                                   <?php echo (isset($saved_data['order_type']) && $saved_data['order_type'] == 'pickup') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="pickup">
                                                <i class="fas fa-store me-2"></i>Pickup
                                                <small class="d-block text-muted">Ready in 20-30 minutes</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Method -->
                            <div class="mb-4">
                                <label class="form-label mb-3">Payment Method *</label>
                                
                                <div class="payment-methods">
                                    <div class="payment-method cash <?php echo (!isset($saved_data['payment_method']) || $saved_data['payment_method'] == 'cash') ? 'selected' : ''; ?>" 
                                         onclick="selectPayment('cash')">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <h6>Cash on Delivery</h6>
                                        <small>Pay with cash when your order arrives</small>
                                        <input type="radio" name="payment_method" value="cash" 
                                               <?php echo (!isset($saved_data['payment_method']) || $saved_data['payment_method'] == 'cash') ? 'checked' : ''; ?> hidden>
                                    </div>
                                    
                                    <div class="payment-method card <?php echo (isset($saved_data['payment_method']) && $saved_data['payment_method'] == 'card') ? 'selected' : ''; ?>" 
                                         onclick="selectPayment('card')">
                                        <div class="payment-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <h6>Debit/Card Payment</h6>
                                        <small>Pay securely with your debit/credit card</small>
                                        <input type="radio" name="payment_method" value="card" 
                                               <?php echo (isset($saved_data['payment_method']) && $saved_data['payment_method'] == 'card') ? 'checked' : ''; ?> hidden>
                                    </div>
                                </div>
                                
                                <!-- Security Badges -->
                                <div class="security-badges">
                                    <span class="security-badge">
                                        <i class="fas fa-shield-alt"></i> SSL Secure
                                    </span>
                                    <span class="security-badge">
                                        <i class="fas fa-lock"></i> Encrypted
                                    </span>
                                    <span class="security-badge">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Terms and Conditions -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-primary">Terms & Conditions</a> and 
                                        <a href="#" class="text-primary">Privacy Policy</a> of Hungry Food
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <?php else: ?>
                    
                    <!-- Empty Cart -->
                    <div class="empty-cart animate-fade-in">
                        <i class="fas fa-shopping-cart fa-4x mb-3"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted mb-4">Add some delicious items from our menu to get started!</p>
                        <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                            <a href="menu.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-utensils me-2"></i>Browse Menu
                            </a>
                            <a href="catering.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-glass-cheers me-2"></i>Order Catering
                            </a>
                        </div>
                    </div>
                    
                    <?php endif; ?>
                </div>
                
                <!-- Right Column: Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary animate-fade-in">
                        <h3 class="mb-4">Order Summary</h3>
                        
                        <!-- Regular Items -->
                        <?php if ($cart_count > 0): ?>
                        <div class="summary-item">
                            <span>Menu Items (<?php echo $order_summary['item_count']; ?>)</span>
                            <span>$<?php echo number_format($order_summary['subtotal'] - $order_summary['catering_total'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Catering Items -->
                        <?php if ($has_catering): ?>
                        <div class="summary-item catering">
                            <span>Catering Package (<?php echo $_SESSION['catering_order']['persons']; ?> persons)</span>
                            <span>$<?php echo number_format($order_summary['catering_total'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($order_summary['subtotal'], 2); ?></span>
                        </div>
                        
                        <?php if ($order_summary['delivery_fee'] > 0): ?>
                        <div class="summary-item">
                            <span>Regular Delivery Fee</span>
                            <span>$<?php echo number_format($order_summary['delivery_fee'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($order_summary['catering_delivery_fee'] > 0): ?>
                        <div class="summary-item">
                            <span>Catering Delivery & Setup</span>
                            <span>$<?php echo number_format($order_summary['catering_delivery_fee'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-item">
                            <span>Taxable Amount</span>
                            <span>$<?php echo number_format($order_summary['subtotal'] + $order_summary['delivery_fee'] + $order_summary['catering_delivery_fee'], 2); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Sales Tax (8.25%)</span>
                            <span>$<?php echo number_format($order_summary['tax'], 2); ?></span>
                        </div>
                        
                        <div class="summary-item total">
                            <span>Total Amount</span>
                            <span>$<?php echo number_format($order_summary['total'], 2); ?></span>
                        </div>
                        
                        <!-- Payment Information -->
                        <div class="mt-3 p-3 bg-light rounded-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                <small class="fw-bold">Payment Information</small>
                            </div>
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-check text-success me-1"></i>
                                Secure payment processing
                            </small>
                            <small class="text-muted d-block">
                                <i class="fas fa-check text-success me-1"></i>
                                No hidden fees
                            </small>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-4 pt-4 border-top">
                            <div class="d-grid gap-2">
                                <?php if ($cart_count > 0 || $has_catering): ?>
                                <button type="submit" form="checkout-form" class="btn btn-primary btn-lg">
                                    <i class="fas fa-lock me-2"></i>Place Secure Order
                                </button>
                                <div class="d-flex gap-2">
                                    <a href="menu.php" class="btn btn-outline-primary flex-fill">
                                        <i class="fas fa-plus me-2"></i>Add Menu Items
                                    </a>
                                    <a href="catering.php" class="btn btn-outline-secondary flex-fill">
                                        <i class="fas fa-glass-cheers me-2"></i>Add Catering
                                    </a>
                                </div>
                                <?php else: ?>
                                <a href="menu.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-utensils me-2"></i>Browse Menu
                                </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Estimated Time -->
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php if ($has_catering): ?>
                                    Catering orders require 3 days advance notice
                                    <?php else: ?>
                                    <?php 
                                        $order_type = isset($saved_data['order_type']) ? $saved_data['order_type'] : 'delivery';
                                        if ($order_type === 'pickup') {
                                            echo 'Estimated pickup: 20-30 minutes';
                                        } else {
                                            echo 'Estimated delivery: 30-45 minutes';
                                        }
                                    ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Support Info -->
                    <div class="bg-light p-4 rounded-3 mt-4 animate-fade-in">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-headset fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="mb-1">Need Help?</h5>
                                <p class="mb-0 text-muted">Our team is here to assist you</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-phone fa-lg text-primary me-3"></i>
                            <div>
                                <p class="mb-0">0317-0544863</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fab fa-whatsapp fa-lg text-success me-3"></i>
                            <div>
                                <p class="mb-0">+92 317 0544863</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope fa-lg text-primary me-3"></i>
                            <div>
                                <p class="mb-0">orders@hungryfood.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Action Buttons (Mobile) -->
    <?php if ($cart_count > 0 || $has_catering): ?>
    <section class="py-3 bg-light d-lg-none">
        <div class="container">
            <div class="action-buttons">
                <a href="menu.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
                <button type="submit" form="checkout-form" class="btn btn-primary">
                    <i class="fas fa-lock me-2"></i>Place Order
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clear any saved form data if cart is empty
            if (<?php echo $cart_count + ($has_catering ? 1 : 0); ?> === 0) {
                localStorage.removeItem('checkoutFormData');
            }

            // Back to Top Button
            const backToTop = document.getElementById('backToTop');
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            });
            
            backToTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert-notification');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
            
            // Initialize animations
            const animatedElements = document.querySelectorAll('.animate-fade-in');
            animatedElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Load saved form data from localStorage
            loadFormData();
            
            // Add event listeners for form auto-save
            const formElements = document.querySelectorAll('#checkout-form input, #checkout-form textarea, #checkout-form select');
            formElements.forEach(element => {
                element.addEventListener('change', saveFormData);
                element.addEventListener('input', saveFormData);
            });
            
            // Set minimum date for catering event date
            const eventDateInputs = document.querySelectorAll('input[name="event_date"]');
            const today = new Date();
            const minDate = new Date(today);
            minDate.setDate(today.getDate() + 3);
            
            eventDateInputs.forEach(input => {
                if (input) {
                    input.min = minDate.toISOString().split('T')[0];
                    if (!input.value) {
                        input.value = minDate.toISOString().split('T')[0];
                    }
                }
            });
            
            // Phone number formatting
            const phoneInput = document.querySelector('input[name="phone"]');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 0) {
                        if (value.startsWith('0')) {
                            value = value.slice(0, 11);
                        } else if (value.startsWith('92')) {
                            value = '0' + value.slice(2, 13);
                        } else {
                            value = value.slice(0, 10);
                        }
                    }
                    e.target.value = value;
                });
            }
        });
        
        // Quantity update functions
        function updateQuantity(button, change) {
            const form = button.closest('.quantity-form');
            const input = form.querySelector('.quantity-input');
            let value = parseInt(input.value) + change;
            
            if (value >= 1 && value <= 10) {
                input.value = value;
                
                // Show loading animation
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                // Submit form after a short delay
                setTimeout(() => {
                    form.submit();
                }, 500);
            }
        }
        
        // Payment method selection
        function selectPayment(method) {
            // Update all payment methods UI
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Update selected payment method
            const selectedElement = document.querySelector(`.payment-method.${method}`);
            if (selectedElement) {
                selectedElement.classList.add('selected');
                
                // Update hidden radio button
                const radioInput = selectedElement.querySelector('input[type="radio"]');
                if (radioInput) {
                    radioInput.checked = true;
                }
            }
        }
        
        // Form validation
        document.getElementById('checkout-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const terms = document.getElementById('terms');
            if (!terms.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Terms & Conditions',
                    text: 'Please agree to the Terms & Conditions to continue.',
                    confirmButtonColor: '#FF6B35',
                    background: '#fff',
                    color: '#292929'
                });
                terms.focus();
                return false;
            }
            
            // Validate phone number
            const phoneInput = document.querySelector('input[name="phone"]');
            if (phoneInput) {
                const phoneValue = phoneInput.value.replace(/\D/g, '');
                if (phoneValue.length !== 11 || !phoneValue.startsWith('03')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Phone Number',
                        text: 'Please enter a valid Pakistani phone number starting with 03 (e.g., 03331234567)',
                        confirmButtonColor: '#FF6B35',
                        background: '#fff',
                        color: '#292929'
                    });
                    phoneInput.focus();
                    return false;
                }
            }
            
            // Calculate estimated delivery time
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            const currentTime = new Date();
            let deliveryTime = '';
            
            <?php if ($has_catering): ?>
                // Catering order
                const catering = <?php echo json_encode($_SESSION['catering_order']); ?>;
                const eventDate = new Date(catering.event_date);
                deliveryTime = eventDate.toLocaleDateString('en-US', { 
                    month: 'long', 
                    day: 'numeric', 
                    year: 'numeric' 
                }) + " (Event Date)";
            <?php else: ?>
                // Regular order
                if (orderType === 'pickup') {
                    currentTime.setMinutes(currentTime.getMinutes() + 30);
                    deliveryTime = currentTime.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        hour12: true 
                    }) + " (Ready for pickup)";
                } else {
                    currentTime.setMinutes(currentTime.getMinutes() + 45);
                    deliveryTime = currentTime.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        hour12: true 
                    }) + " (Estimated delivery)";
                }
            <?php endif; ?>
            
            // Show confirmation dialog with delivery time
            Swal.fire({
                title: 'Confirm Your Order',
                html: `
                    <div class="text-start">
                        <p class="mb-3">Are you ready to place your order?</p>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Estimated Delivery:</strong> ${deliveryTime}
                        </div>
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-clock me-2"></i>
                            Please note that this is an estimated time. Actual time may vary.
                        </div>
                        <p class="mb-2"><strong>Total Amount:</strong> $1($order_summary['total'], 2); ?></p>
                        <p class="mb-0"><strong>Payment Method:</strong> <span id="paymentMethodDisplay"></span></p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#FF6B35',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Place Order',
                cancelButtonText: 'Review Order',
                background: '#fff',
                color: '#292929',
                didOpen: () => {
                    // Get selected payment method
                    const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
                    const paymentText = selectedPayment ? 
                        (selectedPayment.value === 'cash' ? 'Cash on Delivery' : 'Debit/Card Payment') : 'Cash on Delivery';
                    document.getElementById('paymentMethodDisplay').textContent = paymentText;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show processing animation
                    Swal.fire({
                        title: 'Processing Your Order',
                        html: '<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p>Please wait while we process your order...</p></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        background: '#fff',
                        color: '#292929'
                    });
                    
                    // Submit form
                    setTimeout(() => {
                        document.getElementById('checkout-form').submit();
                    }, 1500);
                }
            });
        });
        
        // Auto-save form data to localStorage
        function saveFormData() {
            const form = document.getElementById('checkout-form');
            if (!form) return;
            
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (key !== 'place_order') {
                    data[key] = value;
                }
            }
            
            localStorage.setItem('checkoutFormData', JSON.stringify(data));
        }
        
        // Load saved form data from localStorage
        function loadFormData() {
            const savedData = localStorage.getItem('checkoutFormData');
            if (savedData) {
                const data = JSON.parse(savedData);
                const form = document.getElementById('checkout-form');
                
                for (const [key, value] of Object.entries(data)) {
                    const input = form?.querySelector(`[name="${key}"]`);
                    if (input) {
                        if (input.type === 'radio') {
                            if (input.value === value) {
                                input.checked = true;
                                // Update UI for payment methods
                                if (key === 'payment_method') {
                                    selectPayment(value);
                                }
                            }
                        } else if (input.type === 'checkbox') {
                            input.checked = value === 'on' || value === true;
                        } else if (input.type !== 'hidden') {
                            // Don't overwrite values that are already set from PHP session
                            if (!input.value || input.value === '') {
                                input.value = value;
                            }
                        }
                    }
                }
            }
        }
        
        // Catering form validation
        document.querySelectorAll('form').forEach(form => {
            if (form.querySelector('input[name="persons"]')) {
                form.addEventListener('submit', function(e) {
                    const personsInput = this.querySelector('input[name="persons"]');
                    const eventDateInput = this.querySelector('input[name="event_date"]');
                    
                    if (personsInput && (personsInput.value < 10 || personsInput.value > 500)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Number of Persons',
                            text: 'Number of persons must be between 10 and 500 for catering orders.',
                            confirmButtonColor: '#FF6B35',
                            background: '#fff',
                            color: '#292929'
                        });
                        personsInput.focus();
                        return;
                    }
                    
                    if (eventDateInput) {
                        const selectedDate = new Date(eventDateInput.value);
                        const minDate = new Date();
                        minDate.setDate(minDate.getDate() + 3);
                        
                        if (selectedDate < minDate) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Date',
                                text: 'Catering orders require at least 3 days advance notice. Please select a later date.',
                                confirmButtonColor: '#FF6B35',
                                background: '#fff',
                                color: '#292929'
                            });
                            eventDateInput.focus();
                            return;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
