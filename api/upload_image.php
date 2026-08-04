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

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '画像のアップロードに失敗しました']);
    exit;
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '許可されていないファイル形式です（JPG, PNG, GIF, WEBP のみ対応）']);
    exit;
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'img_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
$targetDir = __DIR__ . '/../img/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$targetPath = $targetDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode([
        'success' => true,
        'url' => 'img/' . $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ファイルの保存に失敗しました']);
}
