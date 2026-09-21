<?php

header('Context-Type: application/json; charset=UTF-8');

$rarity = $_GET['rarity'] ?? null;

if ($rarity === null || $rarity === '') {
    http_response_code(400);

    echo json_encode(
        ['error' => 'rarity가 필요합니다.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

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
        id,
        card_code,
        card_name,
        person_name,
        era,
        rarity,
        theme,
        image_path
    FROM cards
    WHERE rarity = :rarity
        AND is_active = 1
    ORDER BY RAND()
    LIMIT 1
    '
);

$stmt->execute([
    'rarity' => $rarity,
]);

$card = $stmt->fetch();

if ($card === false) {
    http_response_code(404);

    echo json_encode(
        ['error' => '해당 등급에서 뽑을 수 있는 카드가 없습니다.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

echo json_encode(
    ['card' => $card],
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);