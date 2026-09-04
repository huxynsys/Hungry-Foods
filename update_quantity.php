<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = $_POST['index'];
    $change = $_POST['change'];
    
    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['quantity'] += intval($change);
        
        // Remove item if quantity becomes 0 or less
        if ($_SESSION['cart'][$index]['quantity'] <= 0) {
            array_splice($_SESSION['cart'], $index, 1);
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Item not found']);
    }
}
?>