<?php

declare(strict_types=1);

require_once __DIR__ . '/constants.php';

$dbConfig = [
    'host'      => 'localhost',
    'dbname'    => 'rockland_db',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4'
];

try {

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $dbConfig['host'],
        $dbConfig['dbname'],
        $dbConfig['charset']
    );

    $pdo = new PDO(
        $dsn,
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false
        ]
    );

} catch (PDOException $e) {

    error_log(
        '[DATABASE ERROR] ' .
        $e->getMessage()
    );

    http_response_code(500);

    if (
        isset($_SERVER['HTTP_ACCEPT']) &&
        str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')
    ) {

        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed.'
        ]);

    } else {

        echo '
        <div style="
            font-family: Arial,sans-serif;
            padding:40px;
            text-align:center;
        ">
            <h2>Database Connection Failed</h2>
            <p>Please check your database configuration.</p>
        </div>
        ';
    }

    exit;
}

/**
 * ==================================================
 * DATABASE HELPERS
 * ==================================================
 */

function db(): PDO
{
    global $pdo;
    return $pdo;
}

function beginTransaction(): bool
{
    return db()->beginTransaction();
}

function commitTransaction(): bool
{
    return db()->commit();
}

function rollbackTransaction(): bool
{
    return db()->rollBack();
}

function lastInsertId(): string
{
    return db()->lastInsertId();
}

function databaseConnected(): bool
{
    try {
        db()->query('SELECT 1');
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
