<?php
session_start();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Hungry Food</title>
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

        /* Hero Section */
        .contact-hero {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.15) 0%, rgba(78, 205, 196, 0.1) 100%), 
                       linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), 
                       url('https://images.unsplash.com/photo-1559925393-8be0ec4767c8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 160px 0 120px;
            position: relative;
            overflow: hidden;
        }

        .contact-hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(transparent, #ffffff);
            z-index: 1;
        }

        .contact-hero .container {
            position: relative;
            z-index: 2;
        }

        .contact-hero h1 {
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

        /* AI Chat Button */
        .ai-chat-btn {
            position: fixed;
            bottom: 100px;
            right: 30px;
            z-index: 1000;
            background: linear-gradient(135deg, #4ECDC4, #2a9d8f);
            color: white;
            border-radius: 50px;
            padding: 12px 24px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: var(--transition);
        }

        .ai-chat-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
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

        /* Contact Cards */
        .contact-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            height: 100%;
            border: 1px solid var(--light-gray);
        }

        .contact-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .contact-icon {
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

        .contact-card:hover .contact-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(78, 205, 196, 0.2));
        }

        /* Contact Form */
        .contact-form-section {
            position: relative;
            overflow: hidden;
        }

        .contact-form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.03) 0%, rgba(78, 205, 196, 0.03) 100%);
        }

        .contact-form-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .contact-form-card::before {
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

        /* Map Section */
        .map-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .map-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.1) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(78, 205, 196, 0.1) 0%, transparent 50%);
        }

        .map-container {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 2;
        }

        .map-overlay {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            max-width: 300px;
            z-index: 3;
        }

        /* FAQ Cards */
        .faq-section {
            position: relative;
            overflow: hidden;
        }

        .faq-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.03) 0%, rgba(78, 205, 196, 0.03) 100%);
        }

        .faq-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
            transition: var(--transition);
            cursor: pointer;
            border-left: 4px solid transparent;
        }

        .faq-card:hover {
            transform: translateX(10px);
            box-shadow: var(--shadow-hover);
            border-left-color: var(--primary);
        }

        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.6s ease;
        }

        .faq-answer.show {
            max-height: 200px;
        }

        /* Social Media */
        .social-section {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.05), rgba(78, 205, 196, 0.05));
            position: relative;
            overflow: hidden;
        }

        .social-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            transition: var(--transition);
            text-decoration: none;
        }

        .social-icon:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: var(--shadow-hover);
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

        /* Custom Alert Notifications */
        .custom-alert {
            animation: slideInRight 0.3s ease forwards;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            border: none;
            border-radius: 10px;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
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

        /* Business Hours */
        .hours-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .hour-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .hour-item:last-child {
            border-bottom: none;
        }

        /* SweetAlert Customization */
        .swal2-popup {
            border-radius: 16px !important;
            padding: 2rem !important;
        }

        .swal2-confirm {
            padding: 0.75rem 2rem !important;
            border-radius: 50px !important;
            font-weight: 600 !important;
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

        /* Responsive */
        @media (max-width: 768px) {
            .contact-hero {
                padding: 140px 0 80px;
                background-attachment: scroll;
            }
            
            .contact-hero h1 {
                font-size: 2.8rem;
            }
            
            .contact-form-card {
                padding: 2rem;
            }
            
            .map-overlay {
                position: relative;
                top: 0;
                right: 0;
                max-width: 100%;
                margin-top: 1rem;
            }
            
            .cart-badge, .ai-chat-btn, .back-to-top {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .cart-badge {
                top: 90px;
                right: 15px;
            }
            
            .back-to-top {
                bottom: 15px;
                right: 15px;
                width: 50px;
                height: 50px;
            }
            
            .ai-chat-btn {
                bottom: 85px;
                right: 15px;
            }
            
            .custom-alert {
                min-width: 250px;
                right: 10px;
                top: 10px;
            }
        }

        @media (max-width: 576px) {
            .contact-hero h1 {
                font-size: 2.2rem;
            }
            
            .ai-chat-btn span {
                display: none;
            }
            
            .ai-chat-btn {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                justify-content: center;
                padding: 0;
            }
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

    <!-- AI Chat Button -->
    <a href="ai.php" class="ai-chat-btn">
        <i class="fas fa-robot"></i>
        <span>AI Assistant</span>
    </a>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </a>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-2 fw-bold mb-4">We're Here to Help</h1>
                    <p class="lead fs-3 mb-4">Get in touch with our team for any questions or support</p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="tel:03170544863" class="btn btn-primary btn-lg">
                            <i class="fas fa-phone me-2"></i>Call Now
                        </a>
                        <a href="#contact-form" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-envelope me-2"></i>Send Message
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Contact Information</h2>
                    <p class="text-muted fs-5">Multiple ways to reach our team</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="contact-card animate-fade-in-up" style="animation-delay: 0.1s">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4 class="mb-3">Our Location</h4>
                        <p class="text-muted mb-2">MM Alam Road, Lahore</p>
                        <p class="text-muted mb-3">Punjab, Pakistan</p>
                        <a href="#map" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-directions me-1"></i>Get Directions
                        </a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="contact-card animate-fade-in-up" style="animation-delay: 0.2s">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4 class="mb-3">Call Us</h4>
                        <p class="text-muted mb-2">0317-0544863</p>
                        <p class="text-muted mb-3">Available 24/7</p>
                        <a href="tel:03170544863" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone-alt me-1"></i>Call Now
                        </a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="contact-card animate-fade-in-up" style="animation-delay: 0.3s">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4 class="mb-3">Email Us</h4>
                        <p class="text-muted mb-2">ahmedsultanfreefire123@gmail.com</p>
                        <p class="text-muted mb-3">Response within 2 hours</p>
                        <a href="mailto:ahmedsultanfreefire123@gmail.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i>Send Email
                        </a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="contact-card animate-fade-in-up" style="animation-delay: 0.4s">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h4 class="mb-3">WhatsApp</h4>
                        <p class="text-muted mb-2">+92 317 0544863</p>
                        <p class="text-muted mb-3">Instant Chat Support</p>
                        <a href="https://wa.me/923170544863" target="_blank" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp me-1"></i>Chat Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form & Business Hours -->
    <section class="contact-form-section py-5" id="contact-form">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="contact-form-card animate-fade-in-up">
                        <h2 class="mb-4 section-title">Send Us a Message</h2>
                        <p class="text-muted mb-4">Fill out the form below and we'll get back to you as soon as possible.</p>
                        
                        <div id="formMessages"></div>
                        
                        <form id="contactForm" method="POST">
                            <input type="hidden" name="contact_submit" value="1">
                            <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required 
                                               placeholder="Enter your full name">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required 
                                               placeholder="0317-0544863">
                                        <small class="text-muted">Format: 0317-0544863 or 03170544863</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="Enter your email address">
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Inquiry Type *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Select inquiry type</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="order">Order Status</option>
                                    <option value="catering">Catering Services</option>
                                    <option value="feedback">Feedback & Reviews</option>
                                    <option value="complaint">Customer Support</option>
                                    <option value="partnership">Business Partnership</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required 
                                          placeholder="Please describe your inquiry in detail..."></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg py-3" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Business Hours -->
                    <div class="hours-card animate-fade-in-up" style="animation-delay: 0.2s">
                        <h4 class="mb-4 section-title">Business Hours</h4>
                        <div class="hour-item">
                            <span class="fw-bold">Monday - Thursday</span>
                            <span class="text-muted">9:00 AM - 11:00 PM</span>
                        </div>
                        <div class="hour-item">
                            <span class="fw-bold">Friday - Saturday</span>
                            <span class="text-muted">9:00 AM - 12:00 AM</span>
                        </div>
                        <div class="hour-item">
                            <span class="fw-bold">Sunday</span>
                            <span class="text-muted">10:00 AM - 10:00 PM</span>
                        </div>
                        <div class="hour-item">
                            <span class="fw-bold">Delivery Hours</span>
                            <span class="text-muted">24/7</span>
                        </div>
                        <div class="hour-item">
                            <span class="fw-bold">Catering Hours</span>
                            <span class="text-muted">8:00 AM - 8:00 PM</span>
                        </div>
                    </div>
                    
                    <!-- Quick Contact -->
                    <div class="contact-card animate-fade-in-up" style="animation-delay: 0.3s">
                        <h4 class="mb-4">Quick Assistance</h4>
                        <p class="text-muted mb-4">Need immediate help? Use these direct contact methods:</p>
                        <div class="d-grid gap-3">
                            <a href="tel:03170544863" class="btn btn-outline-primary">
                                <i class="fas fa-phone me-2"></i>Call: 0317-0544863
                            </a>
                            <a href="https://wa.me/923170544863" target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp Chat
                            </a>
                            <a href="mailto:ahmedsultanfreefire123@gmail.com" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Frequently Asked Questions</h2>
                    <p class="text-muted fs-5">Find quick answers to common questions</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="faq-card animate-fade-in-up" style="animation-delay: 0.1s">
                        <div class="faq-question">
                            <h5 class="mb-0"><i class="fas fa-shipping-fast text-primary me-2"></i>What are your delivery hours?</h5>
                            <i class="fas fa-chevron-down text-primary"></i>
                        </div>
                        <div class="faq-answer mt-3">
                            <p class="text-muted mb-0">We deliver 24/7! Orders placed after 11:00 PM will be delivered the next morning from 9:00 AM onwards.</p>
                        </div>
                    </div>
                    
                    <div class="faq-card animate-fade-in-up" style="animation-delay: 0.2s">
                        <div class="faq-question">
                            <h5 class="mb-0"><i class="fas fa-utensils text-primary me-2"></i>Do you offer catering services?</h5>
                            <i class="fas fa-chevron-down text-primary"></i>
                        </div>
                        <div class="faq-answer mt-3">
                            <p class="text-muted mb-0">Yes! We provide catering for all types of events. Contact us at least 48 hours in advance for bookings. Visit our catering page for packages.</p>
                        </div>
                    </div>
                    
                    <div class="faq-card animate-fade-in-up" style="animation-delay: 0.3s">
                        <div class="faq-question">
                            <h5 class="mb-0"><i class="fas fa-map-marker-alt text-primary me-2"></i>What's your delivery radius?</h5>
                            <i class="fas fa-chevron-down text-primary"></i>
                        </div>
                        <div class="faq-answer mt-3">
                            <p class="text-muted mb-0">We deliver within 15km of MM Alam Road. Free delivery on orders above $150 within 5km radius.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="faq-card animate-fade-in-up" style="animation-delay: 0.4s">
                        <div class="faq-question">
                            <h5 class="mb-0"><i class="fas fa-credit-card text-primary me-2"></i>What payment methods do you accept?</h5>
                            <i class="fas fa-chevron-down text-primary"></i>
                        </div>
                        <div class="faq-answer mt-3">
                            <p class="text-muted mb-0">We accept cash on delivery, credit/debit cards, JazzCash, EasyPaisa, and bank transfers. All online payments are secure.</p>
                        </div>
                    </div>
                    
                    <div class="faq-card animate-fade-in-up" style="animation-delay: 0.5s">
                        <div class="faq-question">
                            <h5 class="mb-0"><i class="fas fa-leaf text-primary me-2"></i>Do you have vegetarian options?</h5>
                            <i class="fas fa-chevron-down text-primary"></i>
                        </div>
                        <div class="faq-answer mt-3">
                            <p class="text-muted mb-0">Yes! We have an extensive vegetarian menu. Look for the green leaf icon on our menu items to identify vegetarian options.</p>
                        </div>
                    </div>
                    
                    <div class="faq-card animate-fade-in-up" style="animation-delay: 0.6s">
                        <div class="faq-question">
                            <h5 class="mb-0"><i class="fas fa-clock text-primary me-2"></i>What's your average delivery time?</h5>
                            <i class="fas fa-chevron-down text-primary"></i>
                        </div>
                        <div class="faq-answer mt-3">
                            <p class="text-muted mb-0">Average delivery time is 30-45 minutes depending on location and order volume. We'll provide an estimated time when you place your order.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section py-5" id="map">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3">Visit Our Restaurant</h2>
                    <p class="text-muted fs-5">Located at the heart of Lahore's food district</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12 position-relative">
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3403.5397223429337!2d74.32939131510627!3d31.462052581391047!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3919045b9d8b2d7f%3A0xf7d9c2b6e7a5d5a5!2sMM%20Alam%20Road%2C%20Lahore%2C%20Punjab%2C%20Pakistan!5e0!3m2!1sen!2s!4v1647854321234!5m2!1sen!2s" 
                            width="100%" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    
                    <div class="map-overlay animate-fade-in-up">
                        <h5 class="mb-3"><i class="fas fa-map-pin text-primary me-2"></i>Our Location</h5>
                        <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-2"></i>MM Alam Road, Lahore</p>
                        <p class="text-muted mb-3"><i class="fas fa-city me-2"></i>Punjab, Pakistan</p>
                        <a href="https://maps.google.com/?q=MM+Alam+Road,+Lahore" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-directions me-1"></i>Get Directions
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="contact-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-car"></i>
                        </div>
                        <h5>Parking Available</h5>
                        <p class="text-muted mb-0">Free valet parking for dine-in customers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="contact-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-wheelchair"></i>
                        </div>
                        <h5>Wheelchair Accessible</h5>
                        <p class="text-muted mb-0">Fully accessible for differently-abled guests</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="contact-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <h5>Free WiFi</h5>
                        <p class="text-muted mb-0">High-speed internet for all customers</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Media Section -->
    <section class="social-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="mb-4">Follow Us on Social Media</h2>
                    <p class="text-muted mb-5">Stay updated with our latest offers, new menu items, and events!</p>
                    
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <a href="#" class="social-icon" style="background: linear-gradient(135deg, #1877F2, #0D5ABD);">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon" style="background: linear-gradient(135deg, #E4405F, #C13584);">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-icon" style="background: linear-gradient(135deg, #1DA1F2, #0D8BD9);">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon" style="background: linear-gradient(135deg, #FF0000, #CC0000);">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="social-icon" style="background: linear-gradient(135deg, #000000, #333333);">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="#" class="social-icon" style="background: linear-gradient(135deg, #0A66C2, #054B87);">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
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
                    <h2 class="display-5 fw-bold mb-4">Need Immediate Assistance?</h2>
                    <p class="mb-4 fs-5 text-muted">Our customer support team is available 24/7 to help you</p>
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4">
                        <a href="tel:03170544863" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-phone me-2"></i>Call Now: 0317-0544863
                        </a>
                        <a href="https://wa.me/923170544863" target="_blank" class="btn btn-success btn-lg px-5">
                            <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
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

            // FAQ Toggle Functionality
            const faqCards = document.querySelectorAll('.faq-card');
            faqCards.forEach(card => {
                const question = card.querySelector('.faq-question');
                const answer = card.querySelector('.faq-answer');
                const icon = card.querySelector('.fa-chevron-down');
                
                question.addEventListener('click', function() {
                    // Close all other FAQs
                    faqCards.forEach(otherCard => {
                        if (otherCard !== card) {
                            otherCard.querySelector('.faq-answer').classList.remove('show');
                            otherCard.querySelector('.fa-chevron-down').classList.remove('fa-chevron-up');
                        }
                    });
                    
                    // Toggle current FAQ
                    answer.classList.toggle('show');
                    icon.classList.toggle('fa-chevron-up');
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
            
            // Add hover effects to contact cards
            document.querySelectorAll('.contact-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
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

        // Form validation function
        function validateForm() {
            const phoneInput = document.getElementById('phone');
            if (!phoneInput) return true;
            
            const phoneValue = phoneInput.value.replace(/\D/g, '');
            
            if (phoneValue.length !== 11) {
                showNotification('Phone number must be 11 digits (e.g., 03170544863)', 'error');
                phoneInput.focus();
                return false;
            }
            
            return true;
        }
        
        // Show notification function
        function showNotification(message, type) {
            // Remove existing notifications
            const existingAlert = document.querySelector('.custom-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            // Create notification element
            const alertDiv = document.createElement('div');
            alertDiv.className = `custom-alert alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
            
            alertDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas ${type === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle'} me-3"></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // Show success modal
        function showSuccessModal(message, contactId) {
            // Store the contact ID in localStorage for tracking
            localStorage.setItem('last_contact_id', contactId);
            
            Swal.fire({
                title: 'Success!',
                html: `
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success fa-4x mb-4"></i>
                        <h4 class="mb-3">Message Sent Successfully!</h4>
                        <p class="mb-3">${message}</p>
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Reference ID:</strong> ${contactId}<br>
                                <strong>Status:</strong> Pending<br>
                                <strong>Expected Response:</strong> Within 2 hours
                            </small>
                        </div>
                    </div>
                `,
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Track Status',
                cancelButtonText: 'Send Another',
                confirmButtonColor: '#FF6B35',
                cancelButtonColor: '#6C757D',
                background: '#fff',
                color: '#292929',
                customClass: {
                    popup: 'rounded-3',
                    confirmButton: 'btn-lg',
                    cancelButton: 'btn-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Store form data in localStorage before redirecting
                    const formData = {
                        name: document.getElementById('name').value,
                        phone: document.getElementById('phone').value,
                        email: document.getElementById('email').value,
                        subject: document.getElementById('subject').value,
                        message: document.getElementById('message').value,
                        contact_id: contactId,
                        timestamp: new Date().toISOString()
                    };
                    localStorage.setItem('contact_data_' + contactId, JSON.stringify(formData));
                    
                    // Redirect to status tracking page
                    window.location.href = `contact_status.php?id=${contactId}`;
                } else {
                    // Reset form and show it again
                    const submitBtn = document.getElementById('submitBtn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Message';
                        submitBtn.disabled = false;
                    }
                    
                    // Scroll to form
                    document.getElementById('contact-form').scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                }
            });
        }
        
        // Form submission with AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');
            
            if (contactForm) {
                contactForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    console.log('Form submitted');
                    
                    // Validate form
                    if (!validateForm()) {
                        console.log('Form validation failed');
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = document.getElementById('submitBtn');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<div class="loader"></div> Sending...';
                    submitBtn.disabled = true;
                    
                    try {
                        // Collect form data
                        const formData = new FormData(this);
                        console.log('FormData collected');
                        
                        // Store form data temporarily in localStorage
                        const formDataObj = {
                            name: document.getElementById('name').value,
                            phone: document.getElementById('phone').value,
                            email: document.getElementById('email').value,
                            subject: document.getElementById('subject').value,
                            message: document.getElementById('message').value,
                            timestamp: new Date().toISOString()
                        };
                        localStorage.setItem('pending_contact', JSON.stringify(formDataObj));
                        
                        // Send AJAX request
                        console.log('Sending request to backend/contact_process.php');
                        const response = await fetch('backend/contact_process.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        console.log('Response received, status:', response.status);
                        
                        // Check if response is JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            const text = await response.text();
                            console.error('Non-JSON response:', text.substring(0, 200));
                            throw new Error('Server returned non-JSON response. Check backend for errors.');
                        }
                        
                        const result = await response.json();
                        console.log('Response JSON:', result);
                        
                        if (result.success) {
                            console.log('Success!');
                            
                            // Remove pending contact from localStorage
                            localStorage.removeItem('pending_contact');
                            
                            // Save contact data to localStorage
                            formDataObj.contact_id = result.contact_id;
                            localStorage.setItem('contact_' + result.contact_id, JSON.stringify(formDataObj));
                            
                            // Save to recent contacts list
                            saveToRecentContacts(formDataObj);
                            
                            // Show success notification
                            showNotification(result.message, 'success');
                            
                            // Reset form
                            contactForm.reset();
                            
                            // Show success modal
                            showSuccessModal(result.message, result.contact_id);
                            
                        } else {
                            console.log('Error from server:', result.message);
                            // Show error notification
                            showNotification(result.message, 'error');
                            
                            // Re-enable submit button
                            submitBtn.innerHTML = originalBtnText;
                            submitBtn.disabled = false;
                        }
                        
                    } catch (error) {
                        console.error('Fetch error:', error);
                        showNotification('An error occurred. Please try again. Error: ' + error.message, 'error');
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }
                });
            }
            
            // Save contact to recent contacts list
            function saveToRecentContacts(contactData) {
                let recentContacts = JSON.parse(localStorage.getItem('recent_contacts') || '[]');
                
                // Add new contact at the beginning
                recentContacts.unshift(contactData);
                
                // Keep only last 10 contacts
                if (recentContacts.length > 10) {
                    recentContacts = recentContacts.slice(0, 10);
                }
                
                localStorage.setItem('recent_contacts', JSON.stringify(recentContacts));
            }
            
            // Load pending contact if exists (in case of page refresh during submission)
            const pendingContact = localStorage.getItem('pending_contact');
            if (pendingContact) {
                try {
                    const contact = JSON.parse(pendingContact);
                    document.getElementById('name').value = contact.name || '';
                    document.getElementById('phone').value = contact.phone || '';
                    document.getElementById('email').value = contact.email || '';
                    document.getElementById('subject').value = contact.subject || '';
                    document.getElementById('message').value = contact.message || '';
                    
                    showNotification('Found a pending submission. Please try submitting again.', 'info');
                } catch (e) {
                    console.error('Error loading pending contact:', e);
                }
            }
        });
    </script>
</body>
</html>