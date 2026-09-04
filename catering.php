<?php
// Start session at the VERY TOP
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle catering package selection via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_catering_package'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid request. Please try again.';
        header('Location: catering.php');
        exit();
    }
    $package = [
        'id' => $_POST['package_id'],
        'name' => $_POST['package_name'],
        'price_per_person' => $_POST['package_price'],
        'persons' => $_POST['persons'],
        'event_date' => $_POST['event_date'],
        'event_type' => $_POST['event_type'],
        'type' => 'catering'
    ];
    
    $_SESSION['catering_order'] = $package;
    $_SESSION['success_message'] = 'Catering package added to cart successfully!';
    header('Location: order.php');
    exit();
}

// Set selected package from GET param
$selected_package_id = $_GET['package'] ?? '';
$selected_package_name = '';
$selected_package_price = 0;

if ($selected_package_id === 'essential') { $selected_package_name = 'Essential Package'; $selected_package_price = 25; }
elseif ($selected_package_id === 'premium') { $selected_package_name = 'Premium Package'; $selected_package_price = 45; }
elseif ($selected_package_id === 'platinum') { $selected_package_name = 'Platinum Package'; $selected_package_price = 75; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Services | Hungry Food</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        * { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; font-weight: 600; }
        body { background-color: #ffffff; color: var(--dark); overflow-x: hidden; }

        /* Hero */
        .catering-hero {
            background: linear-gradient(135deg, rgba(255,107,53,0.15) 0%, rgba(78,205,196,0.1) 100%),
                        linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.8)),
                        url('https://images.unsplash.com/photo-1555244162-803834f70033?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 180px 0 150px;
            position: relative;
            overflow: hidden;
        }
        .catering-hero::before {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 150px;
            background: linear-gradient(transparent, #ffffff);
            z-index: 1;
        }
        .catering-hero .container { position: relative; z-index: 2; }
        .catering-hero h1 { font-size: 4.5rem; font-weight: 700; margin-bottom: 1.5rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }

        /* Section Title */
        .section-title { position: relative; margin-bottom: 3rem; display: inline-block; }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px; left: 0;
            width: 60px; height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        /* Feature Cards */
        .features-section { position: relative; overflow: hidden; }
        .feature-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        .feature-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-hover); }
        .feature-icon {
            width: 80px; height: 80px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255,107,53,0.1), rgba(78,205,196,0.1));
            color: var(--primary);
            font-size: 2rem;
            transition: var(--transition);
        }
        .feature-card:hover .feature-icon { transform: scale(1.1) rotate(5deg); }

        /* Package Cards */
        .package-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
            border: 1px solid var(--light-gray);
            position: relative;
        }
        .package-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-hover); border-color: var(--primary); }
        .package-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .popular-badge {
            position: absolute;
            top: 20px; right: -30px;
            background: white;
            color: var(--primary);
            padding: 8px 40px;
            font-size: 0.875rem;
            font-weight: 600;
            transform: rotate(45deg);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 3;
        }
        .package-price { font-size: 3.5rem; font-weight: 700; margin: 0.5rem 0; line-height: 1; }
        .package-period { font-size: 1rem; opacity: 0.9; }
        .package-body { padding: 2rem; }
        .feature-list { list-style: none; padding: 0; margin: 0 0 2rem 0; }
        .feature-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light-gray);
            display: flex; align-items: center;
        }
        .feature-list li:last-child { border-bottom: none; }
        .feature-list .check { color: var(--primary); margin-right: 0.75rem; font-size: 1.1rem; }
        .feature-list .times { color: var(--light-gray); margin-right: 0.75rem; font-size: 1.1rem; }

        /* Event Types */
        .event-types-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative; overflow: hidden;
        }
        .event-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
        }
        .event-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .event-icon {
            width: 70px; height: 70px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255,107,53,0.1), rgba(78,205,196,0.1));
            color: var(--primary);
            font-size: 1.8rem;
        }

        /* Cart Badge */
        .cart-badge {
            position: fixed; top: 100px; right: 30px; z-index: 1000;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white; border-radius: 50px; padding: 12px 24px;
            box-shadow: var(--shadow);
            display: flex; align-items: center; gap: 8px;
            text-decoration: none; transition: var(--transition);
        }
        .cart-badge:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); color: white; }
        .cart-badge .badge { background: white; color: var(--primary); font-size: 0.8rem; font-weight: 600; }

        /* AI Chat Button */
        .ai-chat-btn {
            position: fixed; bottom: 30px; right: 30px; z-index: 1000;
            background: linear-gradient(135deg, #4ECDC4, #2a9d8f);
            color: white; border-radius: 50px; padding: 12px 24px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: flex; align-items: center; gap: 8px;
            text-decoration: none; transition: var(--transition);
        }
        .ai-chat-btn:hover { transform: translateY(-3px); color: white; }

        /* Buttons */
        .btn-primary {
            padding: 1rem 2.5rem; border-radius: 50px;
            font-weight: 600; font-size: 1.1rem;
            transition: var(--transition); border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 15px rgba(255,107,53,0.4);
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,107,53,0.6); }
        .btn-outline-primary {
            padding: 1rem 2.5rem; border-radius: 50px;
            font-weight: 600; font-size: 1.1rem;
            transition: var(--transition);
            border: 2px solid var(--primary); color: var(--primary);
        }
        .btn-outline-primary:hover { background: var(--primary); color: white; transform: translateY(-3px); }

        /* Process Steps */
        .process-step {
            position: relative; padding: 2rem;
            background: white; border-radius: var(--border-radius);
            box-shadow: var(--shadow); text-align: center;
            transition: var(--transition);
        }
        .process-step:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .step-number {
            position: absolute; top: -15px; left: 50%; transform: translateX(-50%);
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.2rem;
            border: 4px solid white;
        }

        /* Gallery */
        .gallery-item { border-radius: var(--border-radius); overflow: hidden; box-shadow: var(--shadow); transition: var(--transition); }
        .gallery-item:hover { transform: scale(1.02); box-shadow: var(--shadow-hover); }
        .gallery-item img { width: 100%; height: 250px; object-fit: cover; transition: transform 0.8s ease; }
        .gallery-item:hover img { transform: scale(1.05); }

        /* Booking Form Section */
        .booking-form-section {
            background: linear-gradient(135deg, rgba(255,107,53,0.05) 0%, rgba(78,205,196,0.05) 100%);
        }
        .booking-form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 3rem;
            border-top: 5px solid;
            border-image: linear-gradient(90deg, var(--primary), var(--secondary)) 1;
        }
        .package-selector .pkg-btn {
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            padding: 1.2rem 1rem;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            background: white;
            width: 100%;
        }
        .package-selector .pkg-btn:hover,
        .package-selector .pkg-btn.active {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(255,107,53,0.08), rgba(78,205,196,0.08));
        }
        .pkg-btn .pkg-price { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
        .pkg-btn .pkg-name { font-weight: 600; font-size: 0.95rem; color: var(--dark); }
        .pkg-btn .pkg-min { font-size: 0.78rem; color: var(--gray); }
        .total-box {
            background: linear-gradient(135deg, rgba(255,107,53,0.1), rgba(78,205,196,0.1));
            border-radius: 12px;
            padding: 1.5rem;
        }

        /* Success Notification */
        .success-notification {
            position: fixed; top: 100px; left: 50%; transform: translateX(-50%);
            z-index: 9999; min-width: 300px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(var(--primary), var(--secondary)); border-radius: 4px; }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease forwards; }

        @media (max-width: 768px) {
            .catering-hero { padding: 140px 0 100px; background-attachment: scroll; }
            .catering-hero h1 { font-size: 2.8rem; }
            .booking-form-card { padding: 1.5rem; }
            .cart-badge, .ai-chat-btn { padding: 10px 20px; font-size: 0.9rem; }
            .cart-badge { top: 90px; right: 15px; }
            .ai-chat-btn { bottom: 15px; right: 15px; }
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
    if (!empty($_SESSION['catering_order'])) { $cart_count += 1; }
    if ($cart_count > 0): ?>
    <a href="order.php" class="cart-badge">
        <i class="fas fa-shopping-cart"></i>
        <span>View Cart</span>
        <span class="badge rounded-pill"><?php echo $cart_count; ?></span>
    </a>
    <?php endif; ?>

    <!-- AI Chat Button -->
    <a href="ai.php" class="ai-chat-btn">
        <i class="fas fa-robot"></i>
        <span>AI Assistant</span>
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
    <?php unset($_SESSION['success_message']); endif; ?>

    <!-- Hero Section -->
    <section class="catering-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-2 fw-bold mb-4">Catering Excellence for Every Occasion</h1>
                    <p class="lead fs-3 mb-4">From intimate gatherings to grand celebrations, we create unforgettable culinary experiences</p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="#packages" class="btn btn-primary btn-lg">
                            <i class="fas fa-utensils me-2"></i>View Packages
                        </a>
                        <a href="#book-now" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-calendar-check me-2"></i>Book Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="features-section py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Why Choose Our Catering?</h2>
                    <p class="text-muted fs-5">We combine culinary artistry with impeccable service for memorable events</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="feature-card animate-fade-in-up">
                        <div class="feature-icon"><i class="fas fa-award"></i></div>
                        <h4 class="mb-3">Award-Winning Chefs</h4>
                        <p class="text-muted">Experience culinary excellence from our team of professional chefs with decades of combined expertise.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="feature-card animate-fade-in-up">
                        <div class="feature-icon"><i class="fas fa-leaf"></i></div>
                        <h4 class="mb-3">Fresh Ingredients</h4>
                        <p class="text-muted">We source the freshest local ingredients daily to ensure the highest quality in every dish we serve.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="feature-card animate-fade-in-up">
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <h4 class="mb-3">Professional Staff</h4>
                        <p class="text-muted">Our trained service team ensures flawless execution and attentive service throughout your event.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="feature-card animate-fade-in-up">
                        <div class="feature-icon"><i class="fas fa-clipboard-check"></i></div>
                        <h4 class="mb-3">Full Customization</h4>
                        <p class="text-muted">Tailor every aspect of your menu to match your theme, dietary needs, and budget requirements.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">How It Works</h2>
                    <p class="text-muted fs-5">Simple 4-step process to your perfect event</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="process-step animate-fade-in-up">
                        <div class="step-number">1</div>
                        <div class="mt-4">
                            <i class="fas fa-calendar-alt fa-3x text-primary mb-4"></i>
                            <h4 class="mb-3">Consultation</h4>
                            <p class="text-muted">Share your vision, guest count, and dietary preferences with our catering specialists.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="process-step animate-fade-in-up">
                        <div class="step-number">2</div>
                        <div class="mt-4">
                            <i class="fas fa-utensils fa-3x text-primary mb-4"></i>
                            <h4 class="mb-3">Menu Planning</h4>
                            <p class="text-muted">We create a customized menu proposal based on your preferences and budget.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="process-step animate-fade-in-up">
                        <div class="step-number">3</div>
                        <div class="mt-4">
                            <i class="fas fa-check-circle fa-3x text-primary mb-4"></i>
                            <h4 class="mb-3">Confirmation</h4>
                            <p class="text-muted">Review and approve your customized menu, date, and service details.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="process-step animate-fade-in-up">
                        <div class="step-number">4</div>
                        <div class="mt-4">
                            <i class="fas fa-glass-cheers fa-3x text-primary mb-4"></i>
                            <h4 class="mb-3">Enjoy Your Event</h4>
                            <p class="text-muted">Relax while we handle everything from setup to service and cleanup.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section class="py-5" id="packages">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Catering Packages</h2>
                    <p class="text-muted fs-5">Choose from our curated packages or customize your own</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="package-card animate-fade-in-up">
                        <div class="package-header">
                            <h3 class="mb-2">Essential</h3>
                            <p class="mb-0 opacity-90">Perfect for business lunches</p>
                        </div>
                        <div class="package-body">
                            <div class="package-price">$25<span class="package-period">/person</span></div>
                            <p class="text-muted mb-4">Minimum 20 persons</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle check"></i>3 Main Course Options</li>
                                <li><i class="fas fa-check-circle check"></i>2 Appetizer Selections</li>
                                <li><i class="fas fa-check-circle check"></i>Premium Soft Drinks</li>
                                <li><i class="fas fa-check-circle check"></i>Basic Table Setup</li>
                                <li><i class="fas fa-times times"></i>Dessert Station</li>
                                <li><i class="fas fa-times times"></i>On-site Staffing</li>
                                <li><i class="fas fa-times times"></i>Full Service Setup</li>
                            </ul>
                            <a href="#book-now" onclick="selectPackage('essential','Essential Package',25,20)" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-cart-plus me-2"></i>Select Package
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="package-card animate-fade-in-up position-relative">
                        <div class="popular-badge">Most Popular</div>
                        <div class="package-header">
                            <h3 class="mb-2">Premium</h3>
                            <p class="mb-0 opacity-90">Ideal for weddings & celebrations</p>
                        </div>
                        <div class="package-body">
                            <div class="package-price">$45<span class="package-period">/person</span></div>
                            <p class="text-muted mb-4">Minimum 50 persons</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle check"></i>5 Gourmet Main Courses</li>
                                <li><i class="fas fa-check-circle check"></i>4 Artisanal Appetizers</li>
                                <li><i class="fas fa-check-circle check"></i>Premium Beverage Service</li>
                                <li><i class="fas fa-check-circle check"></i>Dessert & Pastry Station</li>
                                <li><i class="fas fa-check-circle check"></i>Basic On-site Staff</li>
                                <li><i class="fas fa-check-circle check"></i>Complete Table Setup</li>
                                <li><i class="fas fa-times times"></i>Full Service Dining</li>
                            </ul>
                            <a href="#book-now" onclick="selectPackage('premium','Premium Package',45,50)" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-star me-2"></i>Select Package
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="package-card animate-fade-in-up">
                        <div class="package-header">
                            <h3 class="mb-2">Platinum</h3>
                            <p class="mb-0 opacity-90">Ultimate luxury experience</p>
                        </div>
                        <div class="package-body">
                            <div class="package-price">$75<span class="package-period">/person</span></div>
                            <p class="text-muted mb-4">Minimum 100 persons</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle check"></i>7 Executive Main Courses</li>
                                <li><i class="fas fa-check-circle check"></i>6 Gourmet Appetizers</li>
                                <li><i class="fas fa-check-circle check"></i>Premium Bar & Wine Service</li>
                                <li><i class="fas fa-check-circle check"></i>Dessert & Coffee Lounge</li>
                                <li><i class="fas fa-check-circle check"></i>Full Professional Staff</li>
                                <li><i class="fas fa-check-circle check"></i>Complete Setup & Cleanup</li>
                                <li><i class="fas fa-check-circle check"></i>Dedicated Event Coordinator</li>
                            </ul>
                            <a href="#book-now" onclick="selectPackage('platinum','Platinum Package',75,100)" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-crown me-2"></i>Select Package
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Event Types -->
    <section class="event-types-section py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Events We Cater</h2>
                    <p class="text-muted fs-5">From intimate gatherings to grand celebrations</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="event-card animate-fade-in-up">
                        <div class="event-icon"><i class="fas fa-glass-cheers"></i></div>
                        <h4 class="mb-3">Weddings</h4>
                        <p class="text-muted mb-0">Create your dream wedding menu with our expert catering team.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="event-card animate-fade-in-up">
                        <div class="event-icon"><i class="fas fa-briefcase"></i></div>
                        <h4 class="mb-3">Corporate Events</h4>
                        <p class="text-muted mb-0">Professional catering for meetings, conferences, and corporate parties.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="event-card animate-fade-in-up">
                        <div class="event-icon"><i class="fas fa-birthday-cake"></i></div>
                        <h4 class="mb-3">Birthday Parties</h4>
                        <p class="text-muted mb-0">Make birthdays special with delicious food and professional service.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="event-card animate-fade-in-up">
                        <div class="event-icon"><i class="fas fa-users"></i></div>
                        <h4 class="mb-3">Family Gatherings</h4>
                        <p class="text-muted mb-0">Perfect food solutions for family reunions and holiday celebrations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Our Catering Gallery</h2>
                    <p class="text-muted fs-5">A glimpse of our beautifully presented events</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1555244162-803834f70033?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Catering Spread" class="img-fluid">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Gourmet Food" class="img-fluid">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Fine Dining" class="img-fluid">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1467003909585-2f8a72700288?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Event Food" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== BOOKING FORM - Always Visible ===== -->
    <section class="booking-form-section py-5" id="book-now">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Book Your Catering</h2>
                    <p class="text-muted fs-5">Fill in the details below and we'll take care of the rest</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="booking-form-card">

                        <!-- Package Selector -->
                        <h5 class="fw-bold mb-3">1. Choose Your Package</h5>
                        <div class="row g-3 package-selector mb-4">
                            <div class="col-4">
                                <button type="button" class="pkg-btn" id="btn-essential" onclick="selectPackage('essential','Essential Package',25,20)">
                                    <div class="pkg-price">$25</div>
                                    <div class="pkg-name">Essential</div>
                                    <div class="pkg-min">min 20 persons</div>
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="pkg-btn" id="btn-premium" onclick="selectPackage('premium','Premium Package',45,50)">
                                    <div class="pkg-price">$45</div>
                                    <div class="pkg-name">Premium</div>
                                    <div class="pkg-min">min 50 persons</div>
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="pkg-btn" id="btn-platinum" onclick="selectPackage('platinum','Platinum Package',75,100)">
                                    <div class="pkg-price">$75</div>
                                    <div class="pkg-name">Platinum</div>
                                    <div class="pkg-min">min 100 persons</div>
                                </button>
                            </div>
                        </div>

                        <form method="POST" action="catering.php" onsubmit="return validateBookingForm()">
                            <input type="hidden" name="add_catering_package" value="1">
                            <input type="hidden" name="package_id" id="f_package_id" value="<?php echo htmlspecialchars($selected_package_id); ?>">
                            <input type="hidden" name="package_name" id="f_package_name" value="<?php echo htmlspecialchars($selected_package_name); ?>">
                            <input type="hidden" name="package_price" id="f_package_price" value="<?php echo $selected_package_price; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">

                            <!-- Selected Package Display -->
                            <div id="selected-pkg-display" class="alert alert-primary mb-4 <?php echo $selected_package_id ? '' : 'd-none'; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong id="disp-name"><?php echo htmlspecialchars($selected_package_name); ?></strong>
                                        <span class="text-muted ms-2" id="disp-price"><?php echo $selected_package_price ? '$'.$selected_package_price.' per person' : ''; ?></span>
                                    </div>
                                    <i class="fas fa-utensils fa-lg text-primary"></i>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-3 mt-2">2. Event Details</h5>

                            <div class="mb-3">
                                <label for="f_persons" class="form-label fw-semibold">Number of Guests <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-lg" id="f_persons" name="persons"
                                       min="20" max="500" required placeholder="How many guests?"
                                       oninput="calcTotal()">
                                <div class="form-text">Minimum 20 persons required</div>
                            </div>

                            <div class="mb-3">
                                <label for="f_event_date" class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-lg" id="f_event_date" name="event_date"
                                       min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required>
                                <div class="form-text">Please book at least 3 days in advance</div>
                            </div>

                            <div class="mb-4">
                                <label for="f_event_type" class="form-label fw-semibold">Event Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" id="f_event_type" name="event_type" required>
                                    <option value="">Select Event Type</option>
                                    <option value="Wedding">Wedding</option>
                                    <option value="Corporate Event">Corporate Event</option>
                                    <option value="Birthday Party">Birthday Party</option>
                                    <option value="Family Gathering">Family Gathering</option>
                                    <option value="Conference">Conference</option>
                                    <option value="Other">Other Special Event</option>
                                </select>
                            </div>

                            <!-- Total Price -->
                            <div id="total-box" class="total-box mb-4 d-none">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">Estimated Total:</span>
                                    <span class="fw-bold fs-3 text-primary" id="total-amount">$0</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 fs-5">
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart & Proceed
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        var currentMinPersons = 20;

        function selectPackage(id, name, price, minPersons) {
            // Update hidden fields
            document.getElementById('f_package_id').value = id;
            document.getElementById('f_package_name').value = name;
            document.getElementById('f_package_price').value = price;

            // Update display
            document.getElementById('disp-name').textContent = name;
            document.getElementById('disp-price').textContent = '$' + price + ' per person';
            document.getElementById('selected-pkg-display').classList.remove('d-none');

            // Highlight active button
            document.querySelectorAll('.pkg-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('btn-' + id).classList.add('active');

            // Update persons min
            currentMinPersons = minPersons;
            document.getElementById('f_persons').min = minPersons;
            if (!document.getElementById('f_persons').value || parseInt(document.getElementById('f_persons').value) < minPersons) {
                document.getElementById('f_persons').value = minPersons;
            }

            calcTotal();
        }

        function calcTotal() {
            var persons = parseInt(document.getElementById('f_persons').value) || 0;
            var price = parseFloat(document.getElementById('f_package_price').value) || 0;
            if (persons > 0 && price > 0) {
                document.getElementById('total-amount').textContent = '$' + (persons * price).toLocaleString();
                document.getElementById('total-box').classList.remove('d-none');
            } else {
                document.getElementById('total-box').classList.add('d-none');
            }
        }

        function validateBookingForm() {
            var pkgId = document.getElementById('f_package_id').value;
            if (!pkgId) {
                alert('Please select a catering package first.');
                return false;
            }
            var persons = parseInt(document.getElementById('f_persons').value);
            if (persons < currentMinPersons) {
                alert('Minimum ' + currentMinPersons + ' persons required for this package.');
                return false;
            }
            if (persons > 500) {
                alert('For over 500 persons, please contact us directly for a custom quote.');
                return false;
            }
            var eventDate = document.getElementById('f_event_date').value;
            var selected = new Date(eventDate);
            var minDate = new Date();
            minDate.setDate(minDate.getDate() + 3);
            if (selected < minDate) {
                alert('Please select a date at least 3 days from today.');
                return false;
            }
            return true;
        }

        // Pre-select package if passed via URL
        <?php if ($selected_package_id): ?>
        window.addEventListener('DOMContentLoaded', function() {
            selectPackage('<?php echo $selected_package_id; ?>', '<?php echo $selected_package_name; ?>', <?php echo $selected_package_price; ?>, <?php echo $selected_package_id === 'platinum' ? 100 : ($selected_package_id === 'premium' ? 50 : 20); ?>);
        });
        <?php endif; ?>

        // Scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.animate-fade-in-up').forEach(function(el) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });

            // Auto-hide success alert
            var successAlert = document.querySelector('.success-notification');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.opacity = '0';
                    setTimeout(function() { successAlert.style.display = 'none'; }, 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>
