<?php
declare(strict_types=1);

require_once __DIR__ . '/../libs/csrfToken.php';

session_start();
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8"/>
    <title>PAY.JP サブウィンドウ型3Dセキュア実装サンプル</title>
</head>
<body>

<script type="text/javascript">
    function onCreatedToken(response) {
        console.log(response);
        document.querySelector('#created-token').textContent = response.id;
        document.querySelector('#customer-email').value = response.card.email;
    }
</script>

<h1>お客様フォーム例</h1>

<form action="/sub-window/create-customer.php" method="post">
    <div style="display: grid; gap: 1rem; align-content: center;">
        <div>
            <label for="customer-name">お客様名 <input id="customer-name" type="text" name="customer_name"/></label>
        </div>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <div>
                <script
                    type="text/javascript"
                    src="https://checkout.pay.jp/prerelease"
                    data-payjp-key="<?php echo htmlspecialchars($_ENV['PAYJP_PUBLIC_KEY'] ?? ''); // `pk_` から始まる公開鍵を設定してください。 ?>"
                    data-payjp-three-d-secure="true"
                    data-payjp-three-d-secure-workflow="subwindow"
                    data-payjp-extra-attribute-email
                    data-payjp-extra-attribute-phone
                    data-payjp-partial="true"
                    data-payjp-on-created="onCreatedToken"
                ></script>
            </div>
            <div>
                <small>※ &lt;input type=&quot;hidden&quot; name=&quot;payjp-token&quot;&gt; の value に token が自動的にセットされます。</small><br />
                <small>※ 生成されたトークン: <span id="created-token"></span></small>
            </div>
        </div>
    </div>

    <br />

    <input id="customer-email" type="hidden" name="customer_email">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
    <button type="submit">登録</button>
</form>

</body>
</html>

