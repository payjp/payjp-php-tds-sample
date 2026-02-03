<?php
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

    $payjpToken = $_POST['payjp-token'] ?? '';
    if ($payjpToken === '') {
        throw new RuntimeException('トークンが取得できませんでした。');
    }

    Payjp\Payjp::$apiKey = $_ENV['PAYJP_SECRET_KEY'] ?? '';

    $charge = Payjp\Charge::create([
        'card' => $payjpToken,
        'amount' => 100,
        'currency' => 'jpy',
        'three_d_secure' => true,
    ]);

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
