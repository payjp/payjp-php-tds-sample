<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../libs/csrfToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// csrf token の検証などを行ってください。このサンプルでは本題と関係ないため省略します。
session_start();
verifyCsrfToken();

// $customerName, $customerEmail などの入力値のバリデーションなどを行ってください。
// このサンプルでは本題と関係ないため省略します。
$customerName = $_POST['customer_name'] ?? '';
$customerEmail = $_POST['customer_email'] ?? '';
$payjpToken = $_POST['payjp-token'] ?? '';

// 入力内容をセッションに保存しておきます。
$_SESSION['tds_input_data'] = [
    'customer_name' => $customerName,
    'customer_email' => $customerEmail,
    'payjp_token' => $payjpToken,
];

// 戻り先 URL ( `back_url` ) を設定します。ここでは firebase/php-jwt を使用しています。
// https://pay.jp/docs/api/#3d%E3%82%BB%E3%82%AD%E3%83%A5%E3%82%A2%E9%96%8B%E5%A7%8B
$jws = Firebase\JWT\JWT::encode(
    [
        'url' => 'http://localhost/redirect/callback.php',
    ],
    $_ENV['PAYJP_SECRET_KEY'] ?? '',
    'HS256'
);

header("Location: https://api.pay.jp/v1/tds/$payjpToken/start?publickey={$_ENV['PAYJP_PUBLIC_KEY']}&back_url=$jws");
exit;
