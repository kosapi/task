<?php
$html = file_get_contents('index.html');

// img/で始まる画像パスを全て抽出
preg_match_all('/(?:src|href|data-src)=["\']img\/([^"\']+\.(?:jpg|jpeg|png|gif|webp|svg|pdf))["\']/', $html, $matches1);
preg_match_all('/img\/([^"\'\\s>]+\.(?:jpg|jpeg|png|gif|webp|svg|pdf))/i', $html, $matches2);

$all_matches = array_merge($matches1[1], $matches2[1]);
$files = array_unique($all_matches);
sort($files);

echo "=== HTML内で使用されている画像 (" . count($files) . "個) ===\n\n";
foreach($files as $f) {
    $decoded = rawurldecode($f);
    echo $decoded . "\n";
}

echo "\n\n=== imgフォルダー内の実際のファイル ===\n\n";
$img_files = glob('img/*');
$img_filenames = [];
foreach($img_files as $f) {
    if (is_file($f)) {
        $img_filenames[] = basename($f);
    }
}
sort($img_filenames);
echo count($img_filenames) . "個のファイル\n\n";

echo "\n=== HTMLで使用されているがimgフォルダーに存在しない画像 ===\n\n";
$missing = [];
foreach($files as $f) {
    $decoded = rawurldecode($f);
    $img_path = 'img/' . $decoded;
    if (!file_exists($img_path)) {
        $missing[] = $decoded;
        echo "❌ " . $decoded . "\n";
    }
}

if (empty($missing)) {
    echo "（なし - 全ての画像が存在します）\n";
} else {
    echo "\n合計 " . count($missing) . "個の画像が見つかりません\n";
}
