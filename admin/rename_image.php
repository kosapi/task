<?php
// 画像ファイル名変更API（imgフォルダ限定）
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
check_login();

$img_dir = dirname(__DIR__) . '/img';
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_name'], $_POST['new_name'])) {
    $old = basename($_POST['old_name']);
    $new = basename($_POST['new_name']);
    $old_path = $img_dir . '/' . $old;
    $new_path = $img_dir . '/' . $new;
    if (!file_exists($old_path)) {
        $response['message'] = '元ファイルが存在しません';
    } elseif (file_exists($new_path)) {
        $response['message'] = '新ファイル名が既に存在します';
    } elseif (!preg_match('/^[a-zA-Z0-9_.-]+\.(jpg|jpeg|png|gif|webp)$/i', $new)) {
        $response['message'] = 'ファイル名は半角英数字と拡張子のみ許可されます';
    } else {
        if (rename($old_path, $new_path)) {
            $response['success'] = true;
            $response['message'] = 'ファイル名を変更しました';
        } else {
            $response['message'] = 'ファイル名変更に失敗しました';
        }
    }
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>