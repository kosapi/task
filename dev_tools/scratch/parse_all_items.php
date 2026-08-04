<?php
$html = file_get_contents('c:/xampp/htdocs/task/index_backup_original.html');

$doc = new DOMDocument();
@$doc->loadHTML('<?xml encoding="utf-8" ?>' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$xpath = new DOMXPath($doc);

$headings = $xpath->query('//h2[contains(@class, "accordion-header")]');
$categories = [];

foreach ($headings as $idx => $heading) {
    $headingId = $heading->getAttribute('id');
    $numMatch = [];
    preg_match('/\d+/', $headingId, $numMatch);
    $catNum = isset($numMatch[0]) ? $numMatch[0] : $idx;

    $titleNode = $xpath->query('.//span[contains(@class, "accordion-title")]', $heading)->item(0);
    $catTitle = $titleNode ? trim($titleNode->textContent) : "Category " . $catNum;

    $collapseId = "collapse" . $catNum;
    $itemsDivId = "items" . $catNum;
    $checkCountId = "check-count" . $catNum;

    $catData = [
        'categoryId' => (int)$catNum,
        'headingId' => $headingId,
        'collapseId' => $collapseId,
        'checkCountId' => $checkCountId,
        'itemsDivId' => $itemsDivId,
        'categoryTitle' => $catTitle,
        'items' => []
    ];

    $itemsDiv = $xpath->query('//div[@id="' . $itemsDivId . '"]')->item(0);

    if ($itemsDiv) {
        // 全ての <a data-bs-toggle="modal"> を取得
        $links = $xpath->query('.//a[@data-bs-toggle="modal"]', $itemsDiv);
        foreach ($links as $itemIdx => $link) {
            $linkId = $link->getAttribute('id');
            $targetModalId = str_replace('#', '', $link->getAttribute('data-bs-target'));
            
            // チェックボックスIDを探す（直近のinputまたは親のform-check内のinput）
            $checkId = "Check" . $catNum . "-" . ($itemIdx + 1);
            $parentFc = $xpath->query('./ancestor::div[contains(@class, "form-check")]', $link)->item(0);
            if ($parentFc) {
                $input = $xpath->query('.//input[contains(@class, "form-check-input")]', $parentFc)->item(0);
                if ($input && $input->getAttribute('id')) {
                    $checkId = $input->getAttribute('id');
                }
            }

            // ラベルのHTMLを取得
            $labelHtml = "";
            foreach ($link->childNodes as $child) {
                $labelHtml .= $doc->saveHTML($child);
            }

            // 対応するモーダルのHTMLを取得
            $modalNode = $xpath->query('//div[@id="' . $targetModalId . '"]')->item(0);
            $modalContentHtml = "";
            if ($modalNode) {
                $modalContent = $xpath->query('.//div[contains(@class, "modal-content")]', $modalNode)->item(0);
                if ($modalContent) {
                    foreach ($modalContent->childNodes as $child) {
                        $modalContentHtml .= $doc->saveHTML($child);
                    }
                }
            }

            $catData['items'][] = [
                'id' => $checkId,
                'name' => $checkId,
                'linkId' => $linkId ?: "M" . $catNum . "-" . ($itemIdx + 1),
                'targetModalId' => $targetModalId,
                'labelHtml' => trim($labelHtml),
                'modalContentHtml' => trim($modalContentHtml)
            ];
        }
    }

    $categories[] = $catData;
}

file_put_contents(
    'c:/xampp/htdocs/task/data/checklist.json',
    json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

echo "Successfully exported " . count($categories) . " categories to data/checklist.json\n";
foreach ($categories as $c) {
    echo " - " . $c['categoryTitle'] . " (" . count($c['items']) . " items)\n";
}
