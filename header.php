<?php
// ── Global header ─────────────────────────────────────────────────────────────
// Include at the very top of every page.
// Pages can define $pageTitle and $pageDescription before including this.

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Make BASE_URL available if not defined in constants
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/rockland-rent-masters');
}

$pageTitle       = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'Find, rent and manage properties with ease in Rockland.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="<?= sanitise($pageDescription) ?>">
    <meta name="theme-color" content="#0b1120">
    <meta name="color-scheme" content="dark">
    
    <title><?= sanitise($pageTitle) ?> | <?= APP_NAME ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Preconnect for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com">

    <!-- Google Fonts - Cormorant Garamond + DM Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">

    <!-- Leaflet (maps) — only loads if page needs it -->
    <?php if (!empty($useMaps)): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <?php endif; ?>

    <!-- Page-specific extra head content -->
    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">

<?php require_once __DIR__ . '/navbar.php'; ?>

<main class="rrm-main">
    <div class="rrm-container rrm-main__inner">
        <?php require_once __DIR__ . '/alerts.php'; ?>