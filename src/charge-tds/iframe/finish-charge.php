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

    $chargeId = $_POST['charge_id'] ?? '';
    if ($chargeId === '') {
        throw new RuntimeException('支払いIDが取得できませんでした。');
    }

    Payjp\Payjp::$apiKey = $_ENV['PAYJP_SECRET_KEY'] ?? '';

    $charge = Payjp\Charge::retrieve($chargeId);
    $threeDSecureStatus = $charge->three_d_secure_status ?? '';

    if ($threeDSecureStatus === 'attempted' || $threeDSecureStatus === 'verified') {
        $charge = $charge->tdsFinish();

        echo json_encode([
            'charge_id' => $charge->id,
            'amount' => $charge->amount,
            'three_d_secure_status' => $charge->three_d_secure_status ?? null,
        ]);
        exit;
    }

    $tdsError = $_POST['tds_error'] ?? '';
    $message = '3Dセキュアが完了していないため支払いを確定できません。';
    if ($tdsError !== '') {
        $message .= ' (' . $tdsError . ')';
    }

    http_response_code(400);
    echo json_encode([
        'message' => $message,
        'three_d_secure_status' => $threeDSecureStatus,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'message' => $e->getMessage(),
    ]);
}
