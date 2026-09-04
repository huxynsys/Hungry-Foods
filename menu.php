<?php
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include database connection
require_once __DIR__ . '/backend/db.php';
// Get database connection
$conn = getDatabaseConnection();

// Initialize currency if not set
if (!isset($_SESSION['currency'])) {
    $_SESSION['currency'] = 'USD'; // Default currency
}

// Handle currency toggle
if (isset($_GET['toggle_currency'])) {
    toggleCurrency();
    header('Location: menu.php');
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding items to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid request. Please try again.';
        header('Location: menu.php');
        exit();
    }
    
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    
    // Get item from database using PDO
    $query = "SELECT mi.*, c.name as category_name 
              FROM menu_items mi 
              LEFT JOIN categories c ON mi.category_slug = c.slug 
              WHERE mi.id = :id AND mi.is_available = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $item_id, PDO::PARAM_INT);
    $stmt->execute();
    $item_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item_data) {
        // Get current currency
        $current_currency = getCurrentCurrency();
        
        // Get appropriate price based on currency
        $price = ($current_currency === 'PKR') ? $item_data['price_pkr'] : $item_data['price_usd'];
        
        // Check if item already exists in cart
        $itemExists = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['id'] == $item_id) {
                $cartItem['quantity'] += $quantity;
                $itemExists = true;
                break;
            }
        }
        
        if (!$itemExists) {
            $item = [
                'id' => $item_id,
                'item_code' => $item_data['item_code'],
                'name' => $item_data['name'],
                'price' => $price,
                'currency' => $current_currency,
                'price_usd' => $item_data['price_usd'],
                'price_pkr' => $item_data['price_pkr'],
                'quantity' => $quantity,
                'image' => $item_data['image_url'],
                'category' => $item_data['category_slug'],
                'category_name' => $item_data['category_name'],
                'type' => 'menu'
            ];
            $_SESSION['cart'][] = $item;
        }
        
        $_SESSION['success_message'] = htmlspecialchars($item_data['name']) . ' added to cart successfully!';
    }
    
    header('Location: menu.php');
    exit();
}

// Get cart count for display
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Get categories from database using PDO
$query = "SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY display_order";
$stmt = $conn->query($query);
$categories_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build categories array
$categories = ['all' => 'All Items'];
foreach ($categories_db as $cat) {
    $categories[$cat['slug']] = $cat['name'];
}

// Get menu items from database
function getMenuItems() {
    $conn = getDatabaseConnection();
    $current_currency = getCurrentCurrency();
    
    $query = "SELECT mi.*, c.name as category_name 
              FROM menu_items mi 
              LEFT JOIN categories c ON mi.category_slug = c.slug 
              WHERE mi.is_available = 1 
              ORDER BY c.display_order, mi.name";
    
    $stmt = $conn->query($query);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $menu_items = [];
    foreach ($items as $item) {
        // Get appropriate price based on currency
        $price = ($current_currency === 'PKR') ? $item['price_pkr'] : $item['price_usd'];
        
        $menu_items[$item['id']] = [
            'id' => $item['id'],
            'item_code' => $item['item_code'],
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => $price,
            'price_usd' => $item['price_usd'],
            'price_pkr' => $item['price_pkr'],
            'image' => $item['image_url'],
            'category' => $item['category_slug'],
            'category_name' => $item['category_name'],
            'vegetarian' => (bool)$item['is_vegetarian'],
            'spicy' => (bool)$item['is_spicy'],
            'popular' => (bool)$item['is_popular'],
            'calories' => $item['calories'],
            'cooking_time' => $item['cooking_time'],
            'ingredients' => $item['ingredients'],
            'available' => (bool)$item['is_available'],
            'stock' => $item['stock_quantity']
        ];
    }
    
    return $menu_items;
}

$menu_items = getMenuItems();
$current_currency = getCurrentCurrency();
$currency_symbol = ($current_currency === 'PKR') ? 'Rs' : '$';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Hungry Food</title>
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
        }

        /* Add Currency Toggle Button Styles */
        .currency-toggle {
            position: fixed;
            top: 100px;
            left: 30px;
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
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .currency-toggle:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            color: white;
        }

        /* Adjust other fixed elements positions */
        .cart-badge {
            top: 100px;
            right: 30px;
        }

        @media (max-width: 768px) {
            .currency-toggle {
                top: 80px;
                left: 15px;
                padding: 10px 16px;
                font-size: 12px;
            }
            
            .cart-badge {
                top: 80px;
                right: 15px;
            }
        }
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
        }

        /* Add Currency Toggle Button Styles */
        .currency-toggle {
            position: fixed;
            top: 100px;
            left: 30px;
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
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .currency-toggle:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            color: white;
        }

        /* Adjust other fixed elements positions */
        .cart-badge {
            top: 100px;
            right: 30px;
        }

        @media (max-width: 768px) {
            .currency-toggle {
                top: 80px;
                left: 15px;
                padding: 10px 16px;
                font-size: 12px;
            }
            
            .cart-badge {
                top: 80px;
                right: 15px;
            }
        }

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

        /* Hero Section */
        .menu-hero {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.15) 0%, rgba(78, 205, 196, 0.1) 100%), 
                       linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)), 
                       url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 160px 0 120px;
            position: relative;
        }

        .menu-hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(transparent, #ffffff);
            z-index: 1;
        }

        .menu-hero .container {
            position: relative;
            z-index: 2;
        }

        .menu-hero h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        /* Section Titles */
        .section-title {
            position: relative;
            margin-bottom: 3rem;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        /* Category Filter */
        .category-filter {
            position: sticky;
            top: 0;
            z-index: 100;
            background: white;
            padding: 1.5rem 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-top: -50px;
        }

        .category-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            border: 2px solid transparent;
            background: var(--light);
            color: var(--dark);
            margin: 0 5px 10px;
        }

        .category-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .category-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        /* Menu Cards */
        .menu-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
            border: 1px solid var(--light-gray);
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .menu-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .menu-card:hover .menu-img {
            transform: scale(1.05);
        }

        .menu-badges {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .menu-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-veg {
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
        }

        .badge-spicy {
            background: linear-gradient(135deg, #F44336, #FF9800);
        }

        .badge-popular {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .menu-card-body {
            padding: 1.5rem;
        }

        .menu-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .menu-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .menu-description {
            font-size: 0.9rem;
            color: var(--gray);
            line-height: 1.5;
            margin-bottom: 1rem;
            height: 42px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .menu-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--light-gray);
            margin-bottom: 1rem;
        }

        .menu-detail {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .menu-detail i {
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
            color: var(--dark);
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
            width: 40px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 500;
            font-size: 1rem;
        }

        /* Add to Cart Button */
        .add-to-cart-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 30px;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 120px;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        .add-to-cart-btn:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        .cart-actions {
            display: flex;
            align-items: center;
            gap: 10px;
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

        /* Search Box */
        .search-box {
            border-radius: 50px;
            box-shadow: var(--shadow);
            border: 1px solid var(--light-gray);
            overflow: hidden;
        }

        .search-box .form-control {
            border: none;
            padding: 1rem 1.5rem;
        }

        .search-box .input-group-text {
            border: none;
            background: transparent;
            padding: 1rem 1.5rem;
        }

        /* Filter Badges */
        .filter-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 1rem 0;
        }

        .filter-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            background: var(--light);
            color: var(--dark);
            border: 1px solid var(--light-gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-badge.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Success Notification */
        .success-notification {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            animation: slideInDown 0.3s ease;
        }

        @keyframes slideInDown {
            from {
                transform: translateX(-50%) translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }

        /* Buttons */
        .btn-primary {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.6);
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
        }

        .btn-outline-primary {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(var(--primary), var(--secondary));
            border-radius: 4px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Quick View Button */
        .quick-view-btn {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--dark);
            opacity: 0;
            transition: var(--transition);
            cursor: pointer;
        }

        .menu-card:hover .quick-view-btn {
            opacity: 1;
            bottom: 20px;
        }

        /* Menu Grid Improvements */
        .menu-items-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            width: 100%;
        }

        .menu-item {
            display: block;
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 767.98px) {
            .menu-hero {
                padding: 120px 0 80px;
                background-attachment: scroll;
            }
            
            .menu-hero h1 {
                font-size: 2.8rem;
            }
            
            .category-filter {
                padding: 1rem 0;
            }
            
            .category-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .cart-badge, .back-to-top {
                padding: 10px 16px;
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
            
            .menu-img {
                height: 180px;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .quantity-selector {
                width: 100%;
                justify-content: center;
            }
            
            .add-to-cart-btn {
                width: 100%;
            }
            
            .menu-items-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .menu-items-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 993px) and (max-width: 1200px) {
            .menu-items-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1201px) {
            .menu-items-container {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            grid-column: 1 / -1;
        }

        .no-results i {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 1rem;
        }

        /* Featured Category */
        .featured-category {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.05), rgba(78, 205, 196, 0.05));
            border-radius: var(--border-radius);
            padding: 3rem 2rem;
            margin: 2rem 0;
        }
        .menu-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .currency-indicator {
            font-size: 0.8rem;
            color: var(--gray);
            margin-left: 5px;
        }
   

        /* Menu price styling for currency */
        .menu-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .currency-indicator {
            font-size: 0.8rem;
            color: var(--gray);
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Currency Toggle Button -->
    <button class="currency-toggle" onclick="toggleCurrency()">
        <i class="fas fa-money-bill-wave"></i>
        <span>Switch to <?php echo ($current_currency === 'PKR') ? 'USD ($)' : 'PKR (Rs)'; ?></span>
    </button>

    <!-- Cart Badge -->
    <?php if ($cart_count > 0): ?>
    <a href="order.php" class="cart-badge">
        <i class="fas fa-shopping-cart"></i>
        <span>View Cart</span>
        <span class="badge rounded-pill"><?php echo $cart_count; ?></span>
    </a>
    <?php endif; ?>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </a>

    <!-- Success Message -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="success-notification">
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

    <!-- Hero Section -->
    <section class="menu-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-2 fw-bold">Explore Our Menu</h1>
                    <p class="lead fs-3 mt-4">Fresh ingredients, exquisite flavors, delivered to you</p>
                    <div class="d-flex align-items-center justify-content-center gap-3 mt-4">
                        <span class="badge bg-light text-dark fs-6">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Prices in <?php echo $current_currency; ?> (<?php echo $currency_symbol; ?>)
                        </span>
                        <?php if ($cart_count > 0): ?>
                        <a href="order.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>
                            View Cart (<?php echo $cart_count; ?> items)
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter -->
    <div class="category-filter">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-center">
                <?php foreach ($categories as $key => $label): ?>
                <button class="category-btn <?php echo $key === 'all' ? 'active' : ''; ?>" 
                        data-category="<?php echo $key; ?>">
                    <?php echo $label; ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <section class="py-4">
        <div class="container">
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-0" 
                                   id="menu-search" placeholder="Search dishes, ingredients, or cuisines...">
                            <button class="btn btn-link text-muted" type="button" id="clear-search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-sort me-2"></i>Sort By
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item sort-option" href="#" data-sort="default">Default</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-sort="price-asc">Price: Low to High</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-sort="price-desc">Price: High to Low</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-sort="name">Name A-Z</a></li>
                                <li><a class="dropdown-item sort-option" href="#" data-sort="popular">Most Popular</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item filter-option" href="#" data-filter="all">Show All</a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="vegetarian">
                                    <i class="fas fa-leaf text-success me-2"></i>Vegetarian Only
                                </a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="popular">
                                    <i class="fas fa-fire text-warning me-2"></i>Popular Items
                                </a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="fast">
                                    <i class="fas fa-bolt text-primary me-2"></i>Fast Delivery (Under 15min)
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Active Filters -->
            <div class="filter-badges" id="active-filters" style="display: none;"></div>
        </div>
    </section>

    <!-- Featured Category -->
    <section class="featured-category">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h2 class="mb-3">Today's Special</h2>
                    <p class="lead text-muted">Try our chef's special selection - fresh ingredients, amazing flavors!</p>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        All prices displayed in <?php echo $current_currency; ?> (<?php echo $currency_symbol; ?>)
                    </small>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <button class="btn btn-primary" onclick="filterBySpecial()">
                        <i class="fas fa-utensils me-2"></i>View Today's Specials
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Items -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Our Delicious Menu</h2>
                    <p class="text-muted fs-5">Carefully crafted dishes for every taste</p>
                    <div class="d-flex justify-content-center align-items-center gap-3">
                        <span class="badge bg-primary">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            Currency: <?php echo $current_currency; ?>
                        </span>
                        <a href="?toggle_currency=1" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-exchange-alt me-1"></i>
                            Switch to <?php echo ($current_currency === 'PKR') ? 'USD' : 'PKR'; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Menu Grid -->
            <div class="menu-items-container" id="menu-items-container">
                <?php foreach ($menu_items as $item_id => $item): ?>
                <div class="menu-item" 
                     data-category="<?php echo $item['category']; ?>"
                     data-name="<?php echo strtolower($item['name']); ?>"
                     data-price="<?php echo $item['price']; ?>"
                     data-vegetarian="<?php echo $item['vegetarian'] ? 'true' : 'false'; ?>"
                     data-popular="<?php echo $item['popular'] ? 'true' : 'false'; ?>"
                     data-cooking-time="<?php echo $item['cooking_time']; ?>">
                    
                    <div class="menu-card animate-fade-in-up">
                        <div class="position-relative">
                            <img src="<?php echo $item['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="menu-img" loading="lazy">
                            
                            <!-- Item Badges -->
                            <div class="menu-badges">
                                <?php if ($item['vegetarian']): ?>
                                <span class="menu-badge badge-veg">
                                    <i class="fas fa-leaf"></i> Veg
                                </span>
                                <?php endif; ?>
                                <?php if ($item['popular']): ?>
                                <span class="menu-badge badge-popular">
                                    <i class="fas fa-fire"></i> Popular
                                </span>
                                <?php endif; ?>
                                <?php if ($item['cooking_time'] < 15): ?>
                                <span class="menu-badge" style="background: linear-gradient(135deg, #4ECDC4, #2a9d8f);">
                                    <i class="fas fa-bolt"></i> Fast
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <button class="quick-view-btn" onclick="showQuickView('<?php echo $item_id; ?>')">
                                <i class="fas fa-eye me-1"></i> Quick View
                            </button>
                        </div>
                        
                        <div class="menu-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="menu-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <div>
                                    <span class="menu-price"><?php echo $currency_symbol; ?><?php echo number_format($item['price'], 2); ?></span>
                                    <small class="currency-indicator"><?php echo $current_currency; ?></small>
                                </div>
                            </div>
                            
                            <p class="menu-description">
                                <?php echo htmlspecialchars($item['description']); ?>
                            </p>
                            
                            <div class="menu-details">
                                <div class="menu-detail">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $item['cooking_time']; ?> min</span>
                                </div>
                                <div class="menu-detail">
                                    <i class="fas fa-fire"></i>
                                    <span><?php echo $item['calories']; ?> cal</span>
                                </div>
                                <?php if ($item['stock'] >= 0): ?>
                                <div class="menu-detail">
                                    <i class="fas fa-box"></i>
                                    <span><?php echo $item['stock']; ?> left</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" action="menu.php" class="add-to-cart-form" data-item-id="<?php echo $item_id; ?>">
                                <input type="hidden" name="add_to_cart" value="1">
                                <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                                <input type="hidden" name="quantity" class="quantity-hidden" value="1">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                                
                                <div class="cart-actions">
                                    <div class="quantity-selector">
                                        <button type="button" class="quantity-btn minus" onclick="adjustQuantity(this, -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="text" class="quantity-input" value="1" readonly>
                                        <button type="button" class="quantity-btn plus" onclick="adjustQuantity(this, 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    
                                    <button type="submit" class="add-to-cart-btn">
                                        <i class="fas fa-cart-plus"></i>
                                        <span>Add to Cart</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- No Results Message -->
            <div class="no-results" id="no-results" style="display: none;">
                <i class="fas fa-search fa-4x mb-3"></i>
                <h3>No items found</h3>
                <p class="text-muted mb-4">Try adjusting your search or filter to find what you're looking for.</p>
                <button class="btn btn-primary" onclick="resetFilters()">
                    <i class="fas fa-redo me-2"></i>Reset All Filters
                </button>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-4">Need Help Choosing?</h2>
                    <p class="mb-4 fs-5 text-muted">Call us or message us on WhatsApp for personalized recommendations</p>
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4">
                        <a href="tel:+923170544863" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-phone me-2"></i>Call Now
                        </a>
                        <a href="https://wa.me/923170544863" target="_blank" class="btn btn-success btn-lg px-5">
                            <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
                        </a>
                        <a href="order.php" class="btn btn-outline-primary btn-lg px-5">
                            <i class="fas fa-shopping-cart me-2"></i>View Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <img src="" alt="" class="img-fluid quick-view-image" style="height: 100%; object-fit: cover;">
                        </div>
                        <div class="col-md-6 p-5">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" 
                                    data-bs-dismiss="modal" aria-label="Close"></button>
                            
                            <h4 class="quick-view-title mb-2"></h4>
                            <div class="quick-view-badges mb-3"></div>
                            <p class="quick-view-description text-muted mb-4"></p>
                            
                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <span class="quick-view-time"></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-fire text-primary me-2"></i>
                                        <span class="quick-view-calories"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h3 class="quick-view-price mb-0 text-primary"></h3>
                                    <small class="text-muted">in <?php echo $current_currency; ?></small>
                                </div>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn minus" onclick="adjustQuantityModal(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="quantity-input" id="modal-quantity" value="1" min="1" max="10" readonly>
                                    <button type="button" class="quantity-btn plus" onclick="adjustQuantityModal(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <form method="POST" action="menu.php" id="quick-view-form">
                                <input type="hidden" name="add_to_cart" value="1">
                                <input type="hidden" name="item_id" id="modal-item-id" value="">
                                <input type="hidden" name="quantity" id="modal-hidden-quantity" value="1">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                                
                                <button type="submit" class="btn btn-primary w-100 py-3">
                                    <i class="fas fa-cart-plus me-2"></i>
                                    Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Store menu items in JavaScript for quick view
        const menuItems = <?php echo json_encode($menu_items); ?>;
        const currentCurrency = '<?php echo $current_currency; ?>';
        const currencySymbol = '<?php echo $currency_symbol; ?>';
        let currentCategory = 'all';
        let currentSort = 'default';
        let currentFilter = 'all';
        let currentSearch = '';
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize
            updateActiveFilters();
            filterAndSortItems();
            
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
            
            // Category Filter
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentCategory = this.dataset.category;
                    
                    // Update active button
                    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter items
                    filterAndSortItems();
                    updateActiveFilters();
                });
            });
            
            // Search Functionality
            const searchInput = document.getElementById('menu-search');
            searchInput.addEventListener('input', function() {
                currentSearch = this.value.toLowerCase().trim();
                filterAndSortItems();
                updateActiveFilters();
            });
            
            // Clear Search
            document.getElementById('clear-search').addEventListener('click', function() {
                searchInput.value = '';
                currentSearch = '';
                filterAndSortItems();
                updateActiveFilters();
            });
            
            // Sort Functionality
            document.querySelectorAll('.sort-option').forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentSort = this.dataset.sort;
                    filterAndSortItems();
                    updateActiveFilters();
                });
            });
            
            // Filter Functionality
            document.querySelectorAll('.filter-option').forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentFilter = this.dataset.filter;
                    filterAndSortItems();
                    updateActiveFilters();
                });
            });
            
            // Add to Cart Form Submission
            document.querySelectorAll('.add-to-cart-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('.add-to-cart-btn');
                    const originalHTML = button.innerHTML;
                    
                    // Show loading state
                    button.innerHTML = '<div class="loading-spinner"></div>';
                    button.disabled = true;
                    
                    // Store reference to this form
                    const formRef = this;
                    
                    // Submit form after delay
                    setTimeout(() => {
                        formRef.submit();
                    }, 500);
                });
            });
            
            // Quick View Form Submission
            const quickViewForm = document.getElementById('quick-view-form');
            if (quickViewForm) {
                quickViewForm.addEventListener('submit', function(e) {
                    const button = this.querySelector('button[type="submit"]');
                    const originalHTML = button.innerHTML;
                    
                    // Show loading state
                    button.innerHTML = '<div class="loading-spinner"></div>';
                    button.disabled = true;
                    
                    // Submit form after delay
                    setTimeout(() => {
                        this.submit();
                    }, 500);
                });
            }
            
            // Auto-hide success alert
            const successAlert = document.querySelector('.success-notification');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    successAlert.style.transform = 'translateX(-50%) translateY(-100%)';
                    setTimeout(() => {
                        successAlert.style.display = 'none';
                    }, 300);
                }, 3000);
            }
        });
        
        function filterAndSortItems() {
            const items = document.querySelectorAll('.menu-item');
            let visibleCount = 0;
            const container = document.getElementById('menu-items-container');
            
            // Store all items in array for sorting
            const itemsArray = Array.from(items);
            
            // First, filter items
            const filteredItems = itemsArray.filter(item => {
                const category = item.dataset.category;
                const name = item.dataset.name;
                const price = parseFloat(item.dataset.price);
                const vegetarian = item.dataset.vegetarian === 'true';
                const popular = item.dataset.popular === 'true';
                const cookingTime = parseInt(item.dataset.cookingTime);
                
                // Apply filters
                let showItem = true;
                
                // Category filter
                if (currentCategory !== 'all' && category !== currentCategory) {
                    showItem = false;
                }
                
                // Search filter
                if (currentSearch && !name.includes(currentSearch)) {
                    showItem = false;
                }
                
                // Additional filters
                switch(currentFilter) {
                    case 'vegetarian':
                        if (!vegetarian) showItem = false;
                        break;
                    case 'popular':
                        if (!popular) showItem = false;
                        break;
                    case 'fast':
                        if (cookingTime > 15) showItem = false;
                        break;
                }
                
                return showItem;
            });
            
            // Sort items
            filteredItems.sort((a, b) => {
                const priceA = parseFloat(a.dataset.price);
                const priceB = parseFloat(b.dataset.price);
                const nameA = a.dataset.name;
                const nameB = b.dataset.name;
                const popularA = a.dataset.popular === 'true';
                const popularB = b.dataset.popular === 'true';
                
                switch(currentSort) {
                    case 'price-asc':
                        return priceA - priceB;
                    case 'price-desc':
                        return priceB - priceA;
                    case 'name':
                        return nameA.localeCompare(nameB);
                    case 'popular':
                        // Popular items first, then sort by name
                        if (popularA && !popularB) return -1;
                        if (!popularA && popularB) return 1;
                        return nameA.localeCompare(nameB);
                    default:
                        // Default: sort by category then name
                        const categoryA = a.dataset.category;
                        const categoryB = b.dataset.category;
                        if (categoryA !== categoryB) {
                            return categoryA.localeCompare(categoryB);
                        }
                        return nameA.localeCompare(nameB);
                }
            });
            
            // Clear container and append sorted items
            container.innerHTML = '';
            filteredItems.forEach(item => {
                container.appendChild(item);
                item.style.display = 'block';
                visibleCount++;
            });
            
            // Update currency display for filtered items
            updateCurrencyDisplay();
            
            // Show/hide no results message
            const noResults = document.getElementById('no-results');
            if (visibleCount === 0) {
                noResults.style.display = 'block';
                container.appendChild(noResults);
            } else {
                noResults.style.display = 'none';
            }
        }
        
        function updateCurrencyDisplay() {
            // Update all price displays with current currency
            document.querySelectorAll('.menu-price').forEach(priceEl => {
                const price = parseFloat(priceEl.textContent.replace(/[^0-9.-]+/g, ''));
                priceEl.innerHTML = currencySymbol + price.toFixed(2);
            });
        }
        
        function updateActiveFilters() {
            const filtersContainer = document.getElementById('active-filters');
            filtersContainer.innerHTML = '';
            
            let hasFilters = false;
            
            // Add category filter badge
            if (currentCategory !== 'all') {
                const categoryLabel = document.querySelector(`.category-btn[data-category="${currentCategory}"]`).textContent;
                filtersContainer.innerHTML += `
                    <span class="filter-badge active" onclick="removeFilter('category')">
                        ${categoryLabel} <i class="fas fa-times ms-2"></i>
                    </span>
                `;
                hasFilters = true;
            }
            
            // Add search filter badge
            if (currentSearch) {
                filtersContainer.innerHTML += `
                    <span class="filter-badge active" onclick="removeFilter('search')">
                        Search: "${currentSearch}" <i class="fas fa-times ms-2"></i>
                    </span>
                `;
                hasFilters = true;
            }
            
            // Add sort filter badge
            if (currentSort !== 'default') {
                const sortLabel = document.querySelector(`.sort-option[data-sort="${currentSort}"]`).textContent;
                filtersContainer.innerHTML += `
                    <span class="filter-badge active" onclick="removeFilter('sort')">
                        Sorted: ${sortLabel} <i class="fas fa-times ms-2"></i>
                    </span>
                `;
                hasFilters = true;
            }
            
            // Add filter badge
            if (currentFilter !== 'all') {
                const filterLabel = document.querySelector(`.filter-option[data-filter="${currentFilter}"]`).textContent.trim();
                filtersContainer.innerHTML += `
                    <span class="filter-badge active" onclick="removeFilter('type')">
                        ${filterLabel} <i class="fas fa-times ms-2"></i>
                    </span>
                `;
                hasFilters = true;
            }
            
            // Show/hide filters container
            filtersContainer.style.display = hasFilters ? 'flex' : 'none';
        }
        
        // Global functions
        function adjustQuantity(button, change) {
            const container = button.closest('.quantity-selector');
            const input = container.querySelector('.quantity-input');
            const hiddenInput = container.closest('form').querySelector('.quantity-hidden');
            let value = parseInt(input.value) + change;
            
            if (value >= 1 && value <= 10) {
                input.value = value;
                hiddenInput.value = value;
            }
        }
        
        function adjustQuantityModal(change) {
            const input = document.getElementById('modal-quantity');
            const hiddenInput = document.getElementById('modal-hidden-quantity');
            let value = parseInt(input.value) + change;
            
            if (value >= 1 && value <= 10) {
                input.value = value;
                hiddenInput.value = value;
            }
        }
        
        function showQuickView(itemId) {
            const item = menuItems[itemId];
            
            if (!item) return;
            
            const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
            const modalElement = document.getElementById('quickViewModal');
            
            // Set modal content
            modalElement.querySelector('.quick-view-image').src = item.image;
            modalElement.querySelector('.quick-view-title').textContent = item.name;
            modalElement.querySelector('.quick-view-price').textContent = currencySymbol + item.price.toFixed(2);
            modalElement.querySelector('.quick-view-description').textContent = item.description;
            modalElement.querySelector('.quick-view-time').textContent = `${item.cooking_time} minutes`;
            modalElement.querySelector('.quick-view-calories').textContent = `${item.calories} calories`;
            modalElement.querySelector('#modal-item-id').value = itemId;
            
            // Set badges
            const badgesContainer = modalElement.querySelector('.quick-view-badges');
            badgesContainer.innerHTML = '';
            
            if (item.vegetarian) {
                badgesContainer.innerHTML += `
                    <span class="menu-badge badge-veg me-2">
                        <i class="fas fa-leaf"></i> Vegetarian
                    </span>
                `;
            }
            
            if (item.popular) {
                badgesContainer.innerHTML += `
                    <span class="menu-badge badge-popular">
                        <i class="fas fa-fire"></i> Popular
                    </span>
                `;
            }
            
            if (item.cooking_time < 15) {
                badgesContainer.innerHTML += `
                    <span class="menu-badge me-2" style="background: linear-gradient(135deg, #4ECDC4, #2a9d8f);">
                        <i class="fas fa-bolt"></i> Fast Delivery
                    </span>
                `;
            }
            
            // Reset quantity
            document.getElementById('modal-quantity').value = 1;
            document.getElementById('modal-hidden-quantity').value = 1;
            
            // Show modal
            modal.show();
        }
        
        function removeFilter(type) {
            switch(type) {
                case 'category':
                    document.querySelector('.category-btn[data-category="all"]').click();
                    break;
                case 'search':
                    document.getElementById('menu-search').value = '';
                    document.getElementById('clear-search').click();
                    break;
                case 'sort':
                    document.querySelector('.sort-option[data-sort="default"]').click();
                    break;
                case 'type':
                    document.querySelector('.filter-option[data-filter="all"]').click();
                    break;
            }
        }
        
        function resetFilters() {
            currentCategory = 'all';
            currentSort = 'default';
            currentFilter = 'all';
            currentSearch = '';
            
            // Update UI
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.category === 'all') {
                    btn.classList.add('active');
                }
            });
            
            document.getElementById('menu-search').value = '';
            
            // Reset dropdowns
            const sortDropdown = document.querySelector('.sort-option[data-sort="default"]');
            const filterDropdown = document.querySelector('.filter-option[data-filter="all"]');
            
            if (sortDropdown) sortDropdown.click();
            if (filterDropdown) filterDropdown.click();
            
            filterAndSortItems();
            updateActiveFilters();
        }
        
        function filterBySpecial() {
            // Filter to show only popular items
            document.querySelector('.filter-option[data-filter="popular"]').click();
        }
        
        function toggleCurrency() {
            window.location.href = '?toggle_currency=1';
        }
    </script>
</body>
</html>
      