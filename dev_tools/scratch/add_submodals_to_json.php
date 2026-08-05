<?php
$jsonPath = 'c:/xampp/htdocs/task/data/checklist.json';
$data = json_decode(file_get_contents($jsonPath), true);

$subModalsHtml = file_get_contents('c:/xampp/htdocs/task/dev_tools/scratch/extracted_sub_modals.html');

// DOMDocument で extracted_sub_modals.html 内の各モーダルを抽出
$doc = new DOMDocument();
@$doc->loadHTML('<?xml encoding="utf-8" ?>' . mb_convert_encoding($subModalsHtml, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$xpath = new DOMXPath($doc);

$modals = $xpath->query('//div[contains(@class, "modal")]');

$subModalCat = [
    'categoryId' => 99,
    'headingId' => 'headingSubModals',
    'collapseId' => 'collapseSubModals',
    'checkCountId' => 'check-countSubModals',
    'itemsDivId' => 'itemsSubModals',
    'categoryTitle' => 'サブモーダル（共通ポップアップ）',
    'items' => []
];

foreach ($modals as $idx => $m) {
    $mId = $m->getAttribute('id');
    $titleNode = $xpath->query('.//h5[contains(@class, "modal-title")]', $m)->item(0);
    $title = $titleNode ? trim($titleNode->textContent) : $mId;

    $contentHtml = '';
    $modalContent = $xpath->query('.//div[contains(@class, "modal-content")]', $m)->item(0);
    if ($modalContent) {
        foreach ($modalContent->childNodes as $child) {
            $contentHtml .= $doc->saveHTML($child);
        }
    }

    $subModalCat['items'][] = [
        'id' => $mId,
        'name' => $mId,
        'linkId' => 'sublink-' . $mId,
        'targetModalId' => $mId,
        'labelHtml' => htmlspecialchars($title),
        'modalContentHtml' => trim($contentHtml)
    ];
}

// 既存データ内に categoryId === 99 がなければ追加
$exists = false;
foreach ($data as $i => $cat) {
    if ($cat['categoryId'] === 99) {
        $data[$i] = $subModalCat;
        $exists = true;
        break;
    }
}
if (!$exists) {
    $data[] = $subModalCat;
}

file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Added/updated SubModals category (categoryId 99) in checklist.json! Items count: " . count($subModalCat['items']) . "\n";
