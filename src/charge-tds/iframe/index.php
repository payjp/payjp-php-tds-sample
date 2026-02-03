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
        body {
            font-family: sans-serif;
        }
        .form-row {
            margin-bottom: 1rem;
        }
        #card-element {
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            max-width: 420px;
        }
        #card-errors {
            color: #b91c1c;
            margin-top: 0.5rem;
        }
        #result {
            margin-top: 1.5rem;
        }
        .note {
            color: #475569;
        }
        .status {
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1>支払い時の3Dセキュア（iframe型）</h1>
<p class="note">トークン作成時ではなく、支払い作成後に3Dセキュア認証を行うサンプルです。</p>

<form id="payment-form">
    <div class="form-row">
        <label>
            カード名義
            <input type="text" name="cardholder_name" placeholder="TARO YAMADA" autocomplete="cc-name" />
        </label>
    </div>
    <div class="form-row">
        <label>
            メールアドレス
            <input type="email" name="email" placeholder="sample@example.com" autocomplete="email" />
        </label>
    </div>
    <div class="form-row">
        <label>
            電話番号
            <input type="tel" name="phone" placeholder="09012345678" autocomplete="tel" />
            <small style="color: #64748b;">※ 日本の番号は自動でE.164形式に変換されます</small>
        </label>
    </div>
    <div class="form-row">
        <p>おにぎり 100円</p>
        <div id="card-element"></div>
        <div id="card-errors" role="alert"></div>
    </div>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
    <button id="submit-button" type="submit">支払いを開始</button>
</form>

<div id="result"></div>

<br />
<a href="/">戻る</a>

<script src="https://js.pay.jp/v2/pay.js"></script>
<script type="text/javascript">
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

            const chargeId = createJson.charge_id;
            let tdsErrorText = '';

            try {
                await payjp.openThreeDSecureIframe(chargeId);
            } catch (error) {
                tdsErrorText = error && error.message ? error.message : String(error);
            }

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
