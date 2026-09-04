<?php
// functions.php
// Common helper functions for Hungry Food website

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Display success message
 */
function setSuccessMessage($message) {
    $_SESSION['success'] = $message;
}

/**
 * Display error message
 */
function setErrorMessage($message) {
    $_SESSION['error'] = $message;
}

/**
 * Get database connection
 */
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Hash password with pepper
 */
function hashPassword($password) {
    return hash('sha256', $password . PEPPER);
}

/**
 * Upload file with validation
 */
function uploadFile($file, $targetDir = UPLOAD_PATH) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, ALLOWED_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $extension;
    $targetPath = $targetDir . '/' . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => $fileName];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file'];
}

/**
 * Send email
 */
function sendEmail($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = "From: " . MAIL_FROM . "\r\n" .
                   "Reply-To: " . MAIL_FROM . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
    }
    
    return mail($to, $subject, $message, $headers);
}