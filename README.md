# payjp-tds-sample

## 動作確認方法

[Docker](https://www.docker.com/ja-jp/) を前提にしています。
Docker がインストールされていない方はそちらの対応を行ってください。

1. ビルドしてください。

```shell
$ docker compose build
```

動作環境は PHP 8.3 です。

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

2. パッケージをインストールしてください。

Composer はコンテナの中に含まれています。

```shell
$ docker compose run --rm app composer install
```

3. サーバーを起動してください。

```shell
$ PAYJP_PUBLIC_KEY=お手持ちの公開鍵 PAYJP_SECRET_KEY=お手持ちの秘密鍵 docker compose up -d
```

http://localhost へアクセスしてください。
