# 勤怠管理アプリ

## 環境構築
**Dockerビルド**
1. `git clone git@github.com:knyys/attendance-system.git`
2. `cd attendance-system`
3. DockerDesktopアプリを立ち上げる
4. `docker-compose up -d --build`

> *MySQLは、OSによって起動しない場合があるので、それぞれのPCに合わせてdocker-compose.ymlファイルを編集してください*  
  
**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルをコピーして.envファイルを作成
4. .envに以下の環境変数を追加
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
5. アプリケーションキーの作成
``` bash
php artisan key:generate
```
6. マイグレーションの実行
``` bash
php artisan migrate
```
7. シーディングの実行
``` bash
php artisan db:seed  
```
8. シンボリックリンク作成
``` bash
php artisan storage:link
```
  
**メール認証の設定(mailtrap)**  
[Mailtrap](https://mailtrap.io)にアクセスしてサインアップ  

- Integrationsをクリックして`Laravel 7.x and 8.x`
- Laravelの`.env`用の認証情報が表示されるのでコピーし`.env`ファイルに貼り付け
- Mailtrapの認証情報をコピーし`.env`ファイルに貼り付け

  ```vim
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.mailtrap.io
  MAIL_PORT=2525
  MAIL_USERNAME=生成されたUSERNAME  //mailtrapを貼り付け
  MAIL_PASSWORD=生成されたPASSWORD  //mailtrapを貼り付け
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS="hello@example.com"
  MAIL_FROM_NAME="${APP_NAME}"
  ```
- メールを送るとMailtrapのインボックス内に表示される



  > *Windowsではファイル権限エラーが発生しやすい  
    アクセスした際に、Permission deniedというエラーが発生した場合は  
  `sudo chmod -R 777 src/*`*

  
## テストアカウント
一般ユーザー
```vim
name: user
email: user@email.com
password: user1111
 ```
管理者
 ```vim
name: admin
email: admin@email.com
password: admin1111
 ```

## 使用技術(実行環境)
- PHP8.2.28
- Laravel8.83.29
- MySQL8.0.26

## ER図
![attendance-system](https://github.com/user-attachments/assets/409382a9-83e4-46d0-a320-67fb4441e92d)


## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
