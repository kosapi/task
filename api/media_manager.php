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
$thumbDir = __DIR__ . '/../data/thumbnails/';

// サムネイルディレクトリの自動作成
if (!is_dir($thumbDir)) {
    mkdir($thumbDir, 0777, true);
}

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
            if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
                foreach ($matches[1] as $src) {
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

// サムネイルを生成して指定パスに保存するヘルパー
function generateThumbnail($origPath, $thumbPath, $maxSize = 300) {
    $imageInfo = @getimagesize($origPath);
    if (!$imageInfo) return false;

    $srcW = $imageInfo[0];
    $srcH = $imageInfo[1];
    $mimeType = $imageInfo['mime'];

    $srcImg = null;
    switch ($mimeType) {
        case 'image/jpeg': $srcImg = @imagecreatefromjpeg($origPath); break;
        case 'image/png':  $srcImg = @imagecreatefrompng($origPath); break;
        case 'image/gif':  $srcImg = @imagecreatefromgif($origPath); break;
        case 'image/webp': $srcImg = @imagecreatefromwebp($origPath); break;
        default: return false;
    }
    if (!$srcImg) return false;

    // リサイズ計算（長辺 $maxSize px に収める）
    if ($srcW > $srcH) {
        $dstW = min($srcW, $maxSize);
        $dstH = (int)round($srcH * ($dstW / $srcW));
    } else {
        $dstH = min($srcH, $maxSize);
        $dstW = (int)round($srcW * ($dstH / $srcH));
    }

    $dstImg = imagecreatetruecolor($dstW, $dstH);

    // PNG/GIF の透明部分は白背景で合成
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        $white = imagecolorallocate($dstImg, 255, 255, 255);
        imagefill($dstImg, 0, 0, $white);
    }

    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
    imagedestroy($srcImg);

    // JPEGとして保存（品質80）
    $result = imagejpeg($dstImg, $thumbPath, 80);
    imagedestroy($dstImg);
    return $result;
}

// ==============================================
// 0. サムネイル生成・配信（action=thumbnail）
// ==============================================
if ($action === 'thumbnail') {
    // JSON Content-Type を画像に上書き
    header_remove('Content-Type');

    $filename = isset($_GET['f']) ? basename($_GET['f']) : '';
    if (empty($filename)) {
        http_response_code(400);
        exit;
    }

    $origPath = $imgDir . $filename;
    if (!file_exists($origPath)) {
        http_response_code(404);
        exit;
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // SVG はそのまま返す
    if ($ext === 'svg') {
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=2592000');
        readfile($origPath);
        exit;
    }

    // サムネイルキャッシュのファイルパス（mtime込みハッシュで一意に）
    $cacheKey = md5($filename . '_' . filemtime($origPath));
    $thumbPath = $thumbDir . $cacheKey . '.jpg';

    // キャッシュがなければ生成
    if (!file_exists($thumbPath)) {
        if (!generateThumbnail($origPath, $thumbPath)) {
            // 生成失敗時はオリジナルを配信
            $imageInfo = getimagesize($origPath);
            $mime = $imageInfo ? $imageInfo['mime'] : 'image/jpeg';
            header('Content-Type: ' . $mime);
            readfile($origPath);
            exit;
        }
    }

    // キャッシュを配信
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=2592000');
    header('Content-Length: ' . filesize($thumbPath));
    readfile($thumbPath);
    exit;
}

// ==============================================
// 1. 画像一覧取得
// ==============================================
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

        $dimensions = @getimagesize($filePath);
        $width = $dimensions ? $dimensions[0] : 0;
        $height = $dimensions ? $dimensions[1] : 0;

        $usedIn = isset($usages[$file]) ? $usages[$file] : [];

        // サムネイルURL（action=thumbnail 経由）
        $thumbUrl = 'api/media_manager.php?action=thumbnail&f=' . rawurlencode($file);

        $images[] = [
            'name' => $file,
            'url' => 'img/' . rawurlencode($file) . '?v=' . $mtime,
            'thumbUrl' => $thumbUrl,
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

// ==============================================
// 2. 新規画像アップロード
// ==============================================
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

    $cleanName = preg_replace('/[^\w\-\p{Han}\p{Hiragana}\p{Katakana}]/u', '_', $originalName);
    if (empty($cleanName)) $cleanName = 'img_' . date('Ymd_His');

    $filename = $cleanName . '.' . $ext;
    if (file_exists($imgDir . $filename)) {
        $filename = $cleanName . '_' . date('His') . '.' . $ext;
    }

    $targetPath = $imgDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true,
            'message' => '画像をアップロードしました',
            'filename' => $filename,
            'url' => 'img/' . rawurlencode($filename) . '?v=' . time()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'ファイルの保存に失敗しました']);
    }
    exit;
}

// ==============================================
// 3. 画像の上書き差し替え
// ==============================================
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

    $backupDir = __DIR__ . '/../data/backups/images/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    @copy($imgDir . $targetFilename, $backupDir . pathinfo($targetFilename, PATHINFO_FILENAME) . '_backup_' . date('Ymd_His') . '.' . pathinfo($targetFilename, PATHINFO_EXTENSION));

    $targetPath = $imgDir . $targetFilename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        touch($targetPath);

        // 古いサムネイルキャッシュを一掃（差し替え後は新しいmtimeで再生成される）
        foreach (glob($thumbDir . '*.jpg') as $tf) {
            @unlink($tf);
        }

        echo json_encode([
            'success' => true,
            'message' => '画像を差し替えました（キャッシュも自動更新されます）',
            'filename' => $targetFilename,
            'url' => 'img/' . rawurlencode($targetFilename) . '?v=' . time()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '差し替え保存に失敗しました']);
    }
    exit;
}

// ==============================================
// 4. 画像の削除
// ==============================================
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
