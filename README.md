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

Laravel 8

MySQL 8

Docker / Docker Compose

HTML / CSS

Git / GitHub

環境構築

1. リポジトリをクローン

git clone git@github.com:yoko757/attendance-app.git
cd attendance-app

2. Dockerコンテナを起動

docker compose up -d

3. Laravelの設定

cd src
cp .env.example .env
composer install
php artisan key:generate

4. データベースの設定

.env にMySQLの接続情報を設定します。

5. マイグレーション

php artisan migrate

テスト

PHPUnitを使用して、勤怠APIなどのテストを実行します。

php artisan test

GitHub

https://github.com/yoko757/attendance-app
