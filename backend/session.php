<?php
// backend/session.php — Session management with security hardening
require_once __DIR__ . '/db.php';

// Configure session security BEFORE starting session
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

class SessionManager {
    private $conn;
    private $logged_in = false;
    private $user_data = [];

    public function __construct() {
        $this->conn = getDatabaseConnection();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->checkSession();
    }

    private function checkSession() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['login_string'])) {
            $user_id = $_SESSION['user_id'];
            $login_string = $_SESSION['login_string'];
            $user_browser = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $query = "SELECT id, username, email, password_hash, full_name, user_type, is_active
                      FROM users WHERE id = :user_id AND is_active = 1 LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                $login_check = hash('sha512', $user['password_hash'] . $user_browser);

                if ($login_check == $login_string) {
                    $this->logged_in = true;
                    $this->user_data = $user;
                } else {
                    $this->logout();
                }
            } else {
                $this->logout();
            }
        }
    }

    public function validateCSRFToken($token) {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function regenerateCSRFToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public function login($email, $password, $remember = false) {
        $query = "SELECT id, username, email, password_hash, full_name, user_type, is_active
                  FROM users
                  WHERE email = :email AND is_active = 1 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch();

            if (password_verify($password, $user['password_hash'])) {
                $user_browser = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $user_id = $user['id'];
                $login_string = hash('sha512', $user['password_hash'] . $user_browser);

                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['full_name'];
                $_SESSION['login_string'] = $login_string;
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['login_time'] = time();

                if ($remember) {
                    $this->setRememberMe($user_id);
                }

                $this->updateLastLogin($user_id);
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $this->logged_in = true;
                $this->user_data = $user;

                return ['success' => true, 'message' => 'Login successful', 'user_type' => $user['user_type']];
            }
        }

        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    public function logout() {
        session_destroy();
        $this->logged_in = false;
        $this->user_data = [];
    }

    private function setRememberMe($user_id) {
        $token = bin2hex(random_bytes(32));
        $expire = time() + (30 * 24 * 60 * 60);

        setcookie('remember_token', $token, $expire, '/', '', false, true);

        $query = "UPDATE users SET remember_token = :token, remember_expires = :expires WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expire);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    }

    public function checkRememberMe() {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];

            $query = "SELECT id FROM users WHERE remember_token = :token AND remember_expires > :now LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $now = time();
            $stmt->bindParam(':now', $now);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                $_SESSION['user_id'] = $user['id'];
                $this->checkSession();
            }
        }
    }

    private function updateLastLogin($user_id) {
        $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    }

    public function isLoggedIn() {
        return $this->logged_in;
    }

    public function getUserData() {
        return $this->user_data;
    }

    public function getUserId() {
        return $this->logged_in ? $this->user_data['id'] : null;
    }

    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }

    public function getUserType() {
        return $_SESSION['user_type'] ?? null;
    }

    public function isAdmin() {
        return $this->logged_in && ($_SESSION['user_type'] ?? '') === 'admin';
    }

    public function requireLogin($redirect_url = '../login.php') {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirect_url);
            exit();
        }
    }

    public function requireAdmin($redirect_url = '../login.php') {
        if (!$this->isAdmin()) {
            header('Location: ' . $redirect_url);
            exit();
        }
    }
}

$session = new SessionManager();

if (!$session->isLoggedIn()) {
    $session->checkRememberMe();
}

function isLoggedIn() {
    global $session;
    return $session->isLoggedIn();
}

function getCurrentUserId() {
    global $session;
    return $session->getUserId();
}

function getCurrentUsername() {
    global $session;
    return $session->getUsername();
}

function isAdmin() {
    global $session;
    return $session->isAdmin();
}

function requireLogin($redirect_url = 'login.php') {
    global $session;
    $session->requireLogin($redirect_url);
}

function getUserData() {
    global $session;
    return $session->getUserData();
}

function displayFlashMessage() {
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-exclamation-circle me-2"></i>';
        echo htmlspecialchars($_SESSION['error']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['error']);
    }

    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-check-circle me-2"></i>';
        echo htmlspecialchars($_SESSION['success']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['success']);
    }
}

// ========== CSRF Token Management ==========
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function getCSRFToken() {
    return $_SESSION['csrf_token'] ?? '';
}

function verifyCSRFToken($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function renderCSRFField() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}
?>