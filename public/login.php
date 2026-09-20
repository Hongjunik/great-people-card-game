<?php

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);

    echo json_encode(
        ['error' => 'POST 요청만 허용합니다.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

if (
    !is_string($username) ||
    !is_string($password)
) {
    http_response_code(400);

    echo json_encode(
        ['error' => '요청 형식이 올바르지 않습니다.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset-utf8mb4',
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

    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash
        FROM users
        WHERE username = :username
        LIMIT 1'
    );

    $stmt->execute([
        'username' => $username,
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $user === false ||
        !password_verify($password, $user['password_hash'])
    ) {
        http_response_code(401);

        echo json_encode(
            ['error' => '아이디 또는 비밀번호가 올바르지 않습니다.'],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_name('gp_session');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    session_regenerate_id();

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];

    echo json_encode(
        [
            'message' => '로그인되었습니다.',
            'user' => [
                'id' => (int) $user['id'],
                'username' => $user['username'],
            ],
        ],
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode(
        ['error' => '로그인 처리 중 서버 오류가 발생했습니다.'],
        JSON_UNESCAPED_UNICODE
    );
}