<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

/*
 * 기존 login.php 및 me.php와 동일한 세션 이름을 사용한다
 * 현재 프로젝트의 세션 이름: gp_session
 */
session_name('gp_session');
session_start();

$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($userId <= 0) {
    http_response_code(401);

    echo json_encode(
        ['error' => '로그인이 필요합니다.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST'),
        getenv('DB_PORT'),
        getenv('DB_NAME')
    );

    $pdo = new PDO(
        $dsn,
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->prepare(
        '
        SELECT
            uc.id AS user_card_id,
            c.id AS card_id,
            c.card_code,
            c.card_name,
            c.person_name,
            c.rarity,
            c.image_path,
            uc.acquired_at
        FROM user_cards AS uc
        INNER JOIN cards AS c
            ON c.id = uc.card_id
        WHERE uc.user_id = :user_id
        ORDER BY uc.id ASC
        '
    );

    $stmt->execute([
        'user_id' => $userId,
    ]);

    $cards = $stmt->fetchAll();

    echo json_encode(
        ['cards' => $cards],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode(
        ['error' => '카드 목록을 조회할 수 없습니다.'],
        JSON_UNESCAPED_UNICODE
    );
}