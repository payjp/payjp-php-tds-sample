<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../libs/csrfToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

session_start();

// セッションから保持していたトークンを再取得します。
$payjpToken = $_SESSION['tds_input_data']['payjp_token'] ?? '';
unset($_SESSION['tds_input_data']);

// 3D セキュア認証が完了した後に、3Dセキュアフローを完了させます。
Payjp\Payjp::$apiKey = $_ENV['PAYJP_SECRET_KEY'] ?? ''; // `PAYJP_SECRET_KEY` には `sk_` から始まる秘密鍵を設定してください。
$token = Payjp\Token::retrieve($payjpToken);

// この時点での `$token->card->three_d_secure_status` の状態を見たりして処理を判断することもできます。

// 3Dセキュアフロー完了します。忘れがちなので注意してください。
$token->tdsFinish();

// 3Dセキュアフローが完了したトークンを用いて、支払いを行います。
$charge = Payjp\Charge::create([
    'card' => $token->id,
    'amount' => 100,
    'currency' => 'jpy',
]);

// 支払い後に必要な処理を行ってください。

echo '支払いが完了しました。<br />';
echo $charge->id . '<br />';
echo $charge->amount . '<br />';

echo '<a href="/">戻る</a>';
