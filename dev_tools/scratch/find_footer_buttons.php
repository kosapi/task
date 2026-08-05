<?php
$html = file_get_contents('c:/xampp/htdocs/task/dev_tools/index_backup_original.html');
$doc = new DOMDocument();
@$doc->loadHTML('<?xml encoding="utf-8" ?>' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$xpath = new DOMXPath($doc);

// <a href="..." で btn クラスがあるものや <button> を探す
$nodes = $xpath->query('//div[contains(@class, "container") or contains(@class, "footer") or contains(@class, "row")]//a[contains(@class, "btn")]');
echo "Found " . $nodes->length . " btn links:\n";
foreach ($nodes as $node) {
    echo "- " . trim($node->textContent) . " (href: " . $node->getAttribute('href') . ")\n";
}
