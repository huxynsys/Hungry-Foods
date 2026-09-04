<?php
// backend/contact_process.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/db.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

global $conn;

$response = [
    'success' => false,
    'message' => '',
    'contact_id' => null
];

try {
    // Log request
    error_log("Contact form submission received at " . date('Y-m-d H:i:s'));

    // Check if it's POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Check if contact_submit is set
    if (!isset($_POST['contact_submit']) || $_POST['contact_submit'] !== '1') {
        throw new Exception('Invalid form submission');
    }

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid security token. Please refresh the page and try again.');
    }

    // Validate required fields
    $required_fields = ['name', 'phone', 'subject', 'message'];
    $missing = [];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Please fill in all required fields: ' . implode(', ', $missing));
    }

    // Sanitize inputs
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validate name
    if (strlen($name) < 2 || strlen($name) > 100) {
        throw new Exception('Name must be between 2 and 100 characters');
    }

    // Validate phone (Pakistan format - 11 digits)
    $phone_clean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone_clean) !== 11) {
        throw new Exception('Phone number must be 11 digits (e.g., 03170544863)');
    }

    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }

    // Validate subject
    $allowed_subjects = ['general', 'order', 'catering', 'feedback', 'complaint', 'partnership'];
    if (!in_array($subject, $allowed_subjects)) {
        throw new Exception('Invalid inquiry type selected');
    }

    // Validate message length
    if (strlen($message) < 10 || strlen($message) > 5000) {
        throw new Exception('Message must be between 10 and 5000 characters');
    }

    // Get IP and user agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Generate unique contact ID
    $contact_id = 'CONTACT_' . date('Ymd') . '_' . strtoupper(uniqid());

    // Check if contacts table exists, if not create it
    $table_check = $conn->query("SHOW TABLES LIKE 'contacts'");
    if ($table_check->rowCount() == 0) {
        // Create contacts table
        $create_table = "CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            contact_id VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100),
            subject VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            admin_notes TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_contact_id (contact_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($create_table);
        error_log("Contacts table created");
    }

    // Insert into database
    $sql = "INSERT INTO contacts (contact_id, name, phone, email, subject, message, status, priority, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending', 'medium', ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        $contact_id,
        $name,
        $phone,
        $email,
        $subject,
        $message,
        $ip_address,
        $user_agent
    ]);

    if ($success) {
        $insert_id = $conn->lastInsertId();
        
        // Create contact_logs table if not exists
        $log_table_check = $conn->query("SHOW TABLES LIKE 'contact_logs'");
        if ($log_table_check->rowCount() == 0) {
            $create_log_table = "CREATE TABLE IF NOT EXISTS contact_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                contact_id INT,
                action VARCHAR(50) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_contact_id (contact_id),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $conn->exec($create_log_table);
        }
        
        // Log the contact submission
        try {
            $log_sql = "INSERT INTO contact_logs (contact_id, action, details, ip_address, user_agent, created_at) 
                        VALUES (?, 'submitted', 'Contact form submitted successfully', ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->execute([$insert_id, $ip_address, $user_agent]);
        } catch (Exception $e) {
            error_log("Error logging contact action: " . $e->getMessage());
        }
        
        $response['success'] = true;
        $response['message'] = 'Your message has been sent successfully! We will get back to you within 2 hours.';
        $response['contact_id'] = $contact_id;
        
        // Generate new CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        error_log("Contact saved successfully: " . $contact_id);
        
    } else {
        throw new Exception('Failed to save your message. Please try again.');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Contact form error: " . $e->getMessage());
}

// Clean output buffer and return JSON
ob_clean();
echo json_encode($response);
exit;
?>