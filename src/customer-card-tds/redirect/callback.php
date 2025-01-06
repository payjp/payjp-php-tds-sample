<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../libs/csrfToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

session_start();

// セッションから保持していた顧客IDを再取得します。
$threeDSecureRequestId = $_SESSION['tds_input_data']['three_d_request_id'] ?? '';
unset($_SESSION['tds_input_data']);

// 3Dセキュア認証が完了した後に、3Dセキュアフローを完了させます。
Payjp\Payjp::$apiKey = $_ENV['PAYJP_SECRET_KEY'] ?? ''; // `PAYJP_SECRET_KEY` には `sk_` から始まる秘密鍵を設定してください。
$threeDSecureRequest = Payjp\ThreeDSecureRequest::retrieve($threeDSecureRequestId);

// この時点での `$threeDSecureRequest->three_d_secure_status` の状態を見て処理を判断します。

echo '顧客カードに対する3Dセキュア認証が完了しました。<br />';
echo $threeDSecureRequest->id . '<br />';
echo $threeDSecureRequest->resource_id . '<br />';
echo $threeDSecureRequest->state . '<br />';
echo $threeDSecureRequest->three_d_secure_status . '<br />';

echo '<a href="/">戻る</a>';
