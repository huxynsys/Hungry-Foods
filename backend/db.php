<?php
// includes/db.php — central PDO database connection
//
// LOCAL (XAMPP):  works out of the box — no configuration needed.
// PRODUCTION:     set these environment variables (InfinityFree control
//                 panel / .htaccess SetEnv) and this file automatically
//                 switches to production mode:
//                     DB_HOST   e.g. sql123.infinityfree.com
//                     DB_NAME   e.g. if0_00000000_hungry_food
//                     DB_USER   e.g. if0_00000000
//                     DB_PASS   your database password
//                 In production mode: errors are logged, never displayed,
//                 and connection failures return a generic 500 response.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
} // Add session start for currency functions

// ---- Environment ---------------------------------------------------------
$isProduction = getenv('DB_HOST') !== false;

$host     = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_NAME') ?: 'hungry_food';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// ---- PDO Connection ------------------------------------------------------
try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Never expose DSN/credentials to end users
    error_log('[hungry-food] DB connection failed: ' . $e->getMessage());
    if ($isProduction) {
        http_response_code(500);
        die('Service temporarily unavailable. Please try again later.');
    }
    die('Connection failed: ' . $e->getMessage());
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting: verbose locally, silent (logged only) in production
error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('log_errors', '1');

// Function to escape strings
function escape($string) {
    global $conn;
    return $conn->quote($string);
}

// Function to get database connection
function getDatabaseConnection() {
    global $conn;
    return $conn;
}

// Currency helper functions
function getExchangeRate($from = 'USD', $to = 'PKR') {
    $conn = getDatabaseConnection();
    
    $query = "SELECT exchange_rate FROM currency_rates 
              WHERE from_currency = :from_currency 
              AND to_currency = :to_currency 
              AND is_active = 1 
              ORDER BY updated_at DESC LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':from_currency', $from);
    $stmt->bindParam(':to_currency', $to);
    $stmt->execute();
    
    $result = $stmt->fetch();
    return $result ? $result['exchange_rate'] : 280; // Default fallback rate
}

function convertCurrency($amount, $from = 'USD', $to = 'PKR') {
    $rate = getExchangeRate($from, $to);
    return $amount * $rate;
}

function formatPrice($amount, $currency = 'USD') {
    if ($currency === 'PKR') {
        return 'Rs ' . number_format($amount, 2);
    } else {
        return '$' . number_format($amount, 2);
    }
}

function getCurrentCurrency() {
    return isset($_SESSION['currency']) ? $_SESSION['currency'] : 'USD';
}

function toggleCurrency() {
    if (!isset($_SESSION['currency'])) {
        $_SESSION['currency'] = 'USD';
    } elseif ($_SESSION['currency'] === 'PKR') {
        $_SESSION['currency'] = 'USD';
    } else {
        $_SESSION['currency'] = 'PKR';
    }
    return $_SESSION['currency'];
}

// Helper function for executing queries
function executeQuery($sql, $params = []) {
    $conn = getDatabaseConnection();
    
    try {
        if (empty($params)) {
            return $conn->query($sql);
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Query error: " . $e->getMessage());
        return false;
    }
}

// Helper function for fetching all rows
function fetchAllRows($stmt) {
    if ($stmt instanceof PDOStatement) {
        return $stmt->fetchAll();
    }
    return [];
}

// Helper function for inserting data
function insertData($table, $data) {
    $conn = getDatabaseConnection();
    
    $keys = array_keys($data);
    $values = array_values($data);
    $placeholders = str_repeat('?,', count($values) - 1) . '?';
    
    $sql = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES ($placeholders)";
    
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($values);
    } catch(PDOException $e) {
        error_log("Insert error: " . $e->getMessage());
        return false;
    }
}

// Helper function for updating data
function updateData($table, $data, $where, $whereParams = []) {
    $conn = getDatabaseConnection();
    
    $setParts = [];
    $setValues = [];
    
    foreach ($data as $key => $value) {
        $setParts[] = "$key = ?";
        $setValues[] = $value;
    }
    
    $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE $where";
    
    try {
        $stmt = $conn->prepare($sql);
        $allParams = array_merge($setValues, $whereParams);
        return $stmt->execute($allParams);
    } catch(PDOException $e) {
        error_log("Update error: " . $e->getMessage());
        return false;
    }
}
?>