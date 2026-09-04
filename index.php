<?php
// Add this at the VERY TOP of the file
session_start();

// Get cart count for display
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hungry Food - Order delicious food online. Fast delivery, fresh ingredients, and amazing taste.">
    <meta name="theme-color" content="#FF6B35">
    <title>Hungry Food - Delicious Food Delivered Fast</title>
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
        .hero-section {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.15) 0%, rgba(78, 205, 196, 0.1) 100%), 
                       linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)), 
                       url('https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 180px 0 150px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(transparent, #ffffff);
            z-index: 1;
        }

        .hero-section .container {
            position: relative;
            z-index: 2;
        }

        .hero-section h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .search-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
            margin-top: 3rem;
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

        /* Categories Section */
        .categories-section {
            position: relative;
            overflow: hidden;
        }

        .category-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border-top: 4px solid var(--primary);
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .category-icon {
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

        .category-card:hover .category-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(78, 205, 196, 0.2));
        }

        /* How It Works */
        .step-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .step-number {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Testimonials */
        .testimonial-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .testimonial-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            position: relative;
            transition: var(--transition);
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 4rem;
            color: rgba(255, 107, 53, 0.1);
            font-family: 'Times New Roman', serif;
        }

        /* Featured Items */
        .featured-item-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
        }

        .featured-item-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .featured-item-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .featured-item-card:hover .featured-item-img {
            transform: scale(1.05);
        }

        .rating {
            color: #FFC107;
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

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 120px 0 80px;
                background-attachment: scroll;
            }
            
            .hero-section h1 {
                font-size: 2.8rem;
            }
            
            .back-to-top, .cart-badge {
                padding: 10px 16px;
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
            
            .search-box {
                padding: 1.5rem;
                margin-top: 2rem;
            }
            
            .category-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .step-card {
                padding: 2rem 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .hero-section h1 {
                font-size: 2.2rem;
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

        /* Success Notification */
        .success-notification {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            animation: slideInDown 0.3s ease;
        }

        @keyframes slideInDown {
            from {
                transform: translateX(-50%) translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Cart Badge -->
    <?php if ($cart_count > 0): ?>
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
    <?php 
    unset($_SESSION['success_message']);
    endif; 
    ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-2 fw-bold">Craving Delicious Food?</h1>
                    <p class="lead fs-3 mt-4">Get your favorite meals delivered hot & fresh in minutes</p>
                    
                    <!-- Search Box -->
                    <div class="search-box animate-fade-in-up">
                        <form action="menu.php" method="GET" id="searchForm">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0 ps-0" 
                                               placeholder="Search for dishes, cuisines, or ingredients..." 
                                               name="search" id="searchInput">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg" id="searchBtn">
                                            <i class="fas fa-search me-2"></i>Search Menu
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <?php if ($cart_count > 0): ?>
                                <a href="order.php" class="btn btn-outline-primary btn-lg px-5">
                                    <i class="fas fa-shopping-cart me-2"></i>View Cart (<?php echo $cart_count; ?> items)
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Popular Categories</h2>
                    <p class="text-muted fs-5">Explore our wide variety of delicious dishes</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="category-card animate-fade-in-up" style="animation-delay: 0.1s">
                        <div class="category-icon">
                            <i class="fas fa-pizza-slice"></i>
                        </div>
                        <h4 class="mb-3">Pizza</h4>
                        <p class="text-muted mb-0">Freshly baked pizzas with various toppings</p>
                        <a href="menu.php?category=pizza" class="btn btn-link text-primary mt-3">Explore →</a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="category-card animate-fade-in-up" style="animation-delay: 0.2s">
                        <div class="category-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h4 class="mb-3">Main Course</h4>
                        <p class="text-muted mb-0">Hearty meals for your main course</p>
                        <a href="menu.php?category=main-course" class="btn btn-link text-primary mt-3">Explore →</a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="category-card animate-fade-in-up" style="animation-delay: 0.3s">
                        <div class="category-icon">
                            <i class="fas fa-hamburger"></i>
                        </div>
                        <h4 class="mb-3">Burgers</h4>
                        <p class="text-muted mb-0">Juicy burgers & crispy fries</p>
                        <a href="menu.php?category=burgers" class="btn btn-link text-primary mt-3">Explore →</a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="category-card animate-fade-in-up" style="animation-delay: 0.4s">
                        <div class="category-icon">
                            <i class="fas fa-ice-cream"></i>
                        </div>
                        <h4 class="mb-3">Desserts</h4>
                        <p class="text-muted mb-0">Sweet treats to satisfy your cravings</p>
                        <a href="menu.php?category=desserts" class="btn btn-link text-primary mt-3">Explore →</a>
                    </div>
                </div>
            </div>
            
            <!-- More Categories -->
            <div class="row g-4 mt-3">
                <div class="col-md-3 col-sm-6">
                    <div class="category-card animate-fade-in-up" style="animation-delay: 0.5s">
                        <div class="category-icon">
                            <i class="fas fa-drumstick-bite"></i>
                        </div>
                        <h4 class="mb-3">Appetizers</h4>
                        <p class="text-muted mb-0">Start your meal with delicious bites</p>
                        <a href="menu.php?category=appetizers" class="btn btn-link text-primary mt-3">Explore →</a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="category-card animate-fade-in-up" style="animation-delay: 0.6s">
                        <div class="category-icon">
                            <i class="fas fa-cocktail"></i>
                        </div>
                        <h4 class="mb-3">Drinks</h4>
                        <p class="text-muted mb-0">Refreshing beverages & drinks</p>
                        <a href="menu.php?category=drinks" class="btn btn-link text-primary mt-3">Explore →</a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="category-card animate-fade-in-up" style="animation-delay: 0.7s">
                        <div class="category-icon">
                            <i class="fas fa-fish"></i>
                        </div>
                        <h4 class="mb-3">Seafood</h4>
                        <p class="text-muted mb-0">Fresh seafood specialties</p>
                        <a href="menu.php?category=seafood" class="btn btn-link text-primary mt-3">Explore →</a>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="category-card animate-fade-in-up" style="animation-delay: 0.8s">
                        <div class="category-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h4 class="mb-3">Vegetarian</h4>
                        <p class="text-muted mb-0">Healthy & delicious veg options</p>
                        <a href="menu.php?filter=vegetarian" class="btn btn-link text-primary mt-3">Explore →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Items Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">Today's Specials</h2>
                    <p class="text-muted fs-5">Popular dishes our customers love</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="featured-item-card">
                        <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Margherita Pizza" class="featured-item-img">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="mb-1">Margherita Pizza</h4>
                                    <p class="text-muted mb-2">Italian • $$</p>
                                </div>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span class="ms-1">4.8</span>
                                </div>
                            </div>
                            <p class="text-muted mb-3">Fresh mozzarella, tomatoes, and basil on signature crust</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">12-15 min</span>
                                <a href="menu.php?search=pizza" class="btn btn-sm btn-primary">Order Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="featured-item-card">
                        <img src="https://images.unsplash.com/photo-1550547660-d9450f859349?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Grilled Steak" class="featured-item-img">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="mb-1">Grilled Ribeye Steak</h4>
                                    <p class="text-muted mb-2">Steak • $$$</p>
                                </div>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span class="ms-1">4.9</span>
                                </div>
                            </div>
                            <p class="text-muted mb-3">12oz prime ribeye grilled to perfection with herb butter</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">20-25 min</span>
                                <a href="menu.php?search=steak" class="btn btn-sm btn-primary">Order Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="featured-item-card">
                        <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Classic Burger" class="featured-item-img">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="mb-1">Classic Cheeseburger</h4>
                                    <p class="text-muted mb-2">Burger • $$</p>
                                </div>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span class="ms-1">4.7</span>
                                </div>
                            </div>
                            <p class="text-muted mb-3">Beef patty with cheese, lettuce, tomato & special sauce</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">15-20 min</span>
                                <a href="menu.php?search=burger" class="btn btn-sm btn-primary">Order Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="menu.php" class="btn btn-outline-primary btn-lg px-5">
                    View Full Menu <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">How It Works</h2>
                    <p class="text-muted fs-5">Get your favorite food in 3 easy steps</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="mt-4">
                            <i class="fas fa-search-location fa-3x text-primary mb-4"></i>
                            <h4 class="mb-3">Browse Menu</h4>
                            <p class="text-muted">Explore our delicious menu and choose your favorite dishes</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="mt-4">
                            <i class="fas fa-shopping-cart fa-3x text-primary mb-4"></i>
                            <h4 class="mb-3">Add to Cart</h4>
                            <p class="text-muted">Add items to your cart and customize your order as needed</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="mt-4">
                            <i class="fas fa-truck fa-3x text-primary mb-4"></i>
                            <h4 class="mb-3">Enjoy Delivery</h4>
                            <p class="text-muted">Complete checkout and enjoy hot food delivered to your door</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonial-section py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3 section-title">What Our Customers Say</h2>
                    <p class="text-muted fs-5">Thousands of satisfied customers</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b786d4d1?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                                 alt="Customer" class="rounded-circle me-3" width="60" height="60">
                            <div>
                                <h5 class="mb-1">Sarah Johnson</h5>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted">"The food always arrives hot and fresh. Best delivery service in town!"</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                                 alt="Customer" class="rounded-circle me-3" width="60" height="60">
                            <div>
                                <h5 class="mb-1">Michael Chen</h5>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted">"Great variety of food and the delivery is always on time!"</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                                 alt="Customer" class="rounded-circle me-3" width="60" height="60">
                            <div>
                                <h5 class="mb-1">Jessica Williams</h5>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted">"The website is so easy to use and the customer support is amazing!"</p>
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
                    <h2 class="display-5 fw-bold mb-4">Ready to Order?</h2>
                    <p class="mb-4 fs-5 text-muted">Order online or chat with us on WhatsApp for quick assistance</p>
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4">
                        <a href="menu.php" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-utensils me-2"></i>Order Now
                        </a>
                        <a href="https://wa.me/923170544863" target="_blank" class="btn btn-success btn-lg px-5">
                            <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
                        </a>
                        <?php if ($cart_count > 0): ?>
                        <a href="order.php" class="btn btn-outline-primary btn-lg px-5">
                            <i class="fas fa-shopping-cart me-2"></i>View Cart (<?php echo $cart_count; ?>)
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
            
            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe elements for animation
            document.querySelectorAll('.category-card, .featured-item-card, .step-card, .testimonial-card').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });

            // Auto-hide success alert
            const successAlert = document.querySelector('.success-notification');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    successAlert.style.transform = 'translateX(-50%) translateY(-100%)';
                    setTimeout(() => {
                        successAlert.style.display = 'none';
                    }, 300);
                }, 3000);
            }
            
            // Quick category navigation
            document.querySelectorAll('.category-card a, .featured-item-card a').forEach(link => {
                link.addEventListener('click', function(e) {
                    const button = this;
                    const originalHTML = button.innerHTML;
                    
                    // Add loading animation
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                    
                    // Reset after delay
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                    }, 1000);
                });
            });
            
            // Search form handling
            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const searchBtn = document.getElementById('searchBtn');
                    const originalText = searchBtn.innerHTML;
                    
                    // Show loading state
                    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
                    searchBtn.disabled = true;
                    
                    // Allow form to submit normally
                });
            }
        });
    </script>
</body>
</html>