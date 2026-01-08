<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// トークン検証
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if (!BACKUP_CRON_TOKEN || !hash_equals((string)BACKUP_CRON_TOKEN, (string)$token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $results = [
        'html' => null,
        'json' => null,
        'rotated' => [ 'html' => 0, 'json' => 0 ],
    ];

    $backupDir = DATA_DIR . '/backups';
    if (!is_dir($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            throw new Exception('バックアップディレクトリの作成に失敗しました');
        }
    }

    // HTMLバックアップ（index.html）
    $indexPath = dirname(__DIR__) . '/index.html';
    if (file_exists($indexPath)) {
        // ローテーション（上限10）
        $before = glob($backupDir . '/index_*.html') ?: [];
        rotate_html_backups(BACKUP_MAX_GENERATIONS);
        $after = glob($backupDir . '/index_*.html') ?: [];
        $results['rotated']['html'] = max(0, count($before) - count($after));

        $htmlBackup = $backupDir . '/index_' . date('Y-m-d_H-i-s') . '.html';
        if (!copy($indexPath, $htmlBackup)) {
            throw new Exception('index.html のバックアップ作成に失敗');
        }
        $results['html'] = basename($htmlBackup);
    } else {
        $results['html'] = 'index.html 不在のためスキップ';
    }

    // JSONバックアップ（content.json）
    if (file_exists(CONTENT_FILE)) {
        // ローテーション（上限10）
        $before = glob($backupDir . '/content_*.json') ?: [];
        rotate_backups(BACKUP_MAX_GENERATIONS);
        $after = glob($backupDir . '/content_*.json') ?: [];
        $results['rotated']['json'] = max(0, count($before) - count($after));

        if (!create_backup()) {
            throw new Exception('content.json のバックアップ作成に失敗');
        }
        // 最新バックアップ名を推定
        $latest = glob($backupDir . '/content_*.json');
        usort($latest, function($a, $b) { return filemtime($b) - filemtime($a); });
        $results['json'] = isset($latest[0]) ? basename($latest[0]) : null;
    } else {
        $results['json'] = 'content.json 不在のためスキップ';
    }

    echo json_encode(['success' => true, 'results' => $results]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
