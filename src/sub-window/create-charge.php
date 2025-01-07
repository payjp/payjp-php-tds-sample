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

// トークンを取得します。
$payjpToken = $_POST['payjp-token'] ?? '';

// トークンを使って支払いを行います。
Payjp\Payjp::$apiKey = $_ENV['PAYJP_SECRET_KEY'] ?? ''; // `PAYJP_SECRET_KEY` には `sk_` から始まる秘密鍵を設定してください。
$charge = Payjp\Charge::create([
    'card' => $payjpToken,
    'amount' => 100,
    'currency' => 'jpy',
]);

// 支払い後に必要な処理を行ってください。

echo '支払いが完了しました。<br />';
echo $charge->id . '<br />';
echo $charge->amount . '<br />';

echo '<a href="/">戻る</a>';
