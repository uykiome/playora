<?php
declare(strict_types=1);

/**
 * Playora database connection.
 * Local XAMPP defaults: MySQL user `root`, empty password.
 * For hosting, set DB_HOST, DB_NAME, DB_USER and DB_PASS environment variables.
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'playora_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '';

$pdo = null;
$dbError = null;

try {
    $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    error_log('Playora database connection failed: ' . $exception->getMessage());
    $dbError = 'Дерекқорға қосылу мүмкін болмады. README.md файлындағы орнату қадамдарын орындаңыз.';
}
