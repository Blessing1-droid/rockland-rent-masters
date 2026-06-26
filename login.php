<?php
/**
 * login.php - User Login Page
 * Dark-luxury theme matching Rockland Rent Masters
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required files
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$pageTitle = "Login - Welcome Back";
$pageDescription = "Sign in to your Rockland Rent Masters account";

$errors = [];
$email = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name, role, status FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $errors[] = 'Account is deactivated. Contact support.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_first_name'] = $user['first_name'];
                    $_SESSION['user_last_name'] = $user['last_name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    
                    session_regenerate_id(true);
                    
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (86400 * 30), '/');
                    }
                    
                    $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/pages/dashboard.php';
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirect);
                    exit;
                }
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $errors[] = 'An error occurred. Please try again.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="rrm-auth-container">
    <div class="rrm-auth-card">
        <div class="rrm-auth-header">
            <div class="rrm-auth-logo">
                <svg width="48" height="48" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="10" fill="#d4a842"/>
                    <path d="M20 10L10 18V30H30V18L20 10Z" stroke="#0b1120" stroke-width="1.5" fill="none"/>
                    <path d="M17 22H23V28H17V22Z" fill="#0b1120" opacity="0.8"/>
                    <circle cx="20" cy="16" r="2" fill="#0b1120"/>
                </svg>
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to continue to your account</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="rrm-alert rrm-alert-error">
                <?php foreach ($errors as $error): ?>
                    <p>⚠️ <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="rrm-auth-form" id="loginForm">
            <div class="rrm-form-group">
                <label for="email">Email Address</label>
                <div class="rrm-input-wrapper">
                    <svg class="rrm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="you@example.com" autocomplete="email" required autofocus>
                </div>
            </div>

            <div class="rrm-form-group">
                <label for="password">Password</label>
                <div class="rrm-input-wrapper">
                    <svg class="rrm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input type="password" name="password" id="password" placeholder="Enter your password" autocomplete="current-password" required>
                    <button type="button" class="rrm-password-toggle" onclick="togglePassword('password')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="rrm-form-options">
                <label class="rrm-checkbox">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="<?= BASE_URL ?>/pages/auth/forgot-password.php" class="rrm-forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="rrm-btn-primary rrm-btn-block" id="loginBtn">Sign In →</button>
        </form>

        <div class="rrm-auth-divider">
            <span>Or continue with</span>
        </div>

        <div class="rrm-social-login">
            <button type="button" class="rrm-social-btn" onclick="socialLogin('google')">
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
            </button>
            <button type="button" class="rrm-social-btn" onclick="socialLogin('facebook')">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="#1877f2">
                    <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/>
                </svg>
                Facebook
            </button>
        </div>

        <div class="rrm-auth-footer">
            Don't have an account? <a href="<?= BASE_URL ?>/pages/auth/register.php">Create Account</a>
        </div>
    </div>
</div>

<style>
/* Auth Container */
.rrm-auth-container {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #0b1120 0%, #111827 100%);
}

.rrm-auth-card {
    max-width: 460px;
    width: 100%;
    background: #1c2840;
    border-radius: 24px;
    padding: 48px 40px;
    border: 1px solid rgba(212, 168, 66, 0.2);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.rrm-auth-header {
    text-align: center;
    margin-bottom: 32px;
}

.rrm-auth-logo {
    margin-bottom: 20px;
}

.rrm-auth-logo svg {
    filter: drop-shadow(0 0 10px rgba(212, 168, 66, 0.3));
}

.rrm-auth-header h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 8px;
}

.rrm-auth-header p {
    color: rgba(255, 255, 255, 0.5);
    font-size: 14px;
}

/* Alert */
.rrm-alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 13px;
}

.rrm-alert-error {
    background: rgba(239, 68, 68, 0.15);
    border-left: 3px solid #ef4444;
    color: #fca5a5;
}

.rrm-alert p {
    margin: 4px 0;
}

/* Form */
.rrm-auth-form {
    margin-bottom: 24px;
}

.rrm-form-group {
    margin-bottom: 24px;
}

.rrm-form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
}

.rrm-input-wrapper {
    position: relative;
}

.rrm-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    stroke: rgba(255, 255, 255, 0.4);
}

.rrm-input-wrapper input {
    width: 100%;
    padding: 14px 16px 14px 44px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    font-size: 15px;
    color: #fff;
    transition: all 0.3s ease;
}

.rrm-input-wrapper input:focus {
    outline: none;
    border-color: #d4a842;
    background: rgba(255, 255, 255, 0.1);
}

.rrm-password-toggle {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.rrm-password-toggle svg {
    width: 18px;
    height: 18px;
    stroke: rgba(255, 255, 255, 0.4);
    transition: stroke 0.2s;
}

.rrm-password-toggle:hover svg {
    stroke: #d4a842;
}

/* Form Options */
.rrm-form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
}

.rrm-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.6);
}

.rrm-checkbox input {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #d4a842;
}

.rrm-forgot-link {
    font-size: 13px;
    color: #d4a842;
    text-decoration: none;
    transition: color 0.2s;
}

.rrm-forgot-link:hover {
    color: #e8c46a;
}

/* Buttons */
.rrm-btn-primary {
    background: linear-gradient(135deg, #d4a842 0%, #b8882e 100%);
    color: #0b1120;
    border: none;
    padding: 14px 24px;
    border-radius: 40px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.rrm-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(212, 168, 66, 0.3);
}

.rrm-btn-block {
    width: 100%;
}

/* Divider */
.rrm-auth-divider {
    text-align: center;
    margin: 24px 0;
    position: relative;
}

.rrm-auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
}

.rrm-auth-divider span {
    position: relative;
    background: #1c2840;
    padding: 0 16px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
}

/* Social Login */
.rrm-social-login {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 28px;
}

.rrm-social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 40px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.rrm-social-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(212, 168, 66, 0.3);
    transform: translateY(-2px);
}

/* Footer */
.rrm-auth-footer {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
}

.rrm-auth-footer a {
    color: #d4a842;
    text-decoration: none;
    font-weight: 600;
}

.rrm-auth-footer a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 560px) {
    .rrm-auth-card {
        padding: 32px 24px;
    }
    
    .rrm-social-login {
        grid-template-columns: 1fr;
    }
    
    .rrm-auth-header h1 {
        font-size: 28px;
    }
}
</style>

<script>
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const type = input.type === 'password' ? 'text' : 'password';
    input.type = type;
}

function socialLogin(provider) {
    alert(provider.charAt(0).toUpperCase() + provider.slice(1) + ' login coming soon!');
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all fields.');
        return;
    }
    
    const btn = document.getElementById('loginBtn');
    btn.textContent = 'Signing in...';
    btn.disabled = true;
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>