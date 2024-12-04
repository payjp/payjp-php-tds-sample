# payjp-php-tds-sample

PHPで3Dセキュアを組み込んだ支払いのデモコードです。  
**あくまで動作を確認するためのサンプルであり、細かいエラーハンドリングなどは省略されていますのでご注意ください。**

## 動作確認方法

[Docker](https://www.docker.com/ja-jp/) を前提にしています。
Docker がインストールされていない方はそちらの対応を行ってください。

1. ビルドしてください。

```shell
$ docker compose build
```

2. パッケージをインストールしてください。

Composer はコンテナの中に含まれています。

```shell
$ docker compose run --rm app composer install
```

3. サーバーを起動してください。

```shell
$ PAYJP_PUBLIC_KEY=お手持ちの公開鍵 PAYJP_SECRET_KEY=お手持ちの秘密鍵 docker compose up -d
```

**設定する鍵はテスト用のものを利用することを強く推奨いたします。**

※環境変数については .env.example もあります。.env にコピーしてご利用ください。

4. http://localhost へアクセスしてください。

※ 80番ポートでサーバーが起動することを想定しています。  
ポートに関する起動エラーが出た場合は他の処理で80番ポートが使われていないか確認してください。


## 動作環境

PHP 8.3 です。下記コマンドにて確認できます。

```shell
$ docker compose run --rm app php -v
PHP 8.3.14 (cli) (built: Nov 21 2024 19:22:48) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.3.14, Copyright (c) Zend Technologies

$ docker compose run --rm app composer --version
Composer version 2.8.3 2024-11-17 13:13:04
PHP version 8.3.14 (/usr/local/bin/php)
Run the "diagnose" command to get more detailed diagnostics output.
```
