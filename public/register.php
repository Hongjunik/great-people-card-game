<?php

header('Content-Type: application/json; charset=UTF-8');

// 1. POST 요청만 허용한다.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);

    echo json_encode(
        ['error' => 'POST 요청만 허용합낟.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// 2. 사용자가 보낸 값을 읽는다.
$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

// 3. 입력값을 검증한다.
if (
    !is_string($username) ||
    !preg_match('/\A[a-z0-9_]{3,30}\z/', $username) ||
    !is_string($password) ||
    strlen($password) < 12 ||
    strlen($password) > 72
) {
    http_response_code(400);

    echo json_encode(
        ['error' => '아이디 또는 비밀번호 형식이 올바르지 않습니다.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// 4. 비밀번호를 해시로 변환한다.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 5. MYSQL에 연결한다.
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
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // 6. 사용자 정보를 저장할 SQL을 준비한다.
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password_hash)
        VALUES (:username, :password_hash)'
    );

    // 7. SQL에 실제 값을 전달하고 실행한다.
    $stmt->execute([
        'username' => $username,
        'password_hash' => $passwordHash,
    ]);

    // 8. 새로 생성된 사용자 번호를 가져온다.
    $userId = (int) $pdo->lastInsertId();

    // 9. 회원가입 성공 결과를 반환한다.
    http_response_code(201);

    echo json_encode(
        [
            'message' => '회원가입이 완료되었습니다.',
            'user_id' => $userId,
        ],
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {

    // 이미 존재하는 아이디라면 중복 오류를 반환한다.
    if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
        http_response_code(409);

        echo json_encode(
            ['error' => '이미 사용 중인 아이디입니다.'],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    // 그 외의 DB 오류는 서버 로그에 기록한다.
    error_log($e->getMessge());

    http_response_code(500);

    echo json_encode(
        ['error' => '회원가입 처리 중 서버 오류가 발생했습니다.'],
        JSON_UNESCAPED_UNICODE
    );
}