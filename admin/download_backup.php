<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$file = $_GET['file'] ?? '';

// 許可するファイル名パターン（index_YYYY-mm-dd_HH-ii-ss.html / content_YYYY-mm-dd_HH-ii-ss.json / index_cleanup_...）
$pattern = '/^(index|index_cleanup|content)_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.(html|json)$/';
if (!preg_match($pattern, $file)) {
    http_response_code(400);
    echo 'Invalid file name';
    exit;
}

$backupDir = DATA_DIR . '/backups';
$targetPath = $backupDir . '/' . $file;

// パス検証（存在確認とディレクトリトラバーサル防止）
$realBackupDir = realpath($backupDir);
$realTarget = $realBackupDir && file_exists($targetPath) ? realpath($targetPath) : false;
if (!$realBackupDir || !$realTarget || strpos($realTarget, $realBackupDir) !== 0 || !is_file($realTarget)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$filesize = filesize($realTarget);
$basename = basename($realTarget);

// 適当な Content-Type を付与（拡張子に応じて簡易判定）
$ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
$contentType = 'application/octet-stream';
if ($ext === 'json') {
    $contentType = 'application/json';
} elseif ($ext === 'html') {
    $contentType = 'text/html';
}

header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . $basename . '"');
header('Content-Length: ' . $filesize);
header('X-Content-Type-Options: nosniff');

$fp = fopen($realTarget, 'rb');
if ($fp) {
    fpassthru($fp);
    fclose($fp);
    exit;
}

http_response_code(500);
echo 'Failed to read file';
