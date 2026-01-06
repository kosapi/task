<?php
$images = [
    '%E5%B1%8B%E6%A0%B9%E4%B8%8B%E5%BB%BA%E7%89%A9%E5%81%B4%E5%A5%A5%E9%9A%85.jpg',
    '%E5%B1%8B%E6%A0%B9%E4%B8%8B%E3%83%AA%E3%83%95%E3%83%88%E5%81%B4%E5%A5%A5%E9%9A%85.jpg',
    '%E5%B1%8B%E6%A0%B9%E4%B8%8B%E3%83%AA%E3%83%95%E3%83%88%E5%81%B4%E9%81%93%E5%81%B4.jpg',
    '%E5%BB%BA%E7%89%A9%E5%81%B4%E5%85%A5%E5%8F%A3.jpg',
    '%E6%95%B4%E5%82%99%E5%B7%A5%E5%A0%B4%E5%89%8D.jpg',
    '%E6%80%A5%E9%80%9F%E5%85%85%E9%9B%BB%E5%99%A8%E3%81%A8%E3%81%AA%E3%82%8A.jpg',
    '%E9%A7%90%E8%BC%AA%E5%A0%B4.jpg',
    '%E3%83%90%E3%83%83%E3%82%AF%E9%A7%90%E8%BB%8A.jpg'
];

echo "=== 駐車場関連画像のチェック ===\n\n";

foreach($images as $img) {
    $decoded = rawurldecode($img);
    echo "ファイル名: " . $decoded . "\n";
    
    if (file_exists($decoded)) {
        echo "  場所: ROOTフォルダー\n";
        echo "  ✅ 存在\n";
    } elseif (file_exists('img/' . $decoded)) {
        echo "  場所: imgフォルダー\n";
        echo "  ✅ 存在\n";
    } else {
        echo "  ❌ 見つかりません\n";
        echo "  必要な修正: HTMLのパスを修正するか、画像をROOTかimgフォルダーに配置\n";
    }
    echo "\n";
}
