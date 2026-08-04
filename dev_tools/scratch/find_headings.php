<?php
$html = file_get_contents('c:/xampp/htdocs/task/index_backup_original.html');
$doc = new DOMDocument();
@$doc->loadHTML('<?xml encoding="utf-8" ?>' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$xpath = new DOMXPath($doc);

$headings = $xpath->query('//h2[contains(@class, "accordion-header")]');
foreach ($headings as $idx => $h) {
    echo "Heading {$idx}: " . trim($h->textContent) . "\n";
}
