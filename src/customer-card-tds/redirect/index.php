<?php
declare(strict_types=1);

require_once __DIR__ . '/../../libs/csrfToken.php';

session_start();
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8"/>
    <title>PAY.JP 顧客カードに対する3Dセキュア（リダイレクト型実装例）</title>
</head>
<body>
<form action="/customer-card-tds/redirect/redirect-to-tds-page.php" method="post">
    <p>顧客カードIDを入力してください。</p>
    <p><small>※ サンプルなのでテストモードにて作成したカードIDを利用することを強く推奨します。</small></p>

    <label><input type="text" name="customer_card_id" placeholder="顧客カードID"/></label>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>"/>
    <button type="submit">3Dセキュア認証開始</button>
</form>
<br/>
<a href="/">戻る</a>
</body>
</html>
