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

Payjp\Payjp::$apiKey = $_ENV['PAYJP_SECRET_KEY'] ?? ''; // `sk_` から始まる秘密鍵を設定してください。

$customer = Payjp\Customer::create([
    'card' => $payjpToken,
    'email' => $customerEmail,
]);

echo $customer->id . '<br />';
echo $customer->email . '<br />';
echo $customer->default_card . '<br />';

echo '<a href="/">戻る</a>';
