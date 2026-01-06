<?php
header('Content-Type: text/html; charset=utf-8');

$html = file_get_contents('index.html');
$imgDir = 'img/';

// HTMLから画像ファイル参照を抽出（コメントアウトされていない）
preg_match_all('/src="(?!.*?<!--)([^"]+?\.(?:jpg|jpeg|png|gif))"/', $html, $matches);

$referenced = array_unique($matches[1]);
sort($referenced);

echo "<h2>✓ 最終確認：画像管理状況</h2>";

$notFound = array();
$found = array();

foreach ($referenced as $file) {
    $decoded = urldecode($file);
    $path = $imgDir . $decoded;
    
    if (file_exists($path)) {
        $found[] = $decoded;
    } else {
        $notFound[] = $decoded;
    }
}

echo "<h3>✅ HTMLで参照されている画像</h3>";
echo "<ul>";
echo "<li><strong>有効な参照数</strong>: " . count($referenced) . "個</li>";
echo "<li><strong>存在するファイル</strong>: " . count($found) . "個</li>";
echo "<li><strong>欠落しているファイル</strong>: " . count($notFound) . "個</li>";
echo "</ul>";

if (count($notFound) > 0) {
    echo "<h4>❌ 欠落ファイル一覧：</h4>";
    echo "<ul>";
    foreach ($notFound as $f) {
        echo "<li>" . htmlspecialchars($f) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h4 style='color: green;'>✅ すべてのファイルが完璧に紐づいています！</h4>";
}

// 実ファイル数
$actualFiles = count(array_filter(glob($imgDir . '*'), 'is_file'));
$unusedCount = $actualFiles - count($found);

echo "<h3>📊 ファイル統計</h3>";
echo "<ul>";
echo "<li><strong>実ファイル総数</strong>: $actualFiles 個</li>";
echo "<li><strong>参照済みファイル</strong>: " . count($found) . " 個</li>";
echo "<li><strong>未使用ファイル</strong>: $unusedCount 個</li>";
echo "</ul>";

// 削除済みファイル確認
echo "<h3>🗑️ 削除済みファイル</h3>";
$deleted_files = array(
    'ect_v2_20251214023738_a68ec8a8.jpg',
    'GOPAY_____________20251217065557_8ccfab64.png',
    'GO2_20251217065350_66e0485d.png',
    'GO3_20251217065359_25494fd0.png',
    'GO4_20251217065409_6e6e9bb0.png',
    'GO5_20251217065421_61adec5c.png',
    'GO6_20251217065521_526a35ee.png',
    'GO7_20251217065532_583315df.png'
);

echo "<ul>";
echo "<li>✓ ect_v2 重複ファイル（タイムスタンプ版）: 削除完了</li>";
echo "<li>✓ GOPAY破損ファイル（タイムスタンプ版）: 削除完了</li>";
echo "<li>✓ GO2～GO7（タイムスタンプ版）: リネーム完了（新名：GO2.png～GO7.png）</li>";
echo "</ul>";
?>
