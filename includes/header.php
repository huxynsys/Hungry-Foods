<?php
// Start session at the VERY TOP of the file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate cart count
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    $cart_count += count($_SESSION['cart']);
}
if (!empty($_SESSION['catering_order'])) {
    $cart_count += 1;
}
?>
<!-- Responsive CSS -->
<link rel="stylesheet" href="assets/css/modern-ui.css">
<link rel="stylesheet" href="assets/css/responsive.css">

<header class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-2">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <div class="logo-wrapper d-flex align-items-center justify-content-center me-2">
                    <i class="fas fa-utensils text-white fs-5"></i>
                </div>
                <div>
                    <span class="fw-bold fs-4" style="color: #FF6B35;">Hungry Food</span>
                    <small class="d-block" style="color: #666; font-size: 0.7rem; margin-top: -5px;">Delicious & Fresh</small>
                </div>
            </a>
            
            <!-- Mobile nav backdrop -->
            <div class="mobile-nav-backdrop" id="mobileNavBackdrop"></div>

            <!-- Mobile toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Main navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php 
                    $current_page = basename($_SERVER['PHP_SELF']);
                    $nav_items = [
                        'index.php' => ['Home', 'fas fa-home'],
                        'about.php' => ['About', 'fas fa-info-circle'],
                        'menu.php' => ['Menu', 'fas fa-utensils'],
                        'catering.php' => ['Catering', 'fas fa-concierge-bell'],
                        'contact.php' => ['Contact', 'fas fa-envelope'],
                        'reservation.php' => ['Reservation', 'fas fa-calendar-check']
                    ];
                    
                    foreach($nav_items as $page => $details):
                        $active = ($current_page == $page) ? 'active' : '';
                        $is_catering = ($page == 'catering.php');
                    ?>
                    <li class="nav-item mx-1">
                        <a class="nav-link position-relative px-3 py-2 rounded <?php echo $active; ?>" 
                           href="<?php echo $page; ?>"
                           style="color: #333; font-weight: 500; transition: all 0.3s ease;">
                            <i class="<?php echo $details[1]; ?> me-2" 
                               style="color: <?php echo $is_catering ? '#FF6B35' : '#666'; ?>;"></i>
                            <?php echo $details[0]; ?>
                            <?php if($active): ?>
                            <span class="position-absolute bottom-0 start-50 translate-middle-x" 
                                  style="width: 30px; height: 3px; background: #FF6B35; border-radius: 10px;"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    
                    <!-- User Section -->
                    <div class="d-flex align-items-center ms-lg-4 mt-3 mt-lg-0">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <!-- Logged In User -->
                            <li class="nav-item dropdown list-unstyled">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" 
                                   href="#" 
                                   role="button" 
                                   data-bs-toggle="dropdown"
                                   style="background: linear-gradient(135deg, #FF6B35, #FF8B5C); 
                                          padding: 8px 16px; 
                                          border-radius: 30px;
                                          border: none;">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 32px; height: 32px;">
                                        <i class="fas fa-user" style="color: #FF6B35;"></i>
                                    </div>
                                    <span class="text-white fw-medium">
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" 
                                    style="min-width: 220px; border-radius: 12px; overflow: hidden;">
                                    <li>
                                        <a class="dropdown-item py-3 d-flex align-items-center" 
                                           href="order.php"
                                           style="border-bottom: 1px solid #f0f0f0;">
                                            <i class="fas fa-shopping-bag me-3" style="color: #FF6B35; width: 20px;"></i>
                                            <span>My Orders</span>
                                        </a>
                                    </li>
                                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                        <li>
                                            <a class="dropdown-item py-3 d-flex align-items-center" 
                                               href="admin/dashboard.php"
                                               style="border-bottom: 1px solid #f0f0f0;">
                                                <i class="fas fa-chart-line me-3" style="color: #FF6B35; width: 20px;"></i>
                                                <span>Dashboard</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item py-3 d-flex align-items-center text-danger" 
                                           href="includes/logout.php">
                                            <i class="fas fa-sign-out-alt me-3" style="width: 20px;"></i>
                                            <span>Logout</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Guest User -->
                            <li class="nav-item list-unstyled me-2">
                                <a class="nav-link px-3 py-2 rounded" 
                                   href="login.php"
                                   style="color: #666; border: 1px solid #ddd; transition: all 0.3s ease;">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Login
                                </a>
                            </li>
                            <li class="nav-item list-unstyled">
                                <a class="btn px-4 py-2 rounded-pill fw-medium shadow-sm" 
                                   href="login.php?register=1"
                                   style="background: linear-gradient(135deg, #FF6B35, #FF8B5C); 
                                          color: white; 
                                          border: none;">
                                   <i class="fas fa-user-plus me-2"></i>
                                   Sign Up
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Cart Icon -->
                        <li class="nav-item list-unstyled ms-3">
                            <a class="nav-link cart-icon position-relative" href="order.php">
                                <div class="rounded-circle d-flex align-items-center justify-content-center p-2 shadow-sm"
                                     style="width: 45px; height: 45px; 
                                            background: linear-gradient(135deg, #FF6B35, #FF8B5C);
                                            transition: all 0.3s ease;">
                                    <i class="fas fa-shopping-cart fs-5 text-white"></i>
                                    <?php if($cart_count > 0): ?>
                                    <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white"
                                          style="font-size: 0.7rem; 
                                                 min-width: 22px; 
                                                 height: 22px; 
                                                 display: flex; 
                                                 align-items: center; 
                                                 justify-content: center;
                                                 padding: 0 6px;">
                                        <?php echo $cart_count; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                    </div>
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
    /* Enhanced CSS for better UX */
    .navbar-nav .nav-link {
        border-radius: 8px;
        margin: 0 3px;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .navbar-nav .nav-link:not(.active):hover {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 139, 92, 0.1) 100%);
        color: #FF6B35 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.15);
    }
    
    .navbar-nav .nav-link.active {
        color: #FF6B35 !important;
        font-weight: 600;
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 139, 92, 0.1) 100%);
    }
    
    /* Catering menu item special styling */
    .navbar-nav .nav-link[href="catering.php"]:not(.active):hover i {
        color: #FF6B35 !important;
    }
    
    .cart-icon:hover div {
        transform: scale(1.1) rotate(-5deg);
        box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
    }
    
    .cart-count {
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
    }
    
    .dropdown-menu {
        animation: fadeInDown 0.3s ease;
        border: 1px solid #FF6B35;
        border-top: 3px solid #FF6B35;
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .dropdown-item {
        border-radius: 8px;
        margin: 2px 8px;
        transition: all 0.2s ease;
        font-weight: 500;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 139, 92, 0.1) 100%);
        transform: translateX(8px);
        color: #FF6B35 !important;
    }
    
    .dropdown-item.text-danger:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545 !important;
    }
    
    .navbar-toggler:focus {
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.3);
        outline: none;
    }
    
    /* Mobile responsive adjustments */
    @media (max-width: 991.98px) {
        .navbar-nav .nav-link {
            padding: 12px 20px;
            margin: 4px 0;
            text-align: center;
        }
        
        .navbar-collapse {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-top: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid #FF6B35;
        }
        
        .d-flex.align-items-center {
            border-top: 1px solid rgba(255, 107, 53, 0.2);
            padding-top: 20px;
            margin-top: 15px;
        }
        
        .navbar-toggler {
            order: 3;
        }
        
        .navbar-brand {
            order: 1;
        }
        
        .navbar-toggler {
            order: 2;
        }
    }
    
    /* Desktop enhancements */
    @media (min-width: 992px) {
        .navbar {
            padding-top: 8px;
            padding-bottom: 8px;
        }
        
        .navbar-nav .nav-link {
            padding: 10px 18px;
        }
        
        .sticky-top {
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
        }
    }
    
    /* Animation for cart icon */
    @keyframes cartBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    
    .cart-icon:hover i {
        animation: cartBounce 0.5s ease;
    }
</style>

<script>
    // Add cart animation and interactions
    document.addEventListener('DOMContentLoaded', function() {
        const cartIcon = document.querySelector('.cart-icon');
        const cartBadge = document.querySelector('.cart-count');
        
        // Add click animation
        cartIcon.addEventListener('click', function(e) {
            // If cart is empty, show alert
            <?php if($cart_count == 0): ?>
            e.preventDefault();
            showCartEmptyAlert();
            <?php endif; ?>
            
            // Bounce animation
            const cartDiv = this.querySelector('div');
            cartDiv.style.transform = 'scale(0.9)';
            setTimeout(() => {
                cartDiv.style.transform = '';
            }, 150);
        });
        
        // Show empty cart alert
        function showCartEmptyAlert() {
            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning position-fixed';
            alertDiv.style.cssText = `
                top: 100px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                background: linear-gradient(135deg, #fff3cd, #ffeaa7);
                border: 2px solid #ffc107;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(255, 193, 7, 0.3);
                animation: slideInRight 0.3s ease;
            `;
            alertDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-shopping-cart me-3" style="color: #856404; font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="mb-1" style="color: #856404;">Your cart is empty</h6>
                        <p class="mb-0 small" style="color: #856404;">Add some delicious items to get started!</p>
                    </div>
                </div>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Remove alert after 3 seconds
            setTimeout(() => {
                alertDiv.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(alertDiv);
                }, 300);
            }, 3000);
            
            // Add CSS animations
            const style = document.createElement('style');
            style.textContent = `
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
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Update cart count on page (if you add items via AJAX)
        function updateCartCount(count) {
            if (count > 0) {
                if (!cartBadge) {
                    // Create badge if it doesn't exist
                    const cartDiv = document.querySelector('.cart-icon div');
                    const badge = document.createElement('span');
                    badge.className = 'cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white';
                    badge.style.cssText = `
                        font-size: 0.7rem; 
                        min-width: 22px; 
                        height: 22px; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center;
                        padding: 0 6px;
                    `;
                    badge.textContent = count;
                    cartDiv.appendChild(badge);
                } else {
                    cartBadge.textContent = count;
                    cartBadge.style.display = 'flex';
                }
            } else if (cartBadge) {
                cartBadge.style.display = 'none';
            }
        }
        
        // Initialize cart count from PHP
        updateCartCount(<?php echo $cart_count; ?>);
        
        // Add hover effect to navbar items
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.boxShadow = '0 4px 15px rgba(255, 107, 53, 0.15)';
                }
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.boxShadow = 'none';
            });
        });
    });
</script>