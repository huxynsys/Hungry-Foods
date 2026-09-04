<?php
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
$reservation_success = false;
$reservation_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $reservation_error = 'Invalid security token. Please refresh the page and try again.';
    } else {
    // Collect and sanitize form data
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $guests = intval($_POST['guests'] ?? 0);
    $date = htmlspecialchars(trim($_POST['date'] ?? ''));
    $time = htmlspecialchars(trim($_POST['time'] ?? ''));
    $table_type = htmlspecialchars(trim($_POST['table-type'] ?? 'window'));
    $occasion = htmlspecialchars(trim($_POST['occasion'] ?? ''));
    $special_requests = htmlspecialchars(trim($_POST['special-requests'] ?? ''));
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^[0-9]{4}[0-9]{7}$/', str_replace('-', '', $phone))) {
        $errors[] = "Phone number must be 11 digits (e.g., 03170544863)";
    }
    
    if (empty($guests) || $guests < 1) {
        $errors[] = "Number of guests is required";
    }
    
    if (empty($date)) {
        $errors[] = "Date is required";
    }
    
    if (empty($time)) {
        $errors[] = "Time is required";
    }
    
    // If no errors, process reservation
    if (empty($errors)) {
        // Generate reservation ID
        $reservation_id = 'HF' . date('Ymd') . rand(1000, 9999);
        
        // Format phone number for display
        $formatted_phone = $phone;
        if (strlen(str_replace('-', '', $phone)) === 11) {
            $clean_phone = str_replace('-', '', $phone);
            $formatted_phone = substr($clean_phone, 0, 4) . '-' . substr($clean_phone, 4);
        }
        
        // Store reservation in session
        $reservation_data = [
            'id' => $reservation_id,
            'name' => $name,
            'phone' => $formatted_phone,
            'email' => $email,
            'guests' => $guests,
            'date' => $date,
            'time' => $time,
            'table_type' => $table_type,
            'occasion' => $occasion,
            'special_requests' => $special_requests,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Initialize reservations array if not exists
        if (!isset($_SESSION['reservations'])) {
            $_SESSION['reservations'] = [];
        }
        
        // Add to reservations
        $_SESSION['reservations'][] = $reservation_data;
        
        // Also store latest reservation for confirmation display
        $_SESSION['latest_reservation'] = $reservation_data;
        
        $reservation_success = true;
        
        // Send email notification (if email is configured)
        if (!empty($email)) {
            $to = $email;
            $subject = "Reservation Confirmation - Hungry Food";
            $message = "
            <html>
            <head>
                <title>Reservation Confirmation</title>
            </head>
            <body>
                <h2>Reservation Confirmed!</h2>
                <p>Thank you for your reservation at Hungry Food Restaurant.</p>
                <h3>Reservation Details:</h3>
                <ul>
                    <li><strong>Reservation ID:</strong> $reservation_id</li>
                    <li><strong>Name:</strong> $name</li>
                    <li><strong>Phone:</strong> $formatted_phone</li>
                    <li><strong>Date:</strong> $date</li>
                    <li><strong>Time:</strong> $time</li>
                    <li><strong>Guests:</strong> $guests</li>
                    <li><strong>Table Type:</strong> $table_type</li>
                </ul>
                <p>Please arrive 10 minutes before your reservation time.</p>
                <p>If you need to make changes, please call us at 0317-0544863</p>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: Hungry Food <reservations@hungryfood.com>' . "\r\n";
            
                        // mail($to, $subject, $message, $headers);
            }
        } else {
            $reservation_error = implode("<br>", $errors);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Reservation | Hungry Food</title>
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

        /* Error Message */
        .error-message {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9), rgba(200, 35, 51, 0.9));
            color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: var(--shadow);
        }

        /* Hero Section */
        .reservation-hero {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.15) 0%, rgba(78, 205, 196, 0.1) 100%), 
                       linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), 
                       url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 160px 0 120px;
            position: relative;
            overflow: hidden;
        }

        .reservation-hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(transparent, #ffffff);
            z-index: 1;
        }

        .reservation-hero .container {
            position: relative;
            z-index: 2;
        }

        .reservation-hero h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
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

        /* Reservation Form */
        .reservation-form-section {
            position: relative;
            overflow: hidden;
            margin-top: -50px;
        }

        .reservation-form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.03) 0%, rgba(78, 205, 196, 0.03) 100%);
        }

        .reservation-form-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .reservation-form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .form-control, .form-select {
            padding: 1rem 1.25rem;
            border: 2px solid var(--light-gray);
            border-radius: 10px;
            font-size: 1rem;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
            outline: none;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.75rem;
            color: var(--dark);
            font-size: 1rem;
        }

        /* Table Types */
        .table-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            height: 100%;
            cursor: pointer;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .table-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .table-card.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.05), rgba(78, 205, 196, 0.05));
        }

        .table-card.recommended::after {
            content: 'Recommended';
            position: absolute;
            top: 15px;
            right: -30px;
            background: var(--primary);
            color: white;
            padding: 5px 40px;
            font-size: 0.75rem;
            font-weight: 600;
            transform: rotate(45deg);
            z-index: 2;
        }

        .table-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(78, 205, 196, 0.1));
            color: var(--primary);
            font-size: 2rem;
            transition: var(--transition);
        }

        .table-card:hover .table-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(78, 205, 196, 0.2));
        }

        /* Time Slots */
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin: 1.5rem 0;
        }

        .time-slot {
            padding: 1rem 0.5rem;
            border: 2px solid var(--light-gray);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            background: white;
        }

        .time-slot:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .time-slot.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(78, 205, 196, 0.1));
            color: var(--primary);
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.2);
        }

        .time-slot.unavailable {
            opacity: 0.5;
            cursor: not-allowed;
            text-decoration: line-through;
            background: var(--light-gray);
        }

        /* Features */
        .features-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .features-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.1) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(78, 205, 196, 0.1) 0%, transparent 50%);
        }

        .feature-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(78, 205, 196, 0.1));
            color: var(--primary);
            font-size: 1.75rem;
            transition: var(--transition);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(78, 205, 196, 0.2));
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
            position: relative;
            overflow: hidden;
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

        /* Success Message */
        .success-message {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.9), rgba(139, 195, 74, 0.9));
            color: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            text-align: center;
            box-shadow: var(--shadow);
            display: none;
        }

        .success-message.show {
            display: block;
            animation: fadeInUp 0.6s ease forwards;
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

        /* Responsive */
        @media (max-width: 768px) {
            .reservation-hero {
                padding: 140px 0 80px;
                background-attachment: scroll;
            }
            
            .reservation-hero h1 {
                font-size: 2.8rem;
            }
            
            .reservation-form-card {
                padding: 2rem;
            }
            
            .time-slots {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }
            
            .back-to-top, .cart-badge {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .back-to-top {
                bottom: 15px;
                right: 15px;
                width: 50px;
                height: 50px;
            }
            
            .cart-badge {
                top: 90px;
                right: 15px;
            }
        }

        @media (max-width: 576px) {
            .reservation-hero h1 {
                font-size: 2.2rem;
            }
            
            .table-card.recommended::after {
                right: -35px;
                padding: 5px 45px;
                font-size: 0.7rem;
            }
        }

        /* Date Picker */
        .date-picker {
            position: relative;
        }

        .date-picker-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            pointer-events: none;
        }

        /* Loader */
        .loader {
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Cart Badge -->
    <?php 
    $cart_count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
    }
    if (!empty($_SESSION['catering_order'])) {
        $cart_count += 1;
    }
    if ($cart_count > 0): ?>
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

    <!-- Hero Section -->
    <section class="reservation-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-2 fw-bold mb-4">Reserve Your Table</h1>
                    <p class="lead fs-3 mb-4">Experience fine dining at its best. Book your table in advance for the perfect dining experience.</p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="#reservation-form" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check me-2"></i>Book Now
                        </a>
                        <a href="tel:03170544863" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Call to Reserve
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reservation Form -->
    <section class="reservation-form-section py-5" id="reservation-form">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="reservation-form-card animate-fade-in-up">
                        <h2 class="mb-4 section-title">Make a Reservation</h2>
                        <p class="text-muted mb-5">Fill out the form below to reserve your table. We'll confirm your booking within 2 hours.</p>
                        
                        <?php if ($reservation_error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h4 class="mb-2">Please correct the following errors:</h4>
                            <p class="mb-0"><?php echo $reservation_error; ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($reservation_success && isset($_SESSION['latest_reservation'])): 
                            $reservation = $_SESSION['latest_reservation'];
                        ?>
                        <!-- Success Message -->
                        <div class="success-message show" id="successMessage">
                            <i class="fas fa-check-circle fa-4x mb-4"></i>
                            <h3 class="mb-3">Reservation Confirmed!</h3>
                            <p class="mb-3 fs-5">Thank you for your reservation. We've sent a confirmation to <span id="confirmationPhone" class="fw-bold"><?php echo $reservation['phone']; ?></span></p>
                            <div class="alert alert-light text-dark mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Reservation ID:</span>
                                    <span class="h5 mb-0 fw-bold" id="reservationId"><?php echo $reservation['id']; ?></span>
                                </div>
                            </div>
                            <p class="mb-0 text-light">We look forward to serving you! Please arrive 10 minutes before your reservation time.</p>
                            <div class="mt-4">
                                <button class="btn btn-light me-2" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Print Details
                                </button>
                                <button class="btn btn-outline-light" onclick="shareReservation()">
                                    <i class="fas fa-share-alt me-2"></i>Share
                                </button>
                                <a href="#reservation-form" class="btn btn-light" onclick="resetForm()">
                                    <i class="fas fa-plus me-2"></i>Make Another Reservation
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        
                        <form id="reservationForm" method="POST" action="#reservation-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required 
                                               placeholder="Enter your full name" value="<?php echo $_POST['name'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required 
                                               placeholder="0317-0544863" value="<?php echo $_POST['phone'] ?? ''; ?>">
                                        <small class="text-muted">Format: 0317-0544863 or 03170544863</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               placeholder="Enter your email address" value="<?php echo $_POST['email'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="guests" class="form-label">Number of Guests *</label>
                                        <select class="form-select" id="guests" name="guests" required>
                                            <option value="">Select number of guests</option>
                                            <option value="1" <?php echo ($_POST['guests'] ?? '') == '1' ? 'selected' : ''; ?>>1 Person</option>
                                            <option value="2" <?php echo ($_POST['guests'] ?? '') == '2' ? 'selected' : ''; ?>>2 People</option>
                                            <option value="3" <?php echo ($_POST['guests'] ?? '') == '3' ? 'selected' : ''; ?>>3 People</option>
                                            <option value="4" <?php echo ($_POST['guests'] ?? '') == '4' ? 'selected' : ''; ?>>4 People</option>
                                            <option value="5" <?php echo ($_POST['guests'] ?? '') == '5' ? 'selected' : ''; ?>>5 People</option>
                                            <option value="6" <?php echo ($_POST['guests'] ?? '') == '6' ? 'selected' : ''; ?>>6 People</option>
                                            <option value="7" <?php echo ($_POST['guests'] ?? '') == '7' ? 'selected' : ''; ?>>7 People</option>
                                            <option value="8" <?php echo ($_POST['guests'] ?? '') == '8' ? 'selected' : ''; ?>>8 People</option>
                                            <option value="9" <?php echo ($_POST['guests'] ?? '') == '9' ? 'selected' : ''; ?>>9 People</option>
                                            <option value="10" <?php echo ($_POST['guests'] ?? '') == '10' ? 'selected' : ''; ?>>10 People</option>
                                            <option value="10+">10+ People (Contact us)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Reservation Date *</label>
                                        <div class="date-picker">
                                            <input type="date" class="form-control" id="date" name="date" required 
                                                   min="<?php echo date('Y-m-d'); ?>" value="<?php echo $_POST['date'] ?? date('Y-m-d', strtotime('+1 day')); ?>">
                                            <i class="fas fa-calendar-alt date-picker-icon"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label mb-3">Preferred Time *</label>
                                        <div class="time-slots" id="time-slots">
                                            <?php
                                            $times = [
                                                '11:00' => '11:00 AM',
                                                '12:00' => '12:00 PM', 
                                                '13:00' => '1:00 PM',
                                                '14:00' => '2:00 PM',
                                                '18:00' => '6:00 PM',
                                                '19:00' => '7:00 PM',
                                                '20:00' => '8:00 PM',
                                                '21:00' => '9:00 PM',
                                                '22:00' => '10:00 PM'
                                            ];
                                            $selected_time = $_POST['time'] ?? '19:00';
                                            foreach ($times as $time_val => $time_label):
                                                $is_unavailable = ($time_val === '22:00');
                                            ?>
                                            <div class="time-slot <?php echo $is_unavailable ? 'unavailable' : ''; ?> <?php echo $selected_time == $time_val ? 'selected' : ''; ?>" 
                                                 data-time="<?php echo $time_val; ?>" <?php echo !$is_unavailable ? 'onclick="selectTime(this)"' : ''; ?>>
                                                <?php echo $time_label; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <input type="hidden" id="selected-time" name="time" value="<?php echo $selected_time; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Table Type Selection -->
                            <div class="mb-4">
                                <label class="form-label mb-3">Table Preference</label>
                                <div class="row g-4">
                                    <?php
                                    $tables = [
                                        'standard' => ['icon' => 'fas fa-chair', 'name' => 'Standard', 'desc' => 'Indoor Dining', 'capacity' => '2-4 Persons'],
                                        'window' => ['icon' => 'fas fa-sun', 'name' => 'Window View', 'desc' => 'City View Seating', 'capacity' => '2-6 Persons'],
                                        'outdoor' => ['icon' => 'fas fa-umbrella-beach', 'name' => 'Outdoor', 'desc' => 'Patio Seating', 'capacity' => '2-8 Persons'],
                                        'private' => ['icon' => 'fas fa-crown', 'name' => 'Private Room', 'desc' => 'VIP Section', 'capacity' => '4-10 Persons']
                                    ];
                                    $selected_table = $_POST['table-type'] ?? 'window';
                                    foreach ($tables as $table_key => $table_data):
                                        $is_recommended = ($table_key === 'window');
                                        $is_selected = ($selected_table == $table_key);
                                    ?>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="table-card <?php echo $is_recommended ? 'recommended' : ''; ?> <?php echo $is_selected ? 'selected' : ''; ?>" 
                                             data-type="<?php echo $table_key; ?>" onclick="selectTable(this)">
                                            <div class="table-icon">
                                                <i class="<?php echo $table_data['icon']; ?>"></i>
                                            </div>
                                            <h5 class="mb-2"><?php echo $table_data['name']; ?></h5>
                                            <p class="text-muted mb-2"><?php echo $table_data['desc']; ?></p>
                                            <div class="d-flex justify-content-center gap-2 mb-2">
                                                <i class="fas fa-user text-muted"></i>
                                                <small class="text-muted"><?php echo $table_data['capacity']; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" id="selected-table" name="table-type" value="<?php echo $selected_table; ?>">
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="occasion" class="form-label">Special Occasion</label>
                                        <select class="form-select" id="occasion" name="occasion">
                                            <option value="">Select occasion (Optional)</option>
                                            <option value="birthday" <?php echo ($_POST['occasion'] ?? '') == 'birthday' ? 'selected' : ''; ?>>Birthday Celebration</option>
                                            <option value="anniversary" <?php echo ($_POST['occasion'] ?? '') == 'anniversary' ? 'selected' : ''; ?>>Anniversary</option>
                                            <option value="business" <?php echo ($_POST['occasion'] ?? '') == 'business' ? 'selected' : ''; ?>>Business Dinner</option>
                                            <option value="date" <?php echo ($_POST['occasion'] ?? '') == 'date' ? 'selected' : ''; ?>>Romantic Date</option>
                                            <option value="family" <?php echo ($_POST['occasion'] ?? '') == 'family' ? 'selected' : ''; ?>>Family Gathering</option>
                                            <option value="other" <?php echo ($_POST['occasion'] ?? '') == 'other' ? 'selected' : ''; ?>>Other Special Occasion</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="special-requests" class="form-label">Special Requests</label>
                                        <textarea class="form-control" id="special-requests" name="special-requests" rows="1" 
                                                  placeholder="Any special requests or dietary requirements (Optional)"><?php echo $_POST['special-requests'] ?? ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary fw-bold">terms and conditions</a> and understand that late arrivals may affect my reservation.
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg py-3" id="submitBtn">
                                    <i class="fas fa-calendar-check me-2"></i>Confirm Reservation
                                </button>
                            </div>
                        </form>
                        
                        <!-- Success Message Template (hidden) -->
                        <div class="success-message mt-5" id="successMessageTemplate">
                            <i class="fas fa-check-circle fa-4x mb-4"></i>
                            <h3 class="mb-3">Reservation Confirmed!</h3>
                            <p class="mb-3 fs-5">Thank you for your reservation. We've sent a confirmation to <span id="confirmationPhone" class="fw-bold"></span></p>
                            <div class="alert alert-light text-dark mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Reservation ID:</span>
                                    <span class="h5 mb-0 fw-bold" id="reservationId"></span>
                                </div>
                            </div>
                            <p class="mb-0 text-light">We look forward to serving you! Please arrive 10 minutes before your reservation time.</p>
                            <div class="mt-4">
                                <button class="btn btn-light me-2" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Print Details
                                </button>
                                <button class="btn btn-outline-light" onclick="shareReservation()">
                                    <i class="fas fa-share-alt me-2"></i>Share
                                </button>
                                <a href="#reservation-form" class="btn btn-light" onclick="resetForm()">
                                    <i class="fas fa-plus me-2"></i>Make Another Reservation
                                </a>
                            </div>
                        </div>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Why Reserve With Us?</h2>
                    <p class="text-muted fs-5">Experience the difference at Hungry Food Restaurant</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="feature-card animate-fade-in-up" style="animation-delay: 0.1s">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4 class="mb-3">Flexible Hours</h4>
                        <p class="text-muted mb-0">Open 7 days a week from 11 AM to 10 PM. Extended hours for private events.</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="feature-card animate-fade-in-up" style="animation-delay: 0.2s">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="mb-3">Ample Seating</h4>
                        <p class="text-muted mb-0">Accommodating up to 100 guests with various seating options including private rooms.</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="feature-card animate-fade-in-up" style="animation-delay: 0.3s">
                        <div class="feature-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4 class="mb-3">Premium Service</h4>
                        <p class="text-muted mb-0">Dedicated staff ensuring exceptional service and memorable dining experiences.</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="feature-card animate-fade-in-up" style="animation-delay: 0.4s">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="mb-3">Safe Dining</h4>
                        <p class="text-muted mb-0">Highest hygiene standards with properly spaced tables and sanitized environment.</p>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="row mt-5">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="feature-card">
                        <h3 class="mb-4">Need Assistance?</h3>
                        <p class="text-muted mb-4">Contact our reservation team for any questions or special arrangements.</p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <i class="fas fa-phone text-primary fa-2x mb-3"></i>
                                    <h5>Call Us</h5>
                                    <p class="text-muted mb-0">0317-0544863</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <i class="fas fa-envelope text-primary fa-2x mb-3"></i>
                                    <h5>Email Us</h5>
                                    <p class="text-muted mb-0">reservations@hungryfood.com</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <i class="fab fa-whatsapp text-success fa-2x mb-3"></i>
                                    <h5>WhatsApp</h5>
                                    <p class="text-muted mb-0">+92 317 0544863</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="tel:03170544863" class="btn btn-primary me-2 mb-2">
                                <i class="fas fa-phone me-2"></i>Call Now
                            </a>
                            <a href="https://wa.me/923170544863" target="_blank" class="btn btn-success me-2 mb-2">
                                <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
                            </a>
                            <a href="mailto:reservations@hungryfood.com" class="btn btn-outline-primary mb-2">
                                <i class="fas fa-envelope me-2"></i>Email Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-4">Ready to Book Your Table?</h2>
                    <p class="mb-4 fs-5 text-muted">Reserve now to ensure you get your preferred time and table</p>
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4">
                        <a href="#reservation-form" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-calendar-check me-2"></i>Book Online
                        </a>
                        <a href="tel:03170544863" class="btn btn-outline-primary btn-lg px-5">
                            <i class="fas fa-phone me-2"></i>Call to Reserve
                        </a>
                        <a href="https://wa.me/923170544863" target="_blank" class="btn btn-success btn-lg px-5">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($reservation_success): ?>
                // Scroll to success message
                document.getElementById('successMessage').scrollIntoView({ behavior: 'smooth' });
            <?php endif; ?>

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

            // Phone number formatting
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 0) {
                        if (value.length <= 4) {
                            value = value;
                        } else if (value.length <= 11) {
                            value = value.slice(0, 4) + '-' + value.slice(4, 11);
                        } else {
                            value = value.slice(0, 4) + '-' + value.slice(4, 11);
                        }
                    }
                    e.target.value = value;
                });
            }

            // Guest count validation
            const guestsSelect = document.getElementById('guests');
            if (guestsSelect) {
                guestsSelect.addEventListener('change', function() {
                    if (this.value === '10+') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Large Group Reservation',
                            html: `
                                <div class="text-start">
                                    <p>For groups of 10 or more, please contact us directly:</p>
                                    <ul class="text-start">
                                        <li><strong>Phone:</strong> 0317-0544863</li>
                                        <li><strong>WhatsApp:</strong> +92 317 0544863</li>
                                        <li><strong>Email:</strong> reservations@hungryfood.com</li>
                                    </ul>
                                </div>
                            `,
                            confirmButtonColor: '#FF6B35',
                            background: '#fff',
                            color: '#292929'
                        });
                    }
                });
            }
            
            // Initialize animation observer
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            // Observe all animated elements
            document.querySelectorAll('.animate-fade-in-up').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
            
            // Add hover effects to table cards
            document.querySelectorAll('.table-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('selected')) {
                        this.style.transform = 'translateY(-10px)';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('selected')) {
                        this.style.transform = 'translateY(0)';
                    }
                });
            });
            
            // Smooth scroll for navigation
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            window.scrollTo({
                                top: target.offsetTop - 100,
                                behavior: 'smooth'
                            });
                        }
                    }
                });
            });
        });

        // Time slot selection
        function selectTime(element) {
            if (element.classList.contains('unavailable')) return;
            
            // Remove selection from all slots
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            
            // Add selection to clicked slot
            element.classList.add('selected');
            const selectedTime = element.dataset.time;
            document.getElementById('selected-time').value = selectedTime;
            
            // Show warning for popular times
            if (selectedTime === '19:00' || selectedTime === '20:00') {
                Swal.fire({
                    icon: 'info',
                    title: 'Popular Time Slot',
                    text: 'This is a popular dining time. We recommend arriving 10 minutes early.',
                    confirmButtonColor: '#FF6B35',
                    background: '#fff',
                    color: '#292929',
                    timer: 3000
                });
            }
        }

        // Table selection
        function selectTable(element) {
            // Remove selection from all table cards
            document.querySelectorAll('.table-card').forEach(card => {
                card.classList.remove('selected');
                card.style.transform = 'translateY(0)';
            });
            
            // Add selection to clicked card
            element.classList.add('selected');
            document.getElementById('selected-table').value = element.dataset.type;
        }

        // Form validation before submission
        const reservationForm = document.getElementById('reservationForm');
        if (reservationForm) {
            reservationForm.addEventListener('submit', function(e) {
                // Validate phone number
                const phoneInput = document.getElementById('phone');
                const phoneValue = phoneInput.value.replace(/\D/g, '');
                
                if (phoneValue.length !== 11) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Phone Number',
                        text: 'Phone number must be 11 digits (e.g., 03170544863)',
                        confirmButtonColor: '#FF6B35',
                        background: '#fff',
                        color: '#292929'
                    });
                    return false;
                }
                
                // Validate date
                const dateInput = document.getElementById('date');
                const selectedDate = new Date(dateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Please select a future date for your reservation.',
                        confirmButtonColor: '#FF6B35',
                        background: '#fff',
                        color: '#292929'
                    });
                    return false;
                }
                
                // Show loading state
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.innerHTML = '<div class="loader"></div> Processing...';
                    submitBtn.disabled = true;
                }
                
                return true;
            });
        }

        // Reset form function
        function resetForm() {
            // Reset form fields
            document.getElementById('reservationForm').reset();
            
            // Reset time slot to default
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            document.querySelector('.time-slot[data-time="19:00"]').classList.add('selected');
            document.getElementById('selected-time').value = '19:00';
            
            // Reset table selection to default
            document.querySelectorAll('.table-card').forEach(c => {
                c.classList.remove('selected');
                c.style.transform = 'translateY(0)';
            });
            document.querySelector('.table-card.recommended').classList.add('selected');
            document.getElementById('selected-table').value = 'window';
            
            // Hide success message
            const successMsg = document.getElementById('successMessage');
            if (successMsg) successMsg.classList.remove('show');
            
            // Scroll to form
            document.getElementById('reservation-form').scrollIntoView({ behavior: 'smooth' });
        }

        // Share reservation function
        function shareReservation() {
            const reservationId = document.getElementById('reservationId')?.textContent || 'Pending';
            
            if (navigator.share) {
                navigator.share({
                    title: 'My Table Reservation - Hungry Food',
                    text: `I've made a reservation at Hungry Food! Reservation ID: ${reservationId}`,
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const shareText = `I've made a reservation at Hungry Food!\nReservation ID: ${reservationId}\n\nBook your table at: ${window.location.href}`;
                
                // Copy to clipboard
                navigator.clipboard.writeText(shareText).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied to Clipboard!',
                        text: 'Reservation details copied to clipboard. You can now paste and share.',
                        confirmButtonColor: '#FF6B35',
                        background: '#fff',
                        color: '#292929'
                    });
                });
            }
        }
    </script>
</body>
</html>