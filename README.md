勤怠管理アプリ

概要

Laravelを使用して作成した勤怠管理アプリです。

一般ユーザーは出勤・退勤・休憩時間の記録や、勤怠情報の確認、修正申請ができます。

管理者はスタッフの勤怠情報の確認や、修正申請の承認・否認ができます。

主な機能

ユーザー登録・ログイン

出勤・退勤の記録

休憩開始・終了の記録

勤怠一覧・詳細表示

勤怠修正申請

管理者による修正申請の承認・否認

スタッフの勤怠管理

勤怠レポート

勤怠API

使用技術

PHP

Laravel 10

MySQL 8

Docker / Docker Compose

HTML / CSS

Git / GitHub

環境構築

1. リポジトリをクローン

git clone git@github.com:yoko757/attendance-app.git
cd attendance-app
cp .env.example .env

.env のデータベース設定を以下のように変更します。

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail

docker run --rm -v "$(pwd):/app" -w /app composer:2 install --ignore-platform-reqs

2. Dockerコンテナを起動

docker compose up -d

3. Laravelの設定

./vendor/bin/sail artisan key:generate

4. マイグレーション

./vendor/bin/sail artisan migrate

テスト

PHPUnitを使用して、勤怠APIなどのテストを実行します。

./vendor/bin/sail artisan test

GitHub

https://github.com/yoko757/attendance-app
