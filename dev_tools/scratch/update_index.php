<?php
$orig = file_get_contents('c:/xampp/htdocs/task/index_backup_original.html');

$scriptTag = '<script src="js/checklist-render.js?v=20260329_001" defer=""></script>' . "\n";
if (strpos($orig, 'js/checklist-render.js') === false) {
    $orig = str_replace('<script src="js/init.js', $scriptTag . '  <script src="js/init.js', $orig);
}

$startPos = strpos($orig, '<div class="accordion" id="accordion">');
$endPos = strrpos($orig, '<!-- フッター -->');

if ($startPos !== false && $endPos !== false) {
    $before = substr($orig, 0, $startPos);
    $after = substr($orig, $endPos);
    
    // フッター内の更新日を本日の日付に更新
    $todayStr = date('Y年m月d日');
    $after = preg_replace('/(<small>更新日:\s*<time>)(.*?)(<\/time><\/small>)/u', '${1}' . $todayStr . '${3}', $after);

    $newHtml = $before . '<div class="accordion" id="accordion"></div>' . "\n\n      " . $after;
    file_put_contents('c:/xampp/htdocs/task/index.html', $newHtml);
    echo "index.html clean build complete! Date updated to {$todayStr}\n";
}
