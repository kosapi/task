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

// 全画像タグ (img src) を走査し、ファイル更新時刻 (filemtime) をキャッシュバスター(?v=...)として自動付与
function applyImageCacheBuster(&$node) {
    if (is_array($node)) {
        foreach ($node as &$child) {
            applyImageCacheBuster($child);
        }
    } elseif (is_string($node)) {
        if (strpos($node, '<img') !== false) {
            $node = preg_replace_callback('/<img([^>]+)src=["\']([^"\']+)["\']([^>]*)>/iu', function($matches) {
                $before = $matches[1];
                $src = $matches[2];
                $after = $matches[3];

                // 外部URL(http://, https://, //)やData URIはスキップ
                if (preg_match('/^(https?:|\/\/|data:)/i', $src)) {
                    return $matches[0];
                }

                // 既存のクエリパラメータを除去
                $parts = explode('?', $src);
                $basePath = $parts[0];
                $cleanPath = ltrim($basePath, '/');
                if (strpos($cleanPath, 'task/') === 0) {
                    $cleanPath = substr($cleanPath, 5);
                }

                $filePath = __DIR__ . '/../' . $cleanPath;
                $version = time();
                if (file_exists($filePath)) {
                    $version = filemtime($filePath);
                }

                $newSrc = $basePath . '?v=' . $version;
                return '<img' . $before . 'src="' . $newSrc . '"' . $after . '>';
            }, $node);
        }
    }
}

applyImageCacheBuster($data);

// 過去のバックアップを自動保存
if (file_exists($jsonFile)) {
    @copy($jsonFile, $backupDir . 'checklist_backup_' . date('Ymd_His') . '.json');
}

// アトミックに保存
$formattedJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if (file_put_contents($jsonFile, $formattedJson) !== false) {
    // index.html のフッター更新日および本日の安全標語（slogans）を本日の最新データに全自動更新
    $indexPath = __DIR__ . '/../index.html';
    if (file_exists($indexPath)) {
        $indexHtml = @file_get_contents($indexPath);
        if ($indexHtml) {
            $todayStr = date('Y年m月d日');
            $updatedHtml = preg_replace('/(<small>更新日:\s*<time[^>]*>)(.*?)(<\/time><\/small>)/iu', '${1}' . $todayStr . '${3}', $indexHtml);
            
            // 標語データが存在する場合は index.html 内の標語タグおよび各曜日テキストも自動更新
            $slogansList = null;
            if (isset($data['slogans']) && is_array($data['slogans']) && count($data['slogans']) === 7) {
                $slogansList = $data['slogans'];
            } elseif (isset($data[0]['slogans']) && is_array($data[0]['slogans'])) {
                $slogansList = $data[0]['slogans'];
            }

            if ($slogansList) {
                $slogansJson = json_encode($slogansList, JSON_UNESCAPED_UNICODE);
                $updatedHtml = preg_replace('/(<script[^>]*id="slogans-data"[^>]*>)(.*?)(<\/script>)/su', '${1}' . $slogansJson . '${3}', $updatedHtml);
                
                $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                foreach ($days as $idx => $dayId) {
                    if (isset($slogansList[$idx])) {
                        $sloganText = htmlspecialchars($slogansList[$idx], ENT_QUOTES, 'UTF-8');
                        $pattern = '/(<div[^>]*id="' . $dayId . '"[^>]*>.*?<p[^>]*class="text-center"[^>]*>)(.*?)(<\/p>)/su';
                        $updatedHtml = preg_replace($pattern, '${1}' . $sloganText . '${3}', $updatedHtml);
                    }
                }
            }

            // index.html に埋め込まれた checklist-preloaded-data も全自動で即時更新（爆速レンダリング用）
            $rawJsonForPreload = str_replace('</script>', '<\/script>', json_encode($data, JSON_UNESCAPED_UNICODE));
            $updatedHtml = preg_replace('/(<script[^>]*id="checklist-preloaded-data"[^>]*>)(.*?)(<\/script>)/su', '${1}' . $rawJsonForPreload . '${3}', $updatedHtml);

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
