<?php
// ── Auth helpers ──────────────────────────────────────────────────────────────
// Always included via header.php. Call requireRole() at the top of any protected page.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Is the user logged in? ────────────────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// ── Return current user array from session ────────────────────────────────────
function currentUser(): array|null {
    if (!isLoggedIn()) return null;
    return [
        'id'     => $_SESSION['user_id'],
        'name'   => $_SESSION['user_name'],
        'email'  => $_SESSION['user_email'],
        'role'   => $_SESSION['user_role'],
        'avatar' => $_SESSION['user_avatar'] ?? null,
    ];
}

// ── Enforce role — redirects if wrong or not logged in ────────────────────────
function requireRole(string $role): void {
    if (!isLoggedIn()) {
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header("Location: " . BASE_URL . "/pages/auth/login.php?redirect=$redirect");
        exit;
    }
    if ($_SESSION['user_role'] !== $role) {
        header("Location: " . BASE_URL . "/pages/auth/login.php");
        exit;
    }
}

// ── Set session after successful login ────────────────────────────────────────
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_name']   = $user['name'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_role']   = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? null;
}

// ── Redirect by role after login ──────────────────────────────────────────────
function redirectByRole(): void {
    $role = $_SESSION['user_role'] ?? '';
    $map  = [
        'renter'   => BASE_URL . '/pages/renter/dashboard.php',
        'landlord' => BASE_URL . '/pages/landlord/dashboard.php',
        'admin'    => BASE_URL . '/pages/admin/dashboard.php',
    ];
    header('Location: ' . ($map[$role] ?? BASE_URL));
    exit;
}

// ── Flash messages ────────────────────────────────────────────────────────────
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): array|null {
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}