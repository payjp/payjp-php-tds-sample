<?php
declare(strict_types=1);

require_once __DIR__ . '/../../libs/csrfToken.php';

session_start();
$csrfToken = generateCsrfToken();
$apiKey = $_ENV['PAYJP_PUBLIC_KEY'];
if (!$apiKey) {
    echo 'PAYJP_PUBLIC_KEY環境変数がセットされていません。README.mdを参照し、pk_から始まる公開鍵をセットしてください。';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8"/>
    <title>PAY.JP 支払い時3Dセキュア実装サンプル（iframe型）</title>
    <style>
        .form-row {
            margin-bottom: 1rem;
        }
        .form-row label {
            display: block;
            margin-bottom: 0.25rem;
        }
        .form-row input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 300px;
        }
        .form-row small {
            display: block;
            color: #666;
        }
        #card-element {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            max-width: 300px;
        }
        #card-errors {
            color: red;
            margin-top: 0.5rem;
        }
        #result {
            margin-top: 1rem;
        }
        .status {
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
<h1>支払い時の3Dセキュア（iframe型）</h1>
<p class="note">トークン作成時ではなく、支払い作成後に3Dセキュア認証を行うサンプルです。</p>

<form id="payment-form">
    <p>おにぎり 100円</p>
    <div class="form-row">
        <div id="card-element"></div>
        <div id="card-errors" role="alert"></div>
    </div>
    <div class="form-row">
        <label>カード名義</label>
        <input type="text" name="cardholder_name" placeholder="TARO YAMADA" autocomplete="cc-name" />
    </div>
    <div class="form-row">
        <label>メールアドレス</label>
        <input type="email" name="email" placeholder="sample@example.com" autocomplete="email" />
    </div>
    <div class="form-row">
        <label>電話番号</label>
        <input type="tel" name="phone" placeholder="09012345678" autocomplete="tel" />
        <small>※ 日本の番号は自動でE.164形式に変換されます</small>
    </div>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
    <button id="submit-button" type="submit">支払いを開始</button>
</form>

<div id="result"></div>

<br />
<a href="/">戻る</a>

<script src="https://js.pay.jp/v2/pay.js"></script>
<script type="text/javascript">
    // payjp.js v2 を初期化し、カード入力フォームを作成します。
    const payjp = Payjp("<?php echo htmlspecialchars($apiKey); ?>");
    const elements = payjp.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');
    const cardErrors = document.getElementById('card-errors');
    const result = document.getElementById('result');

    function setBusy(isBusy) {
        submitButton.disabled = isBusy;
        submitButton.textContent = isBusy ? '処理中...' : '支払いを開始';
    }

    function showResult(html) {
        result.innerHTML = html;
    }

    function clearMessages() {
        cardErrors.textContent = '';
        showResult('');
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessages();
        setBusy(true);

        const formData = new FormData(form);
        const name = formData.get('cardholder_name');
        const email = formData.get('email');
        let phone = formData.get('phone');

        // 日本の電話番号をE.164形式に変換（例: 09012345678 → +819012345678）
        if (phone && phone.startsWith('0')) {
            phone = '+81' + phone.slice(1);
        }

        if (!email && !phone) {
            cardErrors.textContent = 'メールアドレスか電話番号のどちらかを入力してください。';
            setBusy(false);
            return;
        }

        try {
            // ========================================
            // Step 1: トークンを作成
            // ========================================
            // カード情報からトークンを作成します。
            // 3Dセキュアに必要な name, email, phone も一緒に送信します。
            const tokenResponse = await payjp.createToken(cardElement, {
                card: {
                    name: name || undefined,
                    email: email || undefined,
                    phone: phone || undefined,
                },
            });

            if (tokenResponse.error) {
                throw new Error(tokenResponse.error.message || 'トークン作成に失敗しました。');
            }

            // ========================================
            // Step 2: 支払いを作成（サーバーサイド）
            // ========================================
            // トークンをサーバーに送信し、three_d_secure: true で支払いを作成します。
            // この時点では支払いは保留状態です。
            const createParams = new URLSearchParams();
            createParams.append('payjp-token', tokenResponse.id);
            createParams.append('csrf_token', formData.get('csrf_token'));

            const createResponse = await fetch('/charge-tds/iframe/create-charge.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: createParams.toString(),
            });

            const createJson = await createResponse.json();
            if (!createResponse.ok) {
                throw new Error(createJson.message || '支払い作成に失敗しました。');
            }

            // ========================================
            // Step 3: 3Dセキュア認証（iframe）
            // ========================================
            // openThreeDSecureIframe() を呼び出すと、iframe で3Dセキュア認証画面が表示されます。
            // ユーザーが認証を完了すると Promise が解決されます。
            const chargeId = createJson.charge_id;
            let tdsErrorText = '';

            try {
                await payjp.openThreeDSecureIframe(chargeId);
            } catch (error) {
                // 認証がキャンセルされた場合や失敗した場合はここに来ます。
                tdsErrorText = error && error.message ? error.message : String(error);
            }

            // ========================================
            // Step 4: 支払いを確定（サーバーサイド）
            // ========================================
            // 3Dセキュア認証後、サーバーで tdsFinish() を呼び出して支払いを確定します。
            const finishParams = new URLSearchParams();
            finishParams.append('charge_id', chargeId);
            finishParams.append('csrf_token', formData.get('csrf_token'));
            if (tdsErrorText) {
                finishParams.append('tds_error', tdsErrorText);
            }

            const finishResponse = await fetch('/charge-tds/iframe/finish-charge.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: finishParams.toString(),
            });

            const finishJson = await finishResponse.json();
            if (!finishResponse.ok) {
                throw new Error(finishJson.message || '支払い確定に失敗しました。');
            }

            // 支払い完了
            showResult(
                '<p class="status">支払いが完了しました。</p>' +
                '<p>Charge ID: ' + finishJson.charge_id + '</p>' +
                '<p>金額: ' + finishJson.amount + '</p>'
            );
        } catch (error) {
            cardErrors.textContent = error && error.message ? error.message : '処理中にエラーが発生しました。';
        } finally {
            setBusy(false);
        }
    });
</script>
</body>
</html>
