<?php
/**
 * 支払い時3Dセキュア - 支払い作成エンドポイント
 *
 * フロントエンドから送信されたトークンを使って支払いを作成します。
 * three_d_secure: true を指定することで、3Dセキュア認証が必要な支払いを作成します。
 */
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../libs/csrfToken.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
    exit;
}

session_start();

try {
    verifyCsrfToken();

    // フロントエンドで作成したトークンを取得します。
    $payjpToken = $_POST['payjp-token'] ?? '';
    if ($payjpToken === '') {
        throw new RuntimeException('トークンが取得できませんでした。');
    }

    // `PAYJP_SECRET_KEY` には `sk_` から始まる秘密鍵を設定してください。
    Payjp\Payjp::$apiKey = $_ENV['PAYJP_SECRET_KEY'] ?? '';

    // 支払いを作成します。
    // three_d_secure: true を指定すると、3Dセキュア認証が必要な状態で支払いが作成されます。
    // この時点では支払いは「保留」状態であり、まだ決済は完了していません。
    $charge = Payjp\Charge::create([
        'card' => $payjpToken,
        'amount' => 100,
        'currency' => 'jpy',
        'three_d_secure' => true,
    ]);

    // 支払いIDをフロントエンドに返します。
    // フロントエンドではこのIDを使って openThreeDSecureIframe() を呼び出します。
    echo json_encode([
        'charge_id' => $charge->id,
        'three_d_secure_status' => $charge->three_d_secure_status ?? null,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'message' => $e->getMessage(),
    ]);
}
