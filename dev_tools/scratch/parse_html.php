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
        $formChecks = $xpath->query('.//div[contains(@class, "form-check")]', $itemsDiv);
        foreach ($formChecks as $fc) {
            $input = $xpath->query('.//input[contains(@class, "form-check-input")]', $fc)->item(0);
            if (!$input) continue;
            $checkId = $input->getAttribute('id');
            $checkName = $input->getAttribute('name');

            $link = $xpath->query('.//a', $fc)->item(0);
            $linkId = $link ? $link->getAttribute('id') : "";
            $targetModalId = $link ? str_replace('#', '', $link->getAttribute('data-bs-target')) : "";

            $labelHtml = "";
            if ($link) {
                foreach ($link->childNodes as $child) {
                    $labelHtml .= $doc->saveHTML($child);
                }
            }

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
                'name' => $checkName,
                'linkId' => $linkId,
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
