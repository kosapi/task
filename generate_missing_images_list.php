<?php
$html = file_get_contents('index.html');

// 全ての画像パスを抽出（src、href、data-srcなど）
preg_match_all('/(src|href|data-src|data-image)\s*=\s*["\']([^"\']+\.(jpg|jpeg|png|gif|webp|svg|pdf))["\']/', $html, $matches);

$image_paths = array_unique($matches[2]);
$missing_images = [];
$found_images = [];

echo "=== 消えた画像の一覧 ===\n\n";

foreach($image_paths as $path) {
    // 相対パスを絶対パスに変換
    $decoded_path = rawurldecode($path);
    
    // パスの種類を判定
    if (strpos($decoded_path, 'img/') === 0) {
        $file = $decoded_path;
        $location = 'img/';
    } elseif (strpos($decoded_path, 'uploads/') === 0) {
        $file = $decoded_path;
        $location = 'uploads/';
    } elseif (strpos($decoded_path, 'http') === 0 || strpos($decoded_path, '//') === 0) {
        // 外部URLはスキップ
        continue;
    } else {
        // ルートフォルダー
        $file = $decoded_path;
        $location = 'root';
    }
    
    // ファイルの存在確認
    if (!file_exists($file)) {
        $missing_images[] = [
            'original' => $path,
            'decoded' => $decoded_path,
            'file' => $file,
            'location' => $location
        ];
    } else {
        $found_images[] = $file;
    }
}

echo "消えた画像: " . count($missing_images) . "個\n";
echo "存在する画像: " . count($found_images) . "個\n\n";

if (!empty($missing_images)) {
    echo "=== 詳細リスト ===\n\n";
    
    $by_location = [];
    foreach($missing_images as $img) {
        $by_location[$img['location']][] = $img;
    }
    
    foreach($by_location as $location => $images) {
        echo "【{$location}】\n";
        foreach($images as $img) {
            echo "  ファイル名: " . $img['decoded'] . "\n";
            if ($img['original'] !== $img['decoded']) {
                echo "  (HTML内: " . $img['original'] . ")\n";
            }
        }
        echo "\n";
    }
    
    // CSVファイルとしても出力
    $csv_content = "場所,ファイル名,HTML内のパス\n";
    foreach($missing_images as $img) {
        $csv_content .= '"' . $img['location'] . '","' . $img['decoded'] . '","' . $img['original'] . '"' . "\n";
    }
    file_put_contents('missing_images_list.csv', $csv_content);
    echo "CSVファイルも作成しました: missing_images_list.csv\n\n";
    
    // テキストファイルとしても出力（復元用）
    $txt_content = "=== 消えた画像の一覧 (" . date('Y-m-d H:i:s') . ") ===\n\n";
    $txt_content .= "合計: " . count($missing_images) . "個\n\n";
    
    foreach($by_location as $location => $images) {
        $txt_content .= "【{$location}】\n";
        foreach($images as $img) {
            $txt_content .= "  - " . $img['decoded'] . "\n";
        }
        $txt_content .= "\n";
    }
    
    file_put_contents('missing_images_list.txt', $txt_content);
    echo "テキストファイルも作成しました: missing_images_list.txt\n";
    
} else {
    echo "すべての画像が存在します！\n";
}
