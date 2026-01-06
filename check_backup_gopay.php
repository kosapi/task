<?php
header('Content-Type: text/plain; charset=utf-8');

// 最新のバックアップをチェック
$backups_dir = 'data/backups/';
$files = glob($backups_dir . '*.html');
rsort($files);

echo "=== 最新10個のバックアップ内のGOPAY参照 ===\n\n";

foreach (array_slice($files, 0, 10) as $backup_file) {
    $html = file_get_contents($backup_file);
    $basename = basename($backup_file);
    
    // GOPAY参照の抽出
    preg_match_all('/src="([^"]*GOPAY[^"]+?\.(?:jpg|jpeg|png|gif))"/', $html, $matches);
    $refs = array_unique($matches[1]);
    
    echo basename($backup_file) . ":\n";
    if (count($refs) > 0) {
        foreach (array_values($refs) as $ref) {
            echo "  - " . htmlspecialchars_decode($ref) . "\n";
        }
    } else {
        echo "  (参照なし)\n";
    }
    echo "\n";
}
?>
