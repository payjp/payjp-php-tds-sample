<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../libs/csrfToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

session_start();

// $customerName, $customerEmail などの入力値のバリデーションなどを行ってください。
// このサンプルでは本題と関係ないため省略します。
$customerName = $_SESSION['tds_input_data']['customer_name'] ?? '';
$customerEmail = $_SESSION['tds_input_data']['customer_email'] ?? '';
$payjpToken = $_SESSION['tds_input_data']['payjp_token'] ?? '';
unset($_SESSION['tds_input_data']);

Payjp\Payjp::$apiKey = $_ENV['PAYJP_SECRET_KEY'] ?? ''; // `sk_` から始まる秘密鍵を設定してください。
// 3D セキュア認証が完了した後に、3Dセキュア認証フローを完了させます。
$token = Payjp\Token::retrieve($payjpToken);
// この時点での `$token->card->three_d_secure_status` の状態を見たりして処理を判断することもできます。
$token->tdsFinish(); // 忘れがちなので注意してください。
$customer = Payjp\Customer::create([
    'card' => $token->id,
    'email' => $customerEmail,
]);

// `$customerName` は DB に登録するとか。

echo $customer->id . '<br />';
echo $customer->email . '<br />';
echo $customer->default_card . '<br />';

echo '<a href="/">戻る</a>';
