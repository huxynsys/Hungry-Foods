<?php
session_start();

// This would normally check database for new updates
// For now, we'll just return session data

header('Content-Type: application/json');

$response = [
    'success' => true,
    'new_orders' => false,
    'pending_orders' => 0,
    'cart_count' => 0
];

// Count pending orders
if (isset($_SESSION['user_orders'])) {
    foreach ($_SESSION['user_orders'] as $order) {
        if ($order['status'] === 'processing') {
            $response['pending_orders']++;
        }
    }
}

// Count cart items
$cart_count = 0;
if (isset($_SESSION['cart'])) $cart_count += count($_SESSION['cart']);
if (isset($_SESSION['catering_order']) && !empty($_SESSION['catering_order'])) $cart_count += 1;
$response['cart_count'] = $cart_count;

echo json_encode($response);