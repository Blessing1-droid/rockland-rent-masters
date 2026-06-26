<?php
// ── Constants ────────────────────────────────────────────────────────────────

// App Configuration
define('APP_NAME', 'Rockland Rent Masters');
define('APP_VERSION', '1.1');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Role Constants
define('ROLE_RENTER', 'renter');
define('ROLE_LANDLORD', 'landlord');
define('ROLE_ADMIN', 'admin');

// Upload Limits
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5MB
define('MAX_UPLOAD_MB', 5);

// Allowed File Types
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Pagination
define('ITEMS_PER_PAGE', 12);
?>