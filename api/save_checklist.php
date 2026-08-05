<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

// ログイン認証チェック
if (empty($_SESSION['admin_logged_in'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => '管理者としてログインしていません。再ログインしてください。']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => '許可されていないリクエスト方法です']);
    exit;
}

$rawInput = file_get_contents('php://input');
$requestData = json_decode($rawInput, true);

$data = null;
if (is_array($requestData) && isset($requestData['data_b64'])) {
    $decodedJson = base64_decode($requestData['data_b64']);
    $data = json_decode($decodedJson, true);
} else {
    $data = $requestData;
}

if ($data === null || !is_array($data)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => '無効なデータ形式です']);
    exit;
}

$jsonFile = __DIR__ . '/../data/checklist.json';
$backupDir = __DIR__ . '/../data/backups/';

if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0777, true);
}

// 過去のバックアップを自動保存
if (file_exists($jsonFile)) {
    @copy($jsonFile, $backupDir . 'checklist_backup_' . date('Ymd_His') . '.json');
}

// アトミックに保存
$formattedJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if (file_put_contents($jsonFile, $formattedJson) !== false) {
    // index.html のフッター更新日を本日の日付（例: 2026年08月05日）に自動更新
    $indexPath = __DIR__ . '/../index.html';
    if (file_exists($indexPath)) {
        $indexHtml = @file_get_contents($indexPath);
        if ($indexHtml) {
            $todayStr = date('Y年m月d日');
            $updatedHtml = preg_replace('/(<small>更新日:\s*<time>)(.*?)(<\/time><\/small>)/u', '${1}' . $todayStr . '${3}', $indexHtml);
            if ($updatedHtml) {
                @file_put_contents($indexPath, $updatedHtml);
            }
        }
    }

    ob_clean();
    echo json_encode(['success' => true, 'message' => '正常に保存されました']);
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'ファイルの書き込み権限エラー等により、データの保存に失敗しました']);
}
