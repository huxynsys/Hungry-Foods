<?php
// Mailer functions

/**
 * Send email using PHP's mail function
 * For production, use PHPMailer or similar
 */
function sendEmail($to, $subject, $message, $from = null) {
    if ($from === null) {
        $from = getSetting('support_email', 'noreply@hungryfood.com');
    }
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . $from . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Send admin notification about new contact
 */
function sendAdminNotification($contact_id, $name, $email, $subject_type) {
    $admin_email = getSetting('admin_email', 'admin@hungryfood.com');
    $site_name = getSetting('site_name', 'Hungry Food');
    
    $subject_map = [
        'general' => 'General Inquiry',
        'order' => 'Order Status',
        'catering' => 'Catering Services',
        'feedback' => 'Feedback & Reviews',
        'complaint' => 'Customer Support',
        'partnership' => 'Business Partnership'
    ];
    
    $subject_display = $subject_map[$subject_type] ?? $subject_type;
    
    $email_subject = "New Contact Form Submission - {$site_name}";
    
    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #FF6B35; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #333; }
            .value { color: #666; }
            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
            .button { 
                display: inline-block; 
                padding: 10px 20px; 
                background: #FF6B35; 
                color: white; 
                text-decoration: none; 
                border-radius: 5px; 
                margin-top: 20px; 
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Contact Form Submission</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Contact ID:</div>
                    <div class='value'>{$contact_id}</div>
                </div>
                <div class='field'>
                    <div class='label'>Name:</div>
                    <div class='value'>{$name}</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>" . ($email ?: 'Not provided') . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Inquiry Type:</div>
                    <div class='value'>{$subject_display}</div>
                </div>
                <div class='field'>
                    <div class='label'>Time:</div>
                    <div class='value'>" . date('Y-m-d H:i:s') . "</div>
                </div>
                
                <a href='http://" . $_SERVER['HTTP_HOST'] . "/admin/view_contact.php?id={$contact_id}' class='button'>View Details</a>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " {$site_name}. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($admin_email, $email_subject, $email_body);
}

/**
 * Send auto-reply to customer
 */
function sendAutoReply($to_email, $name, $contact_id) {
    $site_name = getSetting('site_name', 'Hungry Food');
    $support_email = getSetting('support_email', 'support@hungryfood.com');
    
    $email_subject = "Thank You for Contacting {$site_name}";
    
    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #FF6B35; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
            .reference { background: #e9ecef; padding: 10px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Thank You for Contacting Us!</h2>
            </div>
            <div class='content'>
                <p>Dear {$name},</p>
                
                <p>Thank you for reaching out to {$site_name}. We have received your message and appreciate you taking the time to contact us.</p>
                
                <div class='reference'>
                    <strong>Your Reference ID:</strong> {$contact_id}<br>
                    <strong>Status:</strong> Pending<br>
                    <strong>Expected Response Time:</strong> Within 2 hours
                </div>
                
                <p>Our customer support team will review your inquiry and get back to you as soon as possible. If you have any urgent matters, please don't hesitate to contact us through:</p>
                
                <ul>
                    <li><strong>Phone:</strong> 0317-0544863</li>
                    <li><strong>WhatsApp:</strong> +92 317 0544863</li>
                </ul>
                
                <p>You can track the status of your inquiry using your reference ID on our website.</p>
                
                <p>Best regards,<br>
                <strong>{$site_name} Team</strong></p>
            </div>
            <div class='footer'>
                <p>MM Alam Road, Lahore | support@hungryfood.com | 0317-0544863</p>
                <p>&copy; " . date('Y') . " {$site_name}. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to_email, $email_subject, $email_body, $support_email);
}

/**
 * Send status update notification
 */
function sendStatusUpdateEmail($to_email, $name, $contact_id, $new_status) {
    $site_name = getSetting('site_name', 'Hungry Food');
    $support_email = getSetting('support_email', 'support@hungryfood.com');
    
    $status_map = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed'
    ];
    
    $status_display = $status_map[$new_status] ?? $new_status;
    
    $email_subject = "Your Inquiry Status Updated - {$site_name}";
    
    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #FF6B35; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .status-badge { 
                display: inline-block; 
                padding: 5px 10px; 
                border-radius: 3px; 
                font-weight: bold;
                background: " . getStatusColor($new_status) . "; 
                color: white; 
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Inquiry Status Update</h2>
            </div>
            <div class='content'>
                <p>Dear {$name},</p>
                
                <p>The status of your inquiry has been updated.</p>
                
                <p><strong>Reference ID:</strong> {$contact_id}</p>
                <p><strong>New Status:</strong> <span class='status-badge'>{$status_display}</span></p>
                
                <p>You can view the full details of your inquiry by visiting our website.</p>
                
                <p>Thank you for choosing {$site_name}!</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to_email, $email_subject, $email_body, $support_email);
}

/**
 * Get status color for email
 */
function getStatusColor($status) {
    $colors = [
        'pending' => '#ffc107',
        'in_progress' => '#17a2b8',
        'resolved' => '#28a745',
        'closed' => '#6c757d'
    ];
    
    return $colors[$status] ?? '#6c757d';
}
?>