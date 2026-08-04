<?php
$html = file_get_contents('c:/xampp/htdocs/task/index_backup_original.html');
$doc = new DOMDocument();
@$doc->loadHTML('<?xml encoding="utf-8" ?>' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$xpath = new DOMXPath($doc);

// items8 を探す
$items8 = $xpath->query('//div[@id="items8"]')->item(0);
if ($items8) {
    echo "Found items8 element!\n";
    echo $doc->saveHTML($items8);
} else {
    echo "items8 not found by ID. Searching by collapse8 or raw regex...\n";
    preg_match('/id="collapse8".*?<\/div>\s*<\/div>\s*<\/div>/s', $html, $matches);
    if (!empty($matches)) {
        echo "Found collapse8 via regex:\n" . substr($matches[0], 0, 1000) . "\n";
    } else {
        echo "Regex match failed.\n";
    }
}
