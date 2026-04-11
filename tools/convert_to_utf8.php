<?php
// Usage: php convert_to_utf8.php /full/path/to/file
if ($argc < 2) {
    echo "Usage: php convert_to_utf8.php /full/path/to/file\n";
    exit(1);
}
$file = $argv[1];
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}
$orig = file_get_contents($file);
$bak = $file . '.bak_' . date('Ymd_His');
copy($file, $bak);
echo "Backup created: $bak\n";

$candidates = ["UTF-8","SJIS-win","EUC-JP","ISO-2022-JP","CP932","Windows-1252"];
// If already valid UTF-8, keep as-is
if (mb_check_encoding($orig, 'UTF-8')) {
    echo "File already valid UTF-8. No change made.\n";
    exit(0);
}

foreach ($candidates as $enc) {
    if ($enc === 'UTF-8') continue;
    $converted = @mb_convert_encoding($orig, 'UTF-8', $enc);
    if ($converted === false) continue;
    // check for presence of Japanese characters
    if (preg_match('/[\x{3040}-\x{30FF}\x{4E00}-\x{9FFF}]/u', $converted)) {
        file_put_contents($file, $converted);
        echo "Converted from $enc to UTF-8 and wrote: $file\n";
        exit(0);
    }
}

// Fallback: try iconv permutations
$froms = ['SJIS','CP932','EUC-JP','ISO-2022-JP','Windows-1252'];
foreach ($froms as $from) {
    $converted = @iconv($from, 'UTF-8//IGNORE', $orig);
    if ($converted === false) continue;
    if (preg_match('/[\x{3040}-\x{30FF}\x{4E00}-\x{9FFF}]/u', $converted)) {
        file_put_contents($file, $converted);
        echo "Converted from $from to UTF-8 (iconv) and wrote: $file\n";
        exit(0);
    }
}

echo "No confident conversion found. File left unchanged. You can inspect $bak and try conversions manually.\n";
exit(2);
