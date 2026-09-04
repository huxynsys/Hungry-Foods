<?php
// Utility functions

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Get setting value
 */
function getSetting($key, $default = null) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT setting_value, setting_type FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        if ($result) {
            $value = $result['setting_value'];
            $type = $result['setting_type'];
            
            switch ($type) {
                case 'boolean':
                    return $value == '1';
                case 'number':
                    return is_numeric($value) ? (float)$value : $default;
                case 'json':
                    return json_decode($value, true) ?? $default;
                default:
                    return $value;
            }
        }
        
        return $default;
    } catch (Exception $e) {
        error_log("Error getting setting: " . $e->getMessage());
        return $default;
    }
}

/**
 * Log contact action
 */
function logContactAction($contact_id, $action, $details = null) {
    global $db;
    
    try {
        $stmt = $db->prepare("INSERT INTO contact_logs (contact_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $contact_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Error logging contact action: " . $e->getMessage());
    }
}

/**
 * Format phone number
 */
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) == 11) {
        return substr($phone, 0, 4) . '-' . substr($phone, 4, 7);
    }
    
    return $phone;
}

/**
 * Get contact status badge
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'in_progress' => 'info',
        'resolved' => 'success',
        'closed' => 'secondary'
    ];
    
    $color = $badges[$status] ?? 'secondary';
    return "<span class='badge bg-{$color}'>{$status}</span>";
}

/**
 * Get priority badge
 */
function getPriorityBadge($priority) {
    $badges = [
        'low' => 'success',
        'medium' => 'info',
        'high' => 'warning',
        'urgent' => 'danger'
    ];
    
    $color = $badges[$priority] ?? 'secondary';
    return "<span class='badge bg-{$color}'>{$priority}</span>";
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowed_types = null, $max_size = null) {
    if ($allowed_types === null) {
        $allowed_types = getSetting('allowed_file_types', ['jpg', 'jpeg', 'png', 'pdf']);
    }
    
    if ($max_size === null) {
        $max_size = getSetting('max_file_size', 5242880); // 5MB default
    }
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'File upload failed'];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'message' => 'File size exceeds maximum allowed'];
    }
    
    // Check file type
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        return ['valid' => false, 'message' => 'File type not allowed'];
    }
    
    return ['valid' => true, 'message' => 'File is valid'];
}

/**
 * Upload file
 */
function uploadFile($file, $destination_dir) {
    if (!is_dir($destination_dir)) {
        mkdir($destination_dir, 0755, true);
    }
    
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
    $destination = $destination_dir . '/' . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $new_filename;
    }
    
    return false;
}

/**
 * Send SMS (using a service like Twilio, etc.)
 */
function sendSMS($phone, $message) {
    // Implement SMS sending logic here
    // This is a placeholder
    error_log("SMS to {$phone}: {$message}");
    return true;
}

/**
 * Get time ago string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>