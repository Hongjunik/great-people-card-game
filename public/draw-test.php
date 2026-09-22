<?php

header('Context-Type: application/json; charset=UTF-8');

// 테스트 단계에서는 카드 등급을 서버가 결정한다.
// 실제 카드팩 확률 및 행운 시스템은 아직 구현하지 않는다.
if (array_key_exists('rarity', $_GET)) {
    http_response_code(400);

    echo json_encode(
        ['error' => '카드 등급은 클라이언트가 지정할 수 없습니다.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$rarity = 'Common';

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