<?php

declare(strict_types=1);

// 개발용 명령줄 테스트에서만 실행한다.
// HTTP 요청으로는 카드를 지급할 수 없다.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

$username = 'player01';
$cardCode = 'CARD_TEST_002';

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

    // 사용자 이름과 카드 코드로 실제 DB 식별자를 찾는다.
    $stmt = $pdo->prepare(
        '
        SELECT
            u.id AS user_id,
            c.id AS card_id
        FROM users AS u
        CROSS JOIN cards AS c
        WHERE u.username = :username
            AND c.card_code = :card_code
            AND c.is_active = 1
        LIMIT 1
        '
    );

    $stmt->execute([
        'username' => $username,
        'card_code' => $cardCode,
    ]);

    $target = $stmt->fetch();

    if ($target === false) {
        echo "사용자 또는 지급 가능한 카드를 찾지 못했습니다.\n";
        exit(1);
    }

    // 해당 사용자에게 카드 종류 한 장을 등록한다.
    $insert = $pdo->prepare(
        '
        INSERT INTO user_cards (user_id, card_id)
        VALUES (:user_id, :card_id)
        '
    );

    try {
        $insert->execute([
            'user_id' => $target['user_id'],
            'card_id' => $target['card_id'],
        ]);

        echo "새 카드 지급 완료: {$cardCode}\n";

    } catch (PDOException $e) {
        // 같은 사용자 + 카드 종류의 UNIQUE 제약 위반
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            echo "이미 보유한 카드입니다: {$cardCode}\n";
            echo "중복 카드의 강화 재료 지급은 아직 구현하지 않았습니다.\n";
            exit;
        }

        throw $e;
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "카드 지급 처리 중 DB 오류가 발생했습니다.\n";
    exit(1);
}