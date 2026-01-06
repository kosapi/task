<?php
header('Content-Type: text/html; charset=utf-8');

$html = file_get_contents('index.html');
$imgDir = 'img/';

// GOPAY関連の画像参照を抽出
preg_match_all('/src="([^"]*GOPAY[^"]+?\.(?:jpg|jpeg|png|gif))"/', $html, $matches);

$gopay_refs = array_unique($matches[1]);
sort($gopay_refs);

echo "<h2>✓ GOPAY関連画像の参照確認</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>参照名（エンコード）</th><th>デコード名</th><th>ファイル存在</th><th>パス</th></tr>";

foreach ($gopay_refs as $encoded) {
    $decoded = urldecode($encoded);
    $path = $imgDir . $decoded;
    $exists = file_exists($path) ? "✓ あり" : "❌ なし";
    $status_color = file_exists($path) ? "#90EE90" : "#FFB6C6";
    
    echo "<tr style='background-color: $status_color'>";
    echo "<td>" . htmlspecialchars($encoded) . "</td>";
    echo "<td>" . htmlspecialchars($decoded) . "</td>";
    echo "<td style='font-weight: bold; text-align: center;'>$exists</td>";
    echo "<td>" . htmlspecialchars($path) . "</td>";
    echo "</tr>";
}

echo "</table>";

// 実ファイル
echo "<h3>img フォルダの GOPAY関連実ファイル</h3>";
$files = glob($imgDir . '*GOPAY*');
if (count($files) > 0) {
    echo "<ul>";
    foreach ($files as $f) {
        echo "<li>" . basename($f) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>GOPAY関連ファイルなし</p>";
}
?>
