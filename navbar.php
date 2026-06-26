<?php
// ── Navbar ────────────────────────────────────────────────────────────────────
$user = currentUser();
$role = $user['role'] ?? null;
?>

<nav class="rrm-navbar" id="navbar">
    <div class="rrm-container rrm-navbar__inner">
        <div class="rrm-navbar__brand">
            <a href="<?= BASE_URL ?>" class="rrm-logo">
                <svg class="rrm-logo__icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="10" fill="#d4a842"/>
                    <path d="M20 10L10 18V30H30V18L20 10Z" stroke="#0b1120" stroke-width="1.5" fill="none"/>
                    <path d="M17 22H23V28H17V22Z" fill="#0b1120" opacity="0.8"/>
                    <circle cx="20" cy="16" r="2" fill="#0b1120"/>
                </svg>
                <div class="rrm-logo__text">
                    <span class="rrm-logo__title">Rockland Rent</span>
                    <span class="rrm-logo__subtitle">MASTERS</span>
                </div>
            </a>
        </div>

        <button class="rrm-navbar__toggle" id="navToggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <ul class="rrm-navbar__links" id="navLinks">
            <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
            <li><a href="<?= BASE_URL ?>/pages/renter/search.php">Browse</a></li>

            <?php if (!$user): ?>
                <li><a href="<?= BASE_URL ?>/pages/auth/login.php" class="rrm-btn-outline-nav">Login</a></li>
                <li><a href="<?= BASE_URL ?>/pages/auth/register.php" class="rrm-btn-primary-nav">Register</a></li>

            <?php elseif ($role === ROLE_RENTER): ?>
                <li><a href="<?= BASE_URL ?>/pages/renter/saved.php">Saved</a></li>
                <li><a href="<?= BASE_URL ?>/pages/renter/compare.php">Compare</a></li>
                <li><a href="<?= BASE_URL ?>/pages/renter/applications.php">Applications</a></li>
                <li><a href="<?= BASE_URL ?>/pages/renter/appointment.php">Viewings</a></li>
                
                <li class="rrm-notification-dropdown">
                    <button class="rrm-notification-btn" aria-label="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="rrm-notification-badge" id="notificationBadge" style="display:none">0</span>
                    </button>
                    <div class="rrm-notification-menu">
                        <div class="rrm-notification-header">
                            <strong>Notifications</strong>
                            <button class="rrm-mark-read">Mark all read</button>
                        </div>
                        <div class="rrm-notification-list">
                            <div class="rrm-notification-item">✨ Welcome to Rockland Rent Masters!</div>
                        </div>
                    </div>
                </li>
                
                <li class="rrm-dropdown">
                    <button class="rrm-dropdown__trigger">
                        <?php if ($user['avatar']): ?>
                            <img src="<?= BASE_URL . $user['avatar'] ?>" alt="avatar" class="rrm-avatar">
                        <?php else: ?>
                            <span class="rrm-avatar-placeholder"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <span class="rrm-dropdown__name"><?= sanitise($user['name']) ?></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <ul class="rrm-dropdown__menu">
                        <li><a href="<?= BASE_URL ?>/pages/dashboard.php">📊 Dashboard</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/renter/profile.php">👤 Profile</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/renter/settings.php">⚙️ Settings</a></li>
                        <li><hr class="rrm-dropdown-divider"></li>
                        <li><a href="<?= BASE_URL ?>/pages/auth/logout.php">🚪 Logout</a></li>
                    </ul>
                </li>

            <?php elseif ($role === ROLE_LANDLORD): ?>
                <li><a href="<?= BASE_URL ?>/pages/landlord/listings.php">Listings</a></li>
                <li><a href="<?= BASE_URL ?>/pages/landlord/applications.php">Applications</a></li>
                <li><a href="<?= BASE_URL ?>/pages/landlord/tenants.php">Tenants</a></li>
                <li><a href="<?= BASE_URL ?>/pages/landlord/analytics.php">Analytics</a></li>
                
                <li class="rrm-dropdown">
                    <button class="rrm-dropdown__trigger">
                        <?php if ($user['avatar']): ?>
                            <img src="<?= BASE_URL . $user['avatar'] ?>" alt="avatar" class="rrm-avatar">
                        <?php else: ?>
                            <span class="rrm-avatar-placeholder"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <span class="rrm-dropdown__name"><?= sanitise($user['name']) ?></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <ul class="rrm-dropdown__menu">
                        <li><a href="<?= BASE_URL ?>/pages/dashboard.php">📊 Dashboard</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/landlord/profile.php">👤 Profile</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/landlord/add-property.php">➕ Add Listing</a></li>
                        <li><hr class="rrm-dropdown-divider"></li>
                        <li><a href="<?= BASE_URL ?>/pages/auth/logout.php">🚪 Logout</a></li>
                    </ul>
                </li>

            <?php elseif ($role === ROLE_ADMIN): ?>
                <li><a href="<?= BASE_URL ?>/pages/admin/users.php">Users</a></li>
                <li><a href="<?= BASE_URL ?>/pages/admin/listings.php">Listings</a></li>
                <li><a href="<?= BASE_URL ?>/pages/admin/analytics.php">Analytics</a></li>
                <li><a href="<?= BASE_URL ?>/pages/auth/logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<style>
/* ============================================
   RRM Navbar - Dark Luxury Theme
   ============================================ */

.rrm-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding: 0;
    transition: all 0.3s ease;
}

.rrm-navbar.scrolled {
    background: rgba(11, 17, 32, 0.95);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(212, 168, 66, 0.15);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.rrm-navbar__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
}

/* Logo */
.rrm-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.rrm-logo__icon {
    width: 40px;
    height: 40px;
    transition: transform 0.3s ease;
}

.rrm-logo:hover .rrm-logo__icon {
    transform: scale(1.05);
}

.rrm-logo__text {
    display: flex;
    flex-direction: column;
}

.rrm-logo__title {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -0.3px;
    line-height: 1.2;
}

.rrm-logo__subtitle {
    font-size: 10px;
    font-weight: 600;
    color: #d4a842;
    letter-spacing: 2px;
}

/* Navigation Links */
.rrm-navbar__links {
    display: flex;
    align-items: center;
    gap: 28px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.rrm-navbar__links li {
    list-style: none;
}

.rrm-navbar__links a {
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
}

.rrm-navbar__links a:hover {
    color: #d4a842;
}

.rrm-navbar__links a::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 0;
    width: 0;
    height: 2px;
    background: #d4a842;
    transition: width 0.3s ease;
}

.rrm-navbar__links a:hover::after {
    width: 100%;
}

/* Buttons in Nav */
.rrm-btn-outline-nav {
    padding: 8px 20px !important;
    border: 1.5px solid rgba(212, 168, 66, 0.5);
    border-radius: 40px;
    color: #d4a842 !important;
}

.rrm-btn-outline-nav:hover {
    background: rgba(212, 168, 66, 0.1);
    border-color: #d4a842;
}

.rrm-btn-outline-nav::after {
    display: none !important;
}

.rrm-btn-primary-nav {
    padding: 8px 24px !important;
    background: linear-gradient(135deg, #d4a842 0%, #b8882e 100%);
    border-radius: 40px;
    color: #0b1120 !important;
    font-weight: 600;
}

.rrm-btn-primary-nav:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(212, 168, 66, 0.3);
}

.rrm-btn-primary-nav::after {
    display: none !important;
}

/* Notification Dropdown */
.rrm-notification-dropdown {
    position: relative;
}

.rrm-notification-btn {
    background: none;
    border: none;
    cursor: pointer;
    position: relative;
    padding: 6px;
    color: rgba(255, 255, 255, 0.85);
    transition: color 0.2s;
}

.rrm-notification-btn:hover {
    color: #d4a842;
}

.rrm-notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 20px;
    min-width: 18px;
    text-align: center;
}

.rrm-notification-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 320px;
    background: #1c2840;
    border: 1px solid rgba(212, 168, 66, 0.2);
    border-radius: 16px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
}

.rrm-notification-dropdown:hover .rrm-notification-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(10px);
}

.rrm-notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.rrm-mark-read {
    background: none;
    border: none;
    color: #d4a842;
    font-size: 12px;
    cursor: pointer;
}

.rrm-notification-list {
    max-height: 320px;
    overflow-y: auto;
}

.rrm-notification-item {
    padding: 14px 20px;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.75);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    transition: background 0.2s;
}

.rrm-notification-item:hover {
    background: rgba(212, 168, 66, 0.1);
}

/* User Dropdown */
.rrm-dropdown {
    position: relative;
}

.rrm-dropdown__trigger {
    display: flex;
    align-items: center;
    gap: 10px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: rgba(255, 255, 255, 0.85);
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s;
}

.rrm-dropdown__trigger:hover {
    color: #d4a842;
}

.rrm-dropdown__name {
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.rrm-dropdown__menu {
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 200px;
    background: #1c2840;
    border: 1px solid rgba(212, 168, 66, 0.2);
    border-radius: 12px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
    list-style: none;
    padding: 8px 0;
    margin: 0;
}

.rrm-dropdown:hover .rrm-dropdown__menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(10px);
}

.rrm-dropdown__menu li {
    margin: 0;
}

.rrm-dropdown__menu a {
    display: block;
    padding: 10px 20px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
}

.rrm-dropdown__menu a:hover {
    background: rgba(212, 168, 66, 0.1);
    color: #d4a842;
}

.rrm-dropdown__menu a::after {
    display: none;
}

.rrm-dropdown-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.08);
    margin: 8px 0;
    border: none;
}

/* Avatar */
.rrm-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #d4a842;
}

.rrm-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #d4a842, #b8882e);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    color: #0b1120;
}

/* Mobile Toggle */
.rrm-navbar__toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
}

.rrm-navbar__toggle span {
    width: 24px;
    height: 2px;
    background: #d4a842;
    transition: all 0.3s ease;
}

.rrm-navbar__toggle.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.rrm-navbar__toggle.active span:nth-child(2) {
    opacity: 0;
}

.rrm-navbar__toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -5px);
}

/* Responsive */
@media (max-width: 992px) {
    .rrm-navbar__links {
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .rrm-navbar__toggle {
        display: flex;
    }
    
    .rrm-navbar__links {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        flex-direction: column;
        align-items: stretch;
        background: #0b1120;
        border-bottom: 1px solid rgba(212, 168, 66, 0.2);
        padding: 24px;
        gap: 16px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 999;
    }
    
    .rrm-navbar__links.active {
        transform: translateX(0);
    }
    
    .rrm-navbar__links li {
        width: 100%;
    }
    
    .rrm-navbar__links a {
        display: block;
        padding: 8px 0;
    }
    
    .rrm-btn-outline-nav,
    .rrm-btn-primary-nav {
        text-align: center;
        width: 100%;
    }
    
    .rrm-dropdown__menu {
        position: static;
        box-shadow: none;
        padding-left: 20px;
        background: transparent;
        transform: none;
        opacity: 1;
        visibility: visible;
        display: none;
    }
    
    .rrm-dropdown.active .rrm-dropdown__menu {
        display: block;
    }
    
    .rrm-notification-menu {
        position: fixed;
        top: 70px;
        left: 16px;
        right: 16px;
        width: auto;
    }
}

@media (max-width: 480px) {
    .rrm-logo__text {
        display: none;
    }
}
</style>

<script>
// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Mobile menu toggle
const toggleBtn = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');

if (toggleBtn && navLinks) {
    toggleBtn.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        toggleBtn.classList.toggle('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navLinks.contains(e.target) && !toggleBtn.contains(e.target)) {
            navLinks.classList.remove('active');
            toggleBtn.classList.remove('active');
        }
    });
}

// Mobile dropdown toggles
if (window.innerWidth <= 768) {
    const dropdowns = document.querySelectorAll('.rrm-dropdown');
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.rrm-dropdown__trigger');
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.classList.toggle('active');
        });
    });
}
</script>