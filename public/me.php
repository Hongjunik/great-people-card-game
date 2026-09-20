<?php

header('Content-Type: application/json; charset=UTF-8');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_name('gp_session');

session_set_cookie_params([
    'lifetime'=> 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    http_response_code(401);

    echo json_encode(
        ['error' => '로그인이 필요합니다.'],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

echo json_encode(
    [
        'user' => [
            'id' => (int) $_SESSION['user_id'],
            'username' => $_SESSION['username'],
        ],
    ],
    JSON_UNESCAPED_UNICODE
);