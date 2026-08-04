# マルチ環境デプロイメントガイド

このアプリケーションは **ローカル環境（localhost）** と **本番環境（https://teito.link/task/）** の両方で同じコードセットを使用できるように設計されています。

## 環境構成

### 自動環境判定

ホスト名に基づいて自動的に環境が判定されます：

| 環境 | ホスト名 | ベースURL | エラー表示 |
|------|---------|----------|----------|
| **ローカル** | localhost / 127.0.0.1 | http://localhost/task/ | 有効 |
| **本番** | teito.link | https://teito.link/task/ | 無効 |

### 環境判定ロジック

#### PHP側（`config.php`）
```php
define('ENVIRONMENT', get_environment());  // 'production' or 'local'
define('BASE_URL', get_base_url());        // 自動判定されたベースURL
```

#### JavaScript側（`index.html`）
```javascript
window.ENV.isProduction()    // true/false
window.ENV.isLocal()         // true/false
window.ENV.getBaseUrl()      // ベースURLを取得
window.ENV.getEnvironment()  // 環境名を取得
```

#### PHP関数（`includes/functions.php`）
```php
get_base_url()    // ベースURLを取得
get_environment() // 環境を取得
is_production()   // 本番環境かどうか
is_local()        // ローカル環境かどうか
```

## ローカル環境セットアップ

### 既存のセットアップ（変更なし）

```bash
# XAMPP を起動
c:\xampp\apache_start.bat

# ブラウザで開く
http://localhost/task/

# 管理画面
http://localhost/task/admin/
```

### デフォルトログイン

- **ユーザー名**: admin
- **パスワード**: admin123
- ⚠️ **本番環境デプロイ時は必ず変更してください**

## 本番環境デプロイメント

### 前提条件

- Apache 2.4+ / PHP 8.0+
- SSL/HTTPS対応
- `teito.link` ドメイン設定済み

### デプロイ手順

#### 1. ファイルをサーバーにアップロード

```bash
# すべてのファイルを /var/www/html/teito.link/task/ にアップロード
scp -r /path/to/task user@teito.link:/var/www/html/teito.link/task/
```

#### 2. パーミッション設定

```bash
ssh user@teito.link

# ディレクトリのパーミッション
chmod 755 /var/www/html/teito.link/task
chmod 755 /var/www/html/teito.link/task/data
chmod 755 /var/www/html/teito.link/task/uploads

# ファイルのパーミッション
chmod 644 /var/www/html/teito.link/task/*.php
chmod 644 /var/www/html/teito.link/task/.htaccess

# 書き込み権限が必要なディレクトリ
chmod 755 /var/www/html/teito.link/task/data
chmod 755 /var/www/html/teito.link/task/uploads

# ファイルの所有者を設定（Apacheユーザーに）
sudo chown -R www-data:www-data /var/www/html/teito.link/task/data
sudo chown -R www-data:www-data /var/www/html/teito.link/task/uploads

# お知らせデータ（初回のみ作成）
if [ ! -f /var/www/html/teito.link/task/data/notices.json ]; then
    echo [] | sudo tee /var/www/html/teito.link/task/data/notices.json >/dev/null
fi
sudo chown www-data:www-data /var/www/html/teito.link/task/data/notices.json
sudo chmod 664 /var/www/html/teito.link/task/data/notices.json
```

#### 3. config.php 内のパスワード変更

```php
// config.php
define('ADMIN_PASSWORD', password_hash('【新しいパスワード】', PASSWORD_BCRYPT));
```

#### 4. Apache VirtualHost 設定

```apache
<VirtualHost *:443>
    ServerName teito.link
    ServerAlias www.teito.link
    DocumentRoot /var/www/html/teito.link
    
    # SSL 設定
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/teito.link.crt
    SSLCertificateKeyFile /etc/ssl/private/teito.link.key
    
    # PHP-FPM または mod_php の設定
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost/"
    </FilesMatch>
    
    # ドキュメントルート
    <Directory /var/www/html/teito.link>
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
    
    # ログ
    ErrorLog ${APACHE_LOG_DIR}/teito.link-error.log
    CustomLog ${APACHE_LOG_DIR}/teito.link-access.log combined
</VirtualHost>

# HTTP → HTTPS リダイレクト
<VirtualHost *:80>
    ServerName teito.link
    ServerAlias www.teito.link
    Redirect permanent / https://teito.link/
</VirtualHost>
```

#### 5. Apache設定を有効化

```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
sudo systemctl restart apache2
```

#### 6. .htaccess 設定の確認

本番環境用に、以下の行をアンコメントして有効化してください：

```apache
# .htaccess
# SSL/HTTPS を強制（本番環境のみ）
<IfModule mod_rewrite.c>
    RewriteCond %{SERVER_PORT} !^443$
    RewriteCond %{HTTP_HOST} ^teito\.link$ [NC]
    RewriteRule ^(.*)$ https://teito.link/task/$1 [R=301,L]
</IfModule>
```

#### 7. PHP設定の最適化（本番環境）

`php.ini` を編集：

```ini
; エラー表示を無効化
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; セッションセキュリティ
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = "Lax"

; アップロード制限
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M

; タイムゾーン
date.timezone = "Asia/Tokyo"
```

#### 8. 本番環境でのテスト

```bash
# ブラウザでアクセス
https://teito.link/task/

# 管理画面にログイン
https://teito.link/task/admin/

# 動作確認
- 環境が "production" と表示されることを確認
- すべてのリンクが HTTPS で動作することを確認
- 管理画面でお知らせを作成→トップで集約モーダルに表示されること
- 「今後表示しない」を有効にすると再訪時に出ないこと（localStorage）
```

## 環境別設定

### ローカル環境

```php
// config.php で自動設定
ENVIRONMENT = 'local'
BASE_URL = 'http://localhost/task/'
```

**特性：**
- PHPエラー表示：有効
- SQLエラー表示：有効
- デバッグログ：詳細

### 本番環境

```php
// config.php で自動設定
ENVIRONMENT = 'production'
BASE_URL = 'https://teito.link/task/'
```

**特性：**
- PHPエラー表示：無効（ログファイルに記録）
- SQLエラー表示：無効
- デバッグログ：制限
- HTTPS 強制
- キャッシュ有効化

## セキュリティチェックリスト

本番環境デプロイ前に以下を確認してください：

- [ ] `ADMIN_PASSWORD` を変更した
- [ ] `data/` ディレクトリへのアクセスが制限されている
- [ ] `uploads/` ディレクトリで PHP 実行が禁止されている
- [ ] SSL/HTTPS が有効化されている
- [ ] ファイルパーミッションが正しく設定されている
- [ ] ログファイルディレクトリが書き込み可能である
- [ ] `display_errors` が無効化されている
- [ ] `.htaccess` が有効化されている

## トラブルシューティング

### ローカルから本番環境にアップロード後、リンクが機能しない

**原因**: ベースURLが正しく判定されていない

**解決策**:
1. ブラウザの開発者ツール（F12）でコンソールを確認
2. `window.BASE_URL` と `window.ENVIRONMENT` が正しく表示されているか確認
3. サーバーの `$_SERVER['HTTP_HOST']` が `teito.link` になっているか確認

```php
// デバッグ用
echo '<pre>';
echo 'HTTP_HOST: ' . $_SERVER['HTTP_HOST'] . "\n";
echo 'ENVIRONMENT: ' . ENVIRONMENT . "\n";
echo 'BASE_URL: ' . BASE_URL . "\n";
echo '</pre>';
```

### admin/ ページにアクセスできない

**原因**: リダイレクトが正しく機能していない

**解決策**:
1. `.htaccess` が正しく配置されているか確認
2. Apache の `mod_rewrite` が有効化されているか確認

```bash
sudo apache2ctl -M | grep rewrite
```

### ファイルアップロードが失敗する

**原因**: パーミッションまたはアップロードサイズ制限

**解決策**:
1. `uploads/` ディレクトリのパーミッションを確認: `chmod 755`
2. `php.ini` の `upload_max_filesize` と `post_max_size` を確認

## サポート

問題が発生した場合は、以下の情報を収集してください：

- 環境（ローカル / 本番）
- エラーメッセージ（スクリーンショット）
- ブラウザコンソールのエラー（F12 → Console）
- サーバーエラーログ（`/var/log/apache2/error.log` など）
- PHP エラーログ（`error_log` 設定先）

---

**最終更新**: 2026年1月6日
