<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| SECURITY
|--------------------------------------------------------------------------
*/

function sanitise(?string $value): string
{
    return htmlspecialchars(
        trim((string)$value),
        ENT_QUOTES,
        'UTF-8'
    );
}

function e(?string $value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }

    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrfToken(): string
{
    return generateCsrfToken();
}

function validateCsrfToken(?string $token): bool
{
    return isset($_SESSION[CSRF_TOKEN_NAME])
        && hash_equals($_SESSION[CSRF_TOKEN_NAME], (string)$token);
}

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function userId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function userRole(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    static $user = null;

    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare("
        SELECT *
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([userId()]);

    $user = $stmt->fetch();

    return $user ?: null;
}

function hasRole(string|array $roles): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    $roles = (array)$roles;

    return in_array(
        userRole(),
        $roles,
        true
    );
}

function requireLogin(): void
{
    if (!isLoggedIn()) {

        $_SESSION['intended_url'] =
            $_SERVER['REQUEST_URI'] ?? '';

        header(
            'Location: ' .
            BASE_URL .
            '/pages/auth/login.php'
        );

        exit;
    }
}

function requireRole(string|array $roles): void
{
    requireLogin();

    if (!hasRole($roles)) {

        header(
            'Location: ' .
            BASE_URL .
            '/'
        );

        exit;
    }
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

function setFlashMessage(
    string $message,
    string $type = 'success'
): void {

    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlashMessage(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);

    return $flash;
}

/*
|--------------------------------------------------------------------------
| URLS
|--------------------------------------------------------------------------
*/

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function upload(string $path): string
{
    return UPLOAD_URL . '/' . ltrim($path, '/');
}

/*
|--------------------------------------------------------------------------
| FORMATTERS
|--------------------------------------------------------------------------
*/

function formatPrice(
    float|int|string $amount,
    string $currency = 'KES'
): string {

    return $currency . ' ' .
        number_format((float)$amount, 0);
}

function timeAgo(string $datetime): string
{
    $timestamp = strtotime($datetime);

    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'Just now';
    }

    if ($difference < 3600) {
        return floor($difference / 60) . 'm ago';
    }

    if ($difference < 86400) {
        return floor($difference / 3600) . 'h ago';
    }

    if ($difference < 604800) {
        return floor($difference / 86400) . 'd ago';
    }

    return date('d M Y', $timestamp);
}

function truncate(
    string $text,
    int $length = 120
): string {

    return mb_strlen($text) > $length
        ? mb_substr($text, 0, $length) . '...'
        : $text;
}

function starRating(float $rating): string
{
    $html = '';

    for ($i = 1; $i <= 5; $i++) {

        $html .= $i <= round($rating)
            ? '★'
            : '☆';
    }

    return $html;
}

/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/

function paginate(
    int $totalItems,
    int $page = 1,
    int $perPage = ITEMS_PER_PAGE
): array {

    $totalPages = max(
        1,
        (int)ceil($totalItems / $perPage)
    );

    $page = min(
        max(1, $page),
        $totalPages
    );

    return [
        'page' => $page,
        'perPage' => $perPage,
        'offset' => ($page - 1) * $perPage,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems
    ];
}

/*
|--------------------------------------------------------------------------
| JSON
|--------------------------------------------------------------------------
*/

function jsonResponse(
    bool $success,
    mixed $data = null,
    string $message = ''
): never {

    header('Content-Type: application/json');

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| FILE UPLOADS
|--------------------------------------------------------------------------
*/

function validateImage(array $file): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload failed.';
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return 'File exceeds upload limit.';
    }

    $mime = mime_content_type(
        $file['tmp_name']
    );

    if (
        !in_array(
            $mime,
            ALLOWED_IMAGE_MIME_TYPES,
            true
        )
    ) {
        return 'Invalid image type.';
    }

    return null;
}

function saveImage(
    array $file,
    string $folder = 'properties'
): string|false {

    $extension = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );

    $filename =
        uniqid('img_', true) .
        '.' .
        $extension;

    $directory =
        UPLOAD_PATH .
        '/' .
        trim($folder, '/');

    if (!is_dir($directory)) {
        mkdir(
            $directory,
            0775,
            true
        );
    }

    $destination =
        $directory .
        '/' .
        $filename;

    if (
        move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {
        return $folder . '/' . $filename;
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| PROPERTY HELPERS
|--------------------------------------------------------------------------
*/

function propertyBadge(array $property): string
{
    if (!empty($property['verified'])) {
        return 'Verified';
    }

    if (!empty($property['featured'])) {
        return 'Featured';
    }

    return '';
}

function incrementPropertyView(
    int $propertyId
): void {

    $stmt = db()->prepare("
        INSERT INTO property_views
        (
            property_id,
            user_id,
            ip_address
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $propertyId,
        userId(),
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

/*
|--------------------------------------------------------------------------
| ANALYTICS
|--------------------------------------------------------------------------
*/

function logEvent(
    string $eventType,
    ?int $propertyId = null,
    array $data = []
): void {

    $stmt = db()->prepare("
        INSERT INTO analytics_events
        (
            user_id,
            property_id,
            event_type,
            event_data
        )
        VALUES
        (
            ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        userId(),
        $propertyId,
        $eventType,
        json_encode($data)
    ]);
}
?>
