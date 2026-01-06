<?php
/**
 * CMS設定ファイル
 */

// ============ 環境検出（localhost vs 本番） ============
function get_environment() {
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    
    // teito.link（本番環境）
    if (strpos($host, 'teito.link') !== false) {
        return 'production';
    }
    
    // localhost（ローカル開発環境）
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return 'local';
    }
    
    // デフォルトはローカル
    return 'local';
}

define('ENVIRONMENT', get_environment());

// ============ 環境に応じたベースURL設定 ============
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) 
        ? "https://" 
        : "http://";
    $host = $_SERVER['HTTP_HOST'];
    
    if (ENVIRONMENT === 'production') {
        return 'https://teito.link/task/';
    } else {
        return $protocol . $host . '/task/';
    }
}

define('BASE_URL', get_base_url());

// ============ セキュリティ設定 ============
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', password_hash('admin123', PASSWORD_BCRYPT)); // 初回セットアップ後に変更してください

// ============ ファイルパス設定 ============
define('DATA_DIR', __DIR__ . '/data');
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('CONTENT_FILE', DATA_DIR . '/content.json');
define('USERS_FILE', DATA_DIR . '/users.json');

// ============ セッション設定 ============
define('SESSION_NAME', 'task_cms_session');
define('SESSION_LIFETIME', 3600); // 1時間

// ============ アップロード設定 ============
define('MAX_UPLOAD_SIZE', 20 * 1024 * 1024); // 20MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'pdf']);

// ============ タイムゾーン ============
date_default_timezone_set('Asia/Tokyo');

// ============ エラー表示（環境に応じて） ============
if (ENVIRONMENT === 'production') {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// ============ セッション開始 ============
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
