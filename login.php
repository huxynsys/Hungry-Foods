<?php
session_start();

// Use the central PDO database connection (secure)
require_once __DIR__ . '/backend/db.php';
global $conn;

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    $base = rtrim(dirname($_SERVER["PHP_SELF"]), "/"); 
    header("Location: " . $base . "/index.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        $_SESSION['error'] = 'Security token missing. Please try again.';
    } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token. Please refresh and try again.';
    } else {
    
    if ($action === 'login') {
        processLogin($conn);
    } elseif ($action === 'register') {
        processRegistration($conn);
    }
    }
}

function processLogin($conn) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields";
        return;
    }
    
    // Check if users table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($table_check->rowCount() == 0) {
        $_SESSION['error'] = "System not initialized. Please register first.";
        return;
    }
    
    // Check if user exists using PDO prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Verify password (column is 'password_hash')
        if (password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'] ?? 'customer';
            
            $base = rtrim(dirname($_SERVER["PHP_SELF"]), "/"); 
            header("Location: " . $base . "/index.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
        }
    } else {
        $_SESSION['error'] = "Invalid email or password";
    }
}

function processRegistration($conn) {
    // Get form data and sanitize
    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $last_name  = htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email      = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone      = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "All required fields must be filled";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match";
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode(", ", $errors);
        $_SESSION['register_data'] = $_POST;
        return;
    }
    
    // Check if email already exists using PDO
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $checkStmt->execute([':email' => $email]);
    
    if ($checkStmt->rowCount() > 0) {
        $_SESSION['error'] = "Email already registered";
        $_SESSION['register_data'] = $_POST;
        return;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Build full_name and username
    $full_name = $first_name . ' ' . $last_name;
    $username  = strstr($email, '@', true) ?: $email;
    
    // Check if it's the first user to set as admin
    $checkCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $user_type = ($checkCount == 0) ? 'admin' : 'customer';

    // Insert using PDO prepared statement
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, phone, user_type) VALUES (:username, :email, :password_hash, :full_name, :phone, :user_type)");

    if ($stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $hashed_password,
        ':full_name' => $full_name,
        ':phone' => $phone,
        ':user_type' => $user_type
    ])) {
        $user_id = $conn->lastInsertId();
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id']  = $user_id;
        $_SESSION['username'] = $full_name;
        $_SESSION['email']    = $email;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['success']  = "Registration successful! Welcome to Hungry Food";
        
        $base = rtrim(dirname($_SERVER["PHP_SELF"]), "/"); 
        header("Location: " . $base . "/index.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        $_SESSION['register_data'] = $_POST;
    }
}

function createTablesIfNotExist($conn) {
    // Create users table (matching your actual structure) - using PDO
    $users_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50),
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        phone VARCHAR(20),
        user_type ENUM('customer', 'admin') DEFAULT 'customer',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($users_table);
    
    // Create orders table - using PDO
    $orders_table = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total DECIMAL(10,2) NOT NULL,
        status ENUM('pending','confirmed','preparing','delivered','cancelled') DEFAULT 'pending',
        delivery_address TEXT,
        special_instructions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($orders_table);
    
    // Create order_items table - using PDO
    $order_items_table = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        menu_item_name VARCHAR(100) NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($order_items_table);
    
    // Create reservations table - using PDO
    $reservations_table = "CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        guests INT NOT NULL,
        reservation_date DATE NOT NULL,
        reservation_time TIME NOT NULL,
        table_type VARCHAR(50),
        occasion VARCHAR(50),
        special_requests TEXT,
        status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($reservations_table);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['register']) ? 'Register' : 'Login'; ?> | Hungry Food</title>
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/responsive.css">
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
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.15) 0%, rgba(78, 205, 196, 0.1) 100%),
                       url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.9), rgba(78, 205, 196, 0.8));
            z-index: 1;
        }

        .container {
            position: relative;
            z-index: 2;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .logo-wrapper {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        .form-control, .form-select {
            padding: 0.875rem 1rem;
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

        .input-group-text {
            background: var(--light);
            border: 2px solid var(--light-gray);
            border-right: none;
            color: var(--gray);
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group .form-control:focus + .input-group-text {
            border-color: var(--primary);
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

        /* Password toggle */
        .password-toggle {
            background: var(--light);
            border: 2px solid var(--light-gray);
            border-left: none;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        /* Custom Checkbox */
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            color: var(--gray);
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--light-gray);
        }

        .divider span {
            padding: 0 1rem;
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

        /* Alert Messages */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.25rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
            border-left: 4px solid #28a745;
            color: #155724;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                background-attachment: scroll;
            }

            .auth-card {
                padding: 1.5rem;
                margin: 1rem;
            }
        }

        @media (max-width: 420px) {
            .auth-card {
                padding: 1.25rem;
                margin: 0.5rem;
            }

            .logo-wrapper {
                width: 56px;
                height: 56px;
                font-size: 1.5rem;
                border-radius: 14px;
            }

            .btn-primary {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
                width: 100%;
            }
        }

        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Welcome message for first user */
        .welcome-message {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="auth-card animate-fade-in-up">
                    <div class="logo-wrapper floating">
                        <i class="fas fa-utensils"></i>
                    </div>
                    
                    <!-- Welcome message for first-time setup -->
                    <?php 
                    $checkUsers = $conn->query("SELECT COUNT(*) as count FROM users");
                    $userCount = $checkUsers->fetch()['count'];
                    if ($userCount == 0 && isset($_GET['register'])): ?>
                    <div class="welcome-message">
                        <h4 class="mb-2"><i class="fas fa-crown me-2"></i>Welcome to Hungry Food!</h4>
                        <p class="mb-0">You're about to create the first account. This account will have administrator privileges.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <h2 class="fw-bold mb-2">
                            <?php echo isset($_GET['register']) ? 'Create Account' : 'Welcome Back!'; ?>
                        </h2>
                        <p class="text-muted">
                            <?php echo isset($_GET['register']) ? 'Sign up to start your delicious journey' : 'Sign in to continue to your account'; ?>
                        </p>
                    </div>
                    
                    <!-- Display Messages -->
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Forms -->
                    <div id="loginForm" style="<?php echo isset($_GET['register']) ? 'display: none;' : 'display: block;'; ?>">
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                            
                            
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="loginEmail" name="email" required 
                                           placeholder="Enter your email" value="<?php echo $_SESSION['register_data']['email'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="loginPassword" name="password" required 
                                           placeholder="Enter your password">
                                    <button class="btn password-toggle" type="button" id="toggleLoginPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">Don't have an account? 
                                <a href="?register=1" class="text-primary fw-bold text-decoration-none">Sign up here</a>
                            </p>
                            <!-- 🔹 ADMIN LOGIN LINK -->
                            <p class="text-center mt-2 mb-0">
                                <a href="admin/admin-login.php" class="text-muted small text-decoration-none">
                                    <i class="fas fa-shield-alt me-1"></i> Admin Login
                                </a>
                            </p>
                        </div>
                    </div>
                    
                    <div id="registerForm" style="<?php echo isset($_GET['register']) ? 'display: block;' : 'display: none;'; ?>">
                        <form action="" method="POST" id="registrationForm">
                            <input type="hidden" name="action" value="register">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="firstName" class="form-label">First Name *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="firstName" name="first_name" required 
                                                   placeholder="John" value="<?php echo htmlspecialchars($_SESSION['register_data']['first_name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lastName" class="form-label">Last Name *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="lastName" name="last_name" required 
                                                   placeholder="Doe" value="<?php echo htmlspecialchars($_SESSION['register_data']['last_name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="registerEmail" name="email" required 
                                           placeholder="john@example.com" value="<?php echo htmlspecialchars($_SESSION['register_data']['email'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="(123) 456-7890" value="<?php echo htmlspecialchars($_SESSION['register_data']['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="registerPassword" name="password" required 
                                           placeholder="Create a strong password">
                                    <button class="btn password-toggle" type="button" id="toggleRegisterPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Password must be at least 8 characters with uppercase, lowercase, and numbers
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required 
                                           placeholder="Confirm your password">
                                </div>
                                <div class="form-text text-danger" id="passwordMatchError"></div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary text-decoration-none">Terms of Service</a> and <a href="#" class="text-primary text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary" id="registerButton">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">Already have an account? 
                                <a href="login.php" class="text-primary fw-bold text-decoration-none">Sign in here</a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="../index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle password visibility
        function togglePasswordVisibility(inputId, buttonId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = document.getElementById(buttonId);
            
            if (!passwordInput || !toggleButton) return;
            
            const icon = toggleButton.querySelector('i');
            
            toggleButton.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
        
        // Initialize password toggles
        togglePasswordVisibility('loginPassword', 'toggleLoginPassword');
        togglePasswordVisibility('registerPassword', 'toggleRegisterPassword');
        
        // Password validation
        const registerPassword = document.getElementById('registerPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        const passwordMatchError = document.getElementById('passwordMatchError');
        const registerButton = document.getElementById('registerButton');
        
        if (registerPassword && confirmPassword && registerButton) {
            function validatePassword() {
                const password = registerPassword.value;
                const confirm = confirmPassword.value;
                
                if (confirm === '') {
                    passwordMatchError.textContent = '';
                    registerButton.disabled = false;
                    return;
                }
                
                if (password !== confirm) {
                    passwordMatchError.textContent = 'Passwords do not match!';
                    registerButton.disabled = true;
                } else {
                    passwordMatchError.textContent = '';
                    registerButton.disabled = false;
                }
            }
            
            registerPassword.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
        }
        
        // Form validation
        const registrationForm = document.getElementById('registrationForm');
        if (registrationForm) {
            registrationForm.addEventListener('submit', function(e) {
                const password = registerPassword?.value || '';
                const confirm = confirmPassword?.value || '';
                const terms = document.getElementById('terms')?.checked;
                
                // Password strength validation
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                
                if (!passwordRegex.test(password)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Weak Password',
                        text: 'Password must be at least 8 characters with uppercase, lowercase letters and numbers.',
                        confirmButtonColor: '#FF6B35'
                    });
                    return false;
                }
                
                if (password !== confirm) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'Passwords do not match!',
                        confirmButtonColor: '#FF6B35'
                    });
                    return false;
                }
                
                if (!terms) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Terms Required',
                        text: 'You must agree to the Terms of Service and Privacy Policy.',
                        confirmButtonColor: '#FF6B35'
                    });
                    return false;
                }
                
                return true;
            });
        }
        
        // Show/hide forms based on URL
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('register')) {
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('registerForm').style.display = 'block';
            }
        });
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Clear session data after showing
            <?php unset($_SESSION['register_data']); ?>
        });
    </script>
</body>
</html>
<?php 
// Release the PDO connection (PDO has no close() method; setting to null frees it)
$conn = null;