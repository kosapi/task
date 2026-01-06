<?php
header('Content-Type: application/json; charset=utf-8');

// config.phpから定数を読み込む
$config_file = __DIR__ . '/../config.php';
if (!file_exists($config_file)) {
    // ローカルパスで試す
    $config_file = '/var/www/html/task/config.php';
}

if (file_exists($config_file)) {
    require_once $config_file;
} else {
    // 定義していない場合はここで定義
    define('DATA_DIR', __DIR__ . '/../data');
}

// お知らせファイルを読み込む
$notices_file = DATA_DIR . '/notices.json';

if (file_exists($notices_file)) {
    $json_content = file_get_contents($notices_file);
    $notices = json_decode($json_content, true) ?? [];
    
    // デバッグ用ログ（ファイルに出力）
    error_log('[get-notices.php] ファイル存在 - ' . $notices_file);
    error_log('[get-notices.php] 全お知らせ数: ' . count($notices));
} else {
    error_log('[get-notices.php] ファイル不存在 - ' . $notices_file);
    $notices = [];
}

echo json_encode($notices, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
