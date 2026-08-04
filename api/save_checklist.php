<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ログイン認証チェック
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '管理者としてログインしていません。']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '無効なJSONデータです']);
    exit;
}

$jsonFile = __DIR__ . '/../data/checklist.json';
$backupDir = __DIR__ . '/../data/backups/';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// 過去のバックアップを自動保存
if (file_exists($jsonFile)) {
    copy($jsonFile, $backupDir . 'checklist_backup_' . date('Ymd_His') . '.json');
}

// アトミックに保存
$formattedJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if (file_put_contents($jsonFile, $formattedJson) !== false) {
    // index.html のフッター更新日を本日の日付（例: 2026年08月05日）に自動更新
    $indexPath = __DIR__ . '/../index.html';
    if (file_exists($indexPath)) {
        $indexHtml = file_get_contents($indexPath);
        $todayStr = date('Y年m月d日');
        $updatedHtml = preg_replace('/(<small>更新日:\s*<time>)(.*?)(<\/time><\/small>)/u', '${1}' . $todayStr . '${3}', $indexHtml);
        if ($updatedHtml) {
            file_put_contents($indexPath, $updatedHtml);
        }
    }

    echo json_encode(['success' => true, 'message' => '正常に保存され、フッターの更新日も自動更新されました']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'データの保存に失敗しました']);
}
