<?php

header('Content-Type: text/plain; charset=UTF-8');

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbName = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

$dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO(
        $dsn,
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );

    $result = $pdo->query('SELECT 1 AS result')->fetch(PDO::FETCH_ASSOC);

    echo "Database connection successful.\n";
    echo "SELECT 1 result: {$result['result']}\n";
} catch (PDOException $e) {
    http_response_code(500);

    echo "Database connection failed.\n";

    error_log($e->getMessage());
}