<?php
header('Content-Type: text/html; charset=utf-8');

$html = file_get_contents('index.html');
$imgDir = 'img/';

// HTMLから画像ファイル参照を抽出
preg_match_all('/src="([^"]+?\.(?:jpg|jpeg|png|gif))"/', $html, $matches);

$referenced = array_unique($matches[1]);
sort($referenced);

echo "<h2>✓ 復元されたHTMLの状態チェック</h2>";
echo "<p>HTMLで参照されている画像数: <strong>" . count($referenced) . "</strong>個</p>";

$notFound = array();
$found = array();

foreach ($referenced as $file) {
    $decoded = urldecode($file);
    $path = $imgDir . $decoded;
    
    if (file_exists($path)) {
        $found[] = $decoded;
    } else {
        $notFound[] = array(
            'encoded' => $file,
            'decoded' => $decoded
        );
    }
}

if (count($notFound) > 0) {
    echo "<h3>❌ 見つからない画像 (" . count($notFound) . "個)</h3>";
    echo "<ul>";
    foreach ($notFound as $item) {
        echo "<li>" . htmlspecialchars($item['decoded']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h3>✅ すべての参照されている画像が存在します！</h3>";
    echo "<p>見つかった画像: <strong>" . count($found) . "</strong>個</p>";
}

// 実ファイル数
$actualFiles = count(glob($imgDir . '*{.jpg,.jpeg,.png,.gif}', GLOB_BRACE));
echo "<p>実際のイメージファイル数: <strong>$actualFiles</strong>個</p>";
?>
