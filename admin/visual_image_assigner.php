<?php
// ビジュアル画像割当ツール（初期版）
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
check_login();

// 割当情報ファイル
$assign_file = __DIR__ . '/image_assignments.json';
$assignments = [];
if (file_exists($assign_file)) {
    $assignments = json_decode(file_get_contents($assign_file), true)['assignments'] ?? [];
}

// imgフォルダの画像一覧取得
$img_dir = dirname(__DIR__) . '/img';
$images = [];
foreach (glob($img_dir . '/*') as $file) {
    if (is_file($file)) {
        $images[] = basename($file);
    }
}

// セクション候補（例: index.html内のid属性一覧）
$sections = [
    'section1' => 'チェックリスト',
    'section2' => '決済方法',
    'section3' => '注意事項',
    'section4' => 'タブレット操作',
    'section5' => 'その他'
];

// 割当保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image'], $_POST['section'])) {
    $assignments[] = [
        'image' => $_POST['image'],
        'section' => $_POST['section']
    ];
    file_put_contents($assign_file, json_encode(['assignments' => $assignments], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    header('Location: visual_image_assigner.php?success=1');
    exit;
}

?><!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ビジュアル画像割当ツール</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">ビジュアル画像割当ツール</h1>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">割当を保存しました。</div>
    <?php endif; ?>
    <form method="POST" class="mb-4" id="assignForm">
        <div class="mb-3">
            <label class="form-label">画像選択</label>
            <select name="image" class="form-select" required id="imageSelect">
                <option value="">画像を選択...</option>
                <?php foreach ($images as $img): ?>
                    <option value="<?= htmlspecialchars($img) ?>"><?= htmlspecialchars($img) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="mt-2" id="previewBox" style="min-height:90px;"></div>
        </div>
        <div class="mb-3">
            <label class="form-label">割当先セクション</label>
            <select name="section" class="form-select" required>
                <option value="">セクションを選択...</option>
                <?php foreach ($sections as $id => $label): ?>
                    <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">割当保存</button>
    </form>
    <script>
    document.getElementById('imageSelect').addEventListener('change', function() {
        var val = this.value;
        var box = document.getElementById('previewBox');
        if(val) {
            box.innerHTML = '<img src="../img/' + encodeURIComponent(val) + '" style="max-width:120px;max-height:90px;border:1px solid #ccc;">';
        } else {
            box.innerHTML = '';
        }
    });
    </script>
    <h2 class="h4 mb-3">現在の割当一覧</h2>
    <table class="table table-bordered bg-white">
        <thead><tr><th>画像</th><th>割当先セクション</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($assignments as $a): ?>
            <tr>
                <td><img src="../img/<?= htmlspecialchars($a['image']) ?>" alt="" style="max-width:80px;max-height:80px;"> <?= htmlspecialchars($a['image']) ?></td>
                <td><?= htmlspecialchars($sections[$a['section']] ?? $a['section']) ?></td>
                <td>
                    <form method="POST" action="rename_image.php" class="d-inline-block rename-form" onsubmit="return confirm('ファイル名を変更しますか？');">
                        <input type="hidden" name="old_name" value="<?= htmlspecialchars($a['image']) ?>">
                        <input type="text" name="new_name" value="<?= htmlspecialchars($a['image']) ?>" class="form-control form-control-sm d-inline-block" style="width:140px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">名前変更</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
