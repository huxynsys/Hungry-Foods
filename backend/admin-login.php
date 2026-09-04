<?php
// admin/admin-login.php
session_start();

// If already logged in, redirect
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid security token. Please refresh the page and try again.";
    } else {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Simple hardcoded demo credentials (replace with DB check in production)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = 'Administrator';
        $_SESSION['is_admin'] = true;        // for compatibility with other checks
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Hungry Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: #121212;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: #1E1E1E;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2.5rem;
            width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }
        .logo {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-control {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            border-radius: 12px;
            padding: 0.8rem 1rem;
        }
        .form-control:focus {
            border-color: #FF6B35;
            box-shadow: 0 0 0 3px rgba(255,107,53,0.2);
            background: rgba(255,255,255,0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.8rem;
            border-radius: 12px;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">🍔 Hungry Food</div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-muted">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
            <p class="text-center text-muted small mt-3">
                Demo: admin / admin123
            </p>
        </form>
    </div>
</body>
</html>