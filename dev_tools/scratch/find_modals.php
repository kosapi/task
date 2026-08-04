<?php
$html = file_get_contents('c:/xampp/htdocs/task/index_backup_original.html');
$doc = new DOMDocument();
@$doc->loadHTML('<?xml encoding="utf-8" ?>' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$xpath = new DOMXPath($doc);

// 全ての modal fade を取得
$modals = $xpath->query('//div[contains(@class, "modal")]');
echo "Total modals in original: " . $modals->length . "\n";

foreach ($modals as $m) {
    $id = $m->getAttribute('id');
    $title = $xpath->query('.//*[contains(@class, "modal-title")]', $m)->item(0);
    $tText = $title ? trim($title->textContent) : "";
    if (mb_strpos($tText, '納金') !== false || mb_strpos($tText, 'チケット') !== false || mb_strpos($tText, '福祉') !== false || mb_strpos($id, 'modal-') !== false) {
        echo "ID: {$id} | Title: {$tText}\n";
    }
}
