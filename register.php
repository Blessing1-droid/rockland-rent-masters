<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * register.php - User Registration Page
 * Dark-luxury theme matching Rockland Rent Masters
 * FIXED to match database schema (name column, no status or manual created_at)
 */

// Start session
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

// Page metadata
$pageTitle = "Create Account - Join Rockland Rent Masters";
$pageDescription = "Create your free account to start finding your perfect rental property.";

// Initialize variables
$errors = [];
$success = false;
$formData = [
    'email' => '',
    'name' => '',
    'phone' => '',
    'role' => 'renter'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $formData['email'] = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $formData['name'] = htmlspecialchars(trim($_POST['name'] ?? ''));
    $formData['phone'] = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $formData['role'] = in_array($_POST['role'] ?? '', ['renter', 'landlord']) ? $_POST['role'] : 'renter';
    
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Validation
    if (empty($formData['email'])) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($formData['name'])) {
        $errors[] = 'Full name is required.';
    }
    
    if (!$terms) {
        $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } else {
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $formData['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email already exists.';
            }
        } catch (PDOException $e) {
            error_log("Registration email check error: " . $e->getMessage());
            $errors[] = 'Database error occurred. Please try again later.';
        }
    }
    
    // Create user account
    if (empty($errors)) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // CORRECTED INSERT: matches actual table columns (name, no status/created_at)
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES (:email, :password, :name, :phone, :role)
            ");
            
            $result = $stmt->execute([
                ':email' => $formData['email'],
                ':password' => $hash,
                ':name' => $formData['name'],
                ':phone' => $formData['phone'] ?: null,  // empty string becomes NULL
                ':role' => $formData['role']
            ]);
            
            if ($result) {
                $userId = $pdo->lastInsertId();
                
                // Auto-login after registration
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $formData['email'];
                $_SESSION['user_name'] = $formData['name'];
                $_SESSION['user_role'] = $formData['role'];
                $_SESSION['user_phone'] = $formData['phone'];
                $_SESSION['logged_in'] = true;
                
                session_regenerate_id(true);
                $success = true;
                
                // Set welcome flash message
                $_SESSION['flash_message'] = 'Welcome to Rockland Rent Masters! Your account has been created successfully.';
                $_SESSION['flash_type'] = 'success';
                
                // Redirect based on role
                $redirect = ($formData['role'] === 'landlord') 
                    ? BASE_URL . '/pages/landlord/add-property.php' 
                    : BASE_URL . '/pages/renter/search.php';
                
                header("Location: $redirect");
                exit;
            } else {
                $errors[] = 'Failed to create account. Please try again.';
            }
        } catch (PDOException $e) {
            // For production, log and show generic message.
            // For debugging, uncomment the next line to see the actual error.
            // die("Database error: " . $e->getMessage());
            error_log("Registration insert error: " . $e->getMessage());
            $errors[] = 'Registration failed. Please try again later.';
        }
    }
}

// Include header
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="rrm-auth-container">
    <div class="rrm-auth-card rrm-auth-card--wide">
        <!-- Header -->
        <div class="rrm-auth-header">
            <div class="rrm-auth-logo">
                <svg width="56" height="56" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="10" fill="#d4a842"/>
                    <path d="M20 10L10 18V30H30V18L20 10Z" stroke="#0b1120" stroke-width="1.5" fill="none"/>
                    <path d="M17 22H23V28H17V22Z" fill="#0b1120" opacity="0.8"/>
                    <circle cx="20" cy="16" r="2" fill="#0b1120"/>
                </svg>
            </div>
            <h1>Create Account</h1>
            <p>Join Rockland Rent Masters today</p>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="rrm-alert rrm-alert-error">
                <div class="rrm-alert-icon">⚠️</div>
                <div class="rrm-alert-content">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="" class="rrm-auth-form" id="registerForm" novalidate>
            <!-- Full Name Field (replaces first/last) -->
            <div class="rrm-form-group">
                <label for="name">Full Name <span class="rrm-required">*</span></label>
                <div class="rrm-input-wrapper">
                    <svg class="rrm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="<?= htmlspecialchars($formData['name']) ?>" 
                           placeholder="John Doe" 
                           autocomplete="name"
                           required>
                </div>
            </div>

            <!-- Email Field -->
            <div class="rrm-form-group">
                <label for="email">Email Address <span class="rrm-required">*</span></label>
                <div class="rrm-input-wrapper">
                    <svg class="rrm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="<?= htmlspecialchars($formData['email']) ?>" 
                           placeholder="you@example.com" 
                           autocomplete="email"
                           required>
                </div>
            </div>

            <!-- Phone Field (Optional) -->
            <div class="rrm-form-group">
                <label for="phone">Phone Number <span class="rrm-optional">(Optional)</span></label>
                <div class="rrm-input-wrapper">
                    <svg class="rrm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    <input type="tel" 
                           name="phone" 
                           id="phone" 
                           value="<?= htmlspecialchars($formData['phone']) ?>" 
                           placeholder="(555) 123-4567" 
                           autocomplete="tel">
                </div>
            </div>

            <!-- Password Fields Row (unchanged) -->
            <div class="rrm-form-row">
                <div class="rrm-form-group">
                    <label for="password">Password <span class="rrm-required">*</span></label>
                    <div class="rrm-input-wrapper">
                        <svg class="rrm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               placeholder="Min. 8 characters" 
                               autocomplete="new-password"
                               required>
                        <button type="button" class="rrm-password-toggle" onclick="togglePassword('password')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="rrm-password-hint">
                        Must be 8+ characters, include uppercase letter and number
                    </div>
                    <div class="rrm-strength-meter" id="strengthMeter"></div>
                </div>
                <div class="rrm-form-group">
                    <label for="confirm_password">Confirm Password <span class="rrm-required">*</span></label>
                    <div class="rrm-input-wrapper">
                        <svg class="rrm-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" 
                               name="confirm_password" 
                               id="confirm_password" 
                               placeholder="Confirm your password" 
                               autocomplete="off"
                               required>
                    </div>
                    <div class="rrm-match-message" id="matchMessage"></div>
                </div>
            </div>

            <!-- Role Selection (added unique ids for accessibility) -->
            <div class="rrm-form-group">
                <label>I am a... <span class="rrm-required">*</span></label>
                <div class="rrm-role-group">
                    <label class="rrm-role-btn <?= $formData['role'] === 'renter' ? 'active' : '' ?>">
                        <input type="radio" name="role" value="renter" id="role_renter" <?= $formData['role'] === 'renter' ? 'checked' : '' ?>>
                        <span class="rrm-role-icon">🔍</span>
                        <span class="rrm-role-title">Renter</span>
                        <small>Looking for a property</small>
                    </label>
                    <label class="rrm-role-btn <?= $formData['role'] === 'landlord' ? 'active' : '' ?>">
                        <input type="radio" name="role" value="landlord" id="role_landlord" <?= $formData['role'] === 'landlord' ? 'checked' : '' ?>>
                        <span class="rrm-role-icon">🏠</span>
                        <span class="rrm-role-title">Landlord</span>
                        <small>Listing properties</small>
                    </label>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="rrm-form-group">
                <label class="rrm-checkbox">
                    <input type="checkbox" name="terms" id="terms" required>
                    <span class="rrm-checkbox-text">
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                        <a href="#" target="_blank">Privacy Policy</a>
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="rrm-btn-primary rrm-btn-block" id="registerBtn">
                Create Account
                <svg class="rrm-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </form>

        <!-- Divider -->
        <div class="rrm-auth-divider">
            <span>Already have an account?</span>
        </div>

        <!-- Login Link -->
        <div class="rrm-auth-footer">
            <a href="<?= BASE_URL ?>/pages/auth/login.php" class="rrm-btn-outline rrm-btn-block">
                Sign In Instead
            </a>
        </div>
    </div>
</div>

<style>
/* ============================================
   Registration Page Styles - Dark Luxury Theme
   (unchanged from your original)
   ============================================ */

.rrm-auth-container {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 24px;
    background: linear-gradient(135deg, #0b1120 0%, #111827 100%);
}

.rrm-auth-card {
    max-width: 560px;
    width: 100%;
    background: #1c2840;
    border-radius: 28px;
    padding: 48px 40px;
    border: 1px solid rgba(212, 168, 66, 0.15);
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
    filter: drop-shadow(0 0 15px rgba(212, 168, 66, 0.2));
    transition: transform 0.3s ease;
}

.rrm-auth-logo svg:hover {
    transform: scale(1.05);
}

.rrm-auth-header h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 34px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.rrm-auth-header p {
    color: rgba(255, 255, 255, 0.5);
    font-size: 14px;
}

.rrm-auth-form {
    margin-bottom: 28px;
}

.rrm-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 0;
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

.rrm-required {
    color: #ef4444;
    margin-left: 2px;
}

.rrm-optional {
    color: rgba(255, 255, 255, 0.4);
    font-weight: normal;
    margin-left: 4px;
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
    pointer-events: none;
}

.rrm-input-wrapper input {
    width: 100%;
    padding: 14px 16px 14px 44px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 14px;
    font-size: 15px;
    color: #fff;
    transition: all 0.3s ease;
    font-family: 'DM Sans', sans-serif;
}

.rrm-input-wrapper input:focus {
    outline: none;
    border-color: #d4a842;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 3px rgba(212, 168, 66, 0.1);
}

.rrm-input-wrapper input::placeholder {
    color: rgba(255, 255, 255, 0.3);
}

.rrm-password-toggle {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
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

.rrm-password-hint {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.4);
    margin-top: 8px;
}

.rrm-strength-meter,
.rrm-match-message {
    font-size: 11px;
    margin-top: 6px;
    min-height: 18px;
}

.rrm-strength-meter .weak { color: #ef4444; }
.rrm-strength-meter .medium { color: #f59e0b; }
.rrm-strength-meter .strong { color: #10b981; }

.rrm-match-message .match { color: #10b981; }
.rrm-match-message .mismatch { color: #ef4444; }

.rrm-role-group {
    display: flex;
    gap: 16px;
}

.rrm-role-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px 16px;
    background: rgba(255, 255, 255, 0.03);
    border: 2px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.rrm-role-btn input {
    display: none;
}

.rrm-role-icon {
    font-size: 32px;
}

.rrm-role-title {
    font-size: 16px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.8);
}

.rrm-role-btn small {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.4);
}

.rrm-role-btn.active {
    border-color: #d4a842;
    background: rgba(212, 168, 66, 0.08);
}

.rrm-role-btn.active .rrm-role-title {
    color: #d4a842;
}

.rrm-role-btn:hover:not(.active) {
    border-color: rgba(212, 168, 66, 0.3);
    background: rgba(255, 255, 255, 0.05);
}

.rrm-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
}

.rrm-checkbox input {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    cursor: pointer;
    accent-color: #d4a842;
}

.rrm-checkbox-text {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.6);
    line-height: 1.5;
}

.rrm-checkbox-text a {
    color: #d4a842;
    text-decoration: none;
    font-weight: 500;
}

.rrm-checkbox-text a:hover {
    text-decoration: underline;
}

.rrm-alert {
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 28px;
    display: flex;
    gap: 14px;
}

.rrm-alert-error {
    background: rgba(239, 68, 68, 0.1);
    border-left: 3px solid #ef4444;
}

.rrm-alert-icon {
    font-size: 18px;
    flex-shrink: 0;
}

.rrm-alert-content strong {
    display: block;
    margin-bottom: 8px;
    color: #fca5a5;
    font-size: 13px;
}

.rrm-alert-content ul {
    margin: 0;
    padding-left: 20px;
    color: #fca5a5;
    font-size: 13px;
}

.rrm-alert-content li {
    margin: 4px 0;
}

.rrm-btn-primary {
    background: linear-gradient(135deg, #d4a842 0%, #b8882e 100%);
    color: #0b1120;
    border: none;
    padding: 14px 24px;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.rrm-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(212, 168, 66, 0.4);
}

.rrm-btn-primary:active {
    transform: translateY(0);
}

.rrm-btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.rrm-btn-icon {
    width: 18px;
    height: 18px;
    transition: transform 0.3s ease;
}

.rrm-btn-primary:hover .rrm-btn-icon {
    transform: translateX(4px);
}

.rrm-btn-outline {
    display: block;
    text-align: center;
    background: transparent;
    border: 1.5px solid rgba(212, 168, 66, 0.4);
    color: #d4a842;
    padding: 12px 24px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    width: 100%;
}

.rrm-btn-outline:hover {
    background: rgba(212, 168, 66, 0.1);
    border-color: #d4a842;
    transform: translateY(-2px);
}

.rrm-btn-block {
    width: 100%;
}

.rrm-auth-divider {
    text-align: center;
    margin: 28px 0 20px;
    position: relative;
}

.rrm-auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.08);
}

.rrm-auth-divider span {
    position: relative;
    background: #1c2840;
    padding: 0 16px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
}

.rrm-auth-footer {
    margin-top: 8px;
}

@media (max-width: 640px) {
    .rrm-auth-card {
        padding: 32px 24px;
    }
    
    .rrm-form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .rrm-role-group {
        flex-direction: column;
    }
    
    .rrm-auth-header h1 {
        font-size: 28px;
    }
}

@media (max-width: 480px) {
    .rrm-auth-container {
        padding: 40px 16px;
    }
    
    .rrm-auth-card {
        padding: 28px 20px;
    }
}
</style>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}

// Password strength checker
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const strengthMeter = document.getElementById('strengthMeter');
const matchMessage = document.getElementById('matchMessage');

function checkPasswordStrength(password) {
    if (!password) return '';
    
    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;
    
    if (score <= 1) return '<span class="weak">⚠️ Weak password</span>';
    if (score <= 3) return '<span class="medium">⚠️ Medium password</span>';
    return '<span class="strong">✓ Strong password</span>';
}

function checkPasswordMatch() {
    if (!confirmInput?.value) return '';
    return passwordInput?.value === confirmInput?.value 
        ? '<span class="match">✓ Passwords match</span>' 
        : '<span class="mismatch">✗ Passwords do not match</span>';
}

// Real-time password validation
if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        if (strengthMeter) {
            strengthMeter.innerHTML = checkPasswordStrength(this.value);
        }
        if (confirmInput?.value && matchMessage) {
            matchMessage.innerHTML = checkPasswordMatch();
        }
    });
}

if (confirmInput) {
    confirmInput.addEventListener('input', function() {
        if (matchMessage) {
            matchMessage.innerHTML = checkPasswordMatch();
        }
    });
}

// Role button styling
document.querySelectorAll('.rrm-role-btn').forEach(btn => {
    const radio = btn.querySelector('input[type="radio"]');
    if (radio) {
        radio.addEventListener('change', function() {
            if (this.checked) {
                document.querySelectorAll('.rrm-role-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
        });
    }
});

// Form submission validation
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
    const terms = document.getElementById('terms');
    if (terms && !terms.checked) {
        e.preventDefault();
        alert('Please agree to the Terms of Service and Privacy Policy.');
        return false;
    }
    
    const password = document.getElementById('password')?.value || '';
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long.');
        return false;
    }
    
    if (!/[A-Z]/.test(password)) {
        e.preventDefault();
        alert('Password must contain at least one uppercase letter.');
        return false;
    }
    
    if (!/[0-9]/.test(password)) {
        e.preventDefault();
        alert('Password must contain at least one number.');
        return false;
    }
    
    const confirm = document.getElementById('confirm_password')?.value || '';
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match.');
        return false;
    }
    
    // Show loading state
    const btn = document.getElementById('registerBtn');
    if (btn) {
        btn.textContent = 'Creating Account...';
        btn.disabled = true;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>