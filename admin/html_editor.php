<?php
// 管理画面HTMLエディタ
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit();
}

$target_file = '../index.html';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['html_content'])) {
        $new_content = $_POST['html_content'];
        // バックアップ
        $backup_file = $target_file . '.backup_' . date('Ymd_His');
        copy($target_file, $backup_file);
        // 上書き保存
        file_put_contents($target_file, $new_content);
        $message = '保存しました。バックアップ: ' . basename($backup_file);
    }
}

$current_content = file_exists($target_file) ? file_get_contents($target_file) : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>HTMLエディタ（index.html編集）</title>
    <style>
        textarea { width: 100%; height: 500px; font-family: monospace; }
        .message { color: green; margin-bottom: 10px; }
    </style>
</head>
<body>
<h1>index.html編集</h1>
<?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<form method="post">
    <textarea name="html_content"><?php echo htmlspecialchars($current_content); ?></textarea><br>
    <button type="submit">保存</button>
</form>
<p><a href="index.php">管理画面トップへ戻る</a></p>
</body>
</html>
