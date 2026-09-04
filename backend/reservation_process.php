<?php
// backend/reservation_process.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

ob_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $conn;

$response = [
    'success' => false,
    'message' => '',
    'reservation_id' => null
];

try {
    // Check if it's POST request (same as contact_process)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Verify CSRF token (same as contact form)
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid security token. Please refresh the page.');
    }

    // Validate required fields
    $required = ['name', 'phone', 'guests', 'date', 'time'];
    $missing = [];
    foreach ($required as $field) {
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
    $guests = (int)$_POST['guests'];
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $table_type = isset($_POST['table-type']) ? trim($_POST['table-type']) : 'any';
    $occasion = isset($_POST['occasion']) ? trim($_POST['occasion']) : '';
    $special_requests = isset($_POST['special-requests']) ? trim($_POST['special-requests']) : '';

    // Validate phone (Pakistan format - same as contact form)
    $phone_clean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone_clean) !== 11) {
        throw new Exception('Phone number must be 11 digits (e.g., 03170544863)');
    }

    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }

    // Validate guests
    if ($guests < 1 || $guests > 20) {
        throw new Exception('Number of guests must be between 1 and 20');
    }

    // Validate date
    $today = date('Y-m-d');
    $max_date = date('Y-m-d', strtotime('+30 days'));
    if ($date < $today || $date > $max_date) {
        throw new Exception('Please select a valid date within the next 30 days');
    }

    // Check if reservations table exists, if not create it
    $table_check = $conn->query("SHOW TABLES LIKE 'reservations'");
    if ($table_check->rowCount() == 0) {
        // Create reservations table
        $create_table = "CREATE TABLE IF NOT EXISTS reservations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reservation_id VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100),
            guests INT NOT NULL,
            reservation_date DATE NOT NULL,
            reservation_time TIME NOT NULL,
            table_type VARCHAR(50) DEFAULT 'any',
            occasion VARCHAR(50) DEFAULT '',
            special_requests TEXT,
            status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
            admin_notes TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_reservation_id (reservation_id),
            INDEX idx_status (status),
            INDEX idx_date (reservation_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($create_table);
        error_log("Reservations table created");
    }

    // Check if time slot is available (optional)
    $check_sql = "SELECT COUNT(*) as count FROM reservations 
                  WHERE reservation_date = :date 
                  AND reservation_time = :time 
                  AND status IN ('pending', 'confirmed')";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':date', $date);
    $check_stmt->bindParam(':time', $time);
    $check_stmt->execute();
    $check_result = $check_stmt->fetch();
    
    if ($check_result['count'] >= 10) { // Max 10 reservations per time slot
        throw new Exception('This time slot is fully booked. Please choose another time.');
    }

    // Generate unique reservation ID (similar to contact_id)
    $reservation_id = 'RES_' . date('Ymd') . '_' . strtoupper(uniqid());

    // Get IP and user agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Insert into database
    $sql = "INSERT INTO reservations (
        reservation_id, name, phone, email, guests, 
        reservation_date, reservation_time, table_type, 
        occasion, special_requests, status, ip_address, user_agent, created_at
    ) VALUES (
        :reservation_id, :name, :phone, :email, :guests,
        :date, :time, :table_type, :occasion, :special_requests, 
        'pending', :ip_address, :user_agent, NOW()
    )";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':reservation_id', $reservation_id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':guests', $guests);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':time', $time);
    $stmt->bindParam(':table_type', $table_type);
    $stmt->bindParam(':occasion', $occasion);
    $stmt->bindParam(':special_requests', $special_requests);
    $stmt->bindParam(':ip_address', $ip_address);
    $stmt->bindParam(':user_agent', $user_agent);

    if ($stmt->execute()) {
        $insert_id = $conn->lastInsertId();
        
        // Create reservation_logs table if not exists
        $log_table_check = $conn->query("SHOW TABLES LIKE 'reservation_logs'");
        if ($log_table_check->rowCount() == 0) {
            $create_log_table = "CREATE TABLE IF NOT EXISTS reservation_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reservation_id INT,
                action VARCHAR(50) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_reservation_id (reservation_id),
                FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $conn->exec($create_log_table);
        }
        
        // Log the reservation submission
        try {
            $log_sql = "INSERT INTO reservation_logs (reservation_id, action, details, ip_address, user_agent, created_at) 
                        VALUES (:reservation_id, 'created', 'Reservation created successfully', :ip_address, :user_agent, NOW())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bindParam(':reservation_id', $insert_id);
            $log_stmt->bindParam(':ip_address', $ip_address);
            $log_stmt->bindParam(':user_agent', $user_agent);
            $log_stmt->execute();
        } catch (Exception $e) {
            error_log("Error logging reservation action: " . $e->getMessage());
        }
        
        $response['success'] = true;
        $response['message'] = 'Your reservation has been received! We will confirm shortly.';
        $response['reservation_id'] = $reservation_id;
        
        // Generate new CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        error_log("Reservation saved successfully: " . $reservation_id);
        
    } else {
        throw new Exception('Failed to save your reservation. Please try again.');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Reservation error: " . $e->getMessage());
}

ob_clean();
echo json_encode($response);
exit;
?>