<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ログイン認証チェック
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '管理者としてログインしていません。']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'list');
$imgDir = __DIR__ . '/../img/';
$jsonPath = __DIR__ . '/../data/checklist.json';

// checklist.json から画像使用状況をスキャン
function getImageUsages($jsonPath) {
    $usages = [];
    if (!file_exists($jsonPath)) return $usages;
    
    $raw = file_get_contents($jsonPath);
    $data = json_decode($raw, true);
    if (!is_array($data)) return $usages;

    foreach ($data as $cat) {
        $catTitle = isset($cat['categoryTitle']) ? $cat['categoryTitle'] : '';
        if (empty($cat['items']) || !is_array($cat['items'])) continue;

        foreach ($cat['items'] as $item) {
            $label = isset($item['labelHtml']) ? strip_tags($item['labelHtml']) : (isset($item['name']) ? $item['name'] : '項目');
            $html = isset($item['modalContentHtml']) ? $item['modalContentHtml'] : '';
            
            // img タグの src を抽出
            if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
                foreach ($matches[1] as $src) {
                    // クエリパラメータ (?v=...) を除去
                    $baseSrc = explode('?', $src)[0];
                    $filename = basename($baseSrc);
                    if (!isset($usages[$filename])) {
                        $usages[$filename] = [];
                    }
                    $usages[$filename][] = [
                        'category' => $catTitle,
                        'label' => $label,
                        'itemId' => isset($item['id']) ? $item['id'] : ''
                    ];
                }
            }
        }
    }
    return $usages;
}

// 1. 画像一覧取得
if ($action === 'list') {
    if (!is_dir($imgDir)) {
        mkdir($imgDir, 0777, true);
    }

    $usages = getImageUsages($jsonPath);
    $files = scandir($imgDir);
    $images = [];

    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $filePath = $imgDir . $file;
        if (!is_file($filePath)) continue;

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) continue;

        $size = filesize($filePath);
        $mtime = filemtime($filePath);

        // 画像サイズ（幅・高さ）
        $dimensions = @getimagesize($filePath);
        $width = $dimensions ? $dimensions[0] : 0;
        $height = $dimensions ? $dimensions[1] : 0;

        $usedIn = isset($usages[$file]) ? $usages[$file] : [];

        $images[] = [
            'name' => $file,
            'url' => 'img/' . $file . '?v=' . $mtime,
            'size' => $size,
            'sizeFormatted' => $size > 1048576 ? round($size / 1048576, 2) . ' MB' : round($size / 1024, 1) . ' KB',
            'mtime' => $mtime,
            'dateFormatted' => date('Y/m/d H:i', $mtime),
            'width' => $width,
            'height' => $height,
            'usedIn' => $usedIn,
            'usedCount' => count($usedIn)
        ];
    }

    // 更新日の新しい順にソート
    usort($images, function($a, $b) {
        return $b['mtime'] - $a['mtime'];
    });

    echo json_encode([
        'success' => true,
        'images' => $images,
        'totalCount' => count($images),
        'totalSize' => array_sum(array_column($images, 'size'))
    ]);
    exit;
}

// 2. 新規画像アップロード
if ($action === 'upload') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit;
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'アップロードに失敗しました']);
        exit;
    }

    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '許可されていないファイル形式です（JPG, PNG, GIF, WEBP, SVGのみ対応）']);
        exit;
    }

    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // ファイル名のサニタイズ（英数・ハイフン・アンダースコア・日本語も安全に許可）
    $cleanName = preg_replace('/[^\w\-\p{Han}\p{Hiragana}\p{Katakana}]/u', '_', $originalName);
    if (empty($cleanName)) $cleanName = 'img_' . date('Ymd_His');

    $filename = $cleanName . '.' . $ext;
    // 同名ファイルが存在する場合は連番を付与
    if (file_exists($imgDir . $filename)) {
        $filename = $cleanName . '_' . date('His') . '.' . $ext;
    }

    $targetPath = $imgDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true,
            'message' => '画像をアップロードしました',
            'filename' => $filename,
            'url' => 'img/' . $filename . '?v=' . time()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'ファイルの保存に失敗しました']);
    }
    exit;
}

// 3. 画像の上書き差し替え（同名ファイルを高画質画像等で上書き）
if ($action === 'replace') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit;
    }

    $targetFilename = isset($_POST['target_filename']) ? basename($_POST['target_filename']) : '';
    if (empty($targetFilename) || !file_exists($imgDir . $targetFilename)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '差し替え対象の画像が見つかりません']);
        exit;
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '差し替え画像のアップロードに失敗しました']);
        exit;
    }

    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '許可されていないファイル形式です']);
        exit;
    }

    // 既存画像のバックアップを作成
    $backupDir = __DIR__ . '/../data/backups/images/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    @copy($imgDir . $targetFilename, $backupDir . pathinfo($targetFilename, PATHINFO_FILENAME) . '_backup_' . date('Ymd_His') . '.' . pathinfo($targetFilename, PATHINFO_EXTENSION));

    // 上書き保存
    $targetPath = $imgDir . $targetFilename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // キャッシュバスターを更新するためタッチ
        touch($targetPath);
        echo json_encode([
            'success' => true,
            'message' => '画像を差し替えました（キャッシュも自動更新されます）',
            'filename' => $targetFilename,
            'url' => 'img/' . $targetFilename . '?v=' . time()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '差し替え保存に失敗しました']);
    }
    exit;
}

// 4. 画像の削除
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit;
    }

    $filename = isset($_POST['filename']) ? basename($_POST['filename']) : '';
    if (empty($filename) || !file_exists($imgDir . $filename)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '削除対象の画像が見つかりません']);
        exit;
    }

    // 念のためバックアップ
    $backupDir = __DIR__ . '/../data/backups/deleted_images/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    @copy($imgDir . $filename, $backupDir . pathinfo($filename, PATHINFO_FILENAME) . '_deleted_' . date('Ymd_His') . '.' . pathinfo($filename, PATHINFO_EXTENSION));

    if (@unlink($imgDir . $filename)) {
        echo json_encode([
            'success' => true,
            'message' => '画像を削除しました（バックアップは保持されています）'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '画像の削除に失敗しました']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action']);
