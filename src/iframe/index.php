<?php
declare(strict_types=1);

require_once __DIR__ . '/../libs/csrfToken.php';

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
    <title>PAY.JP iframe型3Dセキュア実装サンプル</title>
</head>
<body>

<script type="text/javascript">
    function onCreatedToken(response) {
        console.log(response);
        document.querySelector('#created-token').textContent = response.id;
    }
</script>

<h1>支払いフォーム例</h1>

<form action="/iframe/create-charge.php" method="post">
    <p>おにぎり 100円</p>

    <div style="display: flex; gap: 1rem; align-items: center;">
        <div>
            <script
                type="text/javascript"
                src="https://checkout.pay.jp"
                class="payjp-button"
                data-payjp-key="<?php echo htmlspecialchars($apiKey); ?>"
                data-payjp-three-d-secure="true"
                data-payjp-three-d-secure-workflow="iframe"
                data-payjp-extra-attribute-email
                data-payjp-extra-attribute-phone
                data-payjp-partial="true"
                data-payjp-on-created="onCreatedToken"
            ></script>
        </div>
        <div>
            <small>※ <?php echo htmlspecialchars('<input type="hidden" name="payjp-token">'); ?> の value に token が自動的にセットされます。</small><br />
            <small>※ 生成されたトークン: <span id="created-token"></span></small>
        </div>
    </div>
    <br />
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
    <button type="submit">支払う</button>
</form>
<br />
<a href="/">戻る</a>
</body>
</html>

