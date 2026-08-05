<?php
$jsonPath = 'c:/xampp/htdocs/task/data/checklist.json';
$data = json_decode(file_get_contents($jsonPath), true);

// 親項目とサブモーダルのマッピング辞書を作成
$modalToParent = [];
foreach ($data as $cat) {
    if ($cat['categoryId'] === 99) continue;
    if (isset($cat['items'])) {
        foreach ($cat['items'] as $it) {
            $html = $it['modalContentHtml'] ?? '';
            // HTML内の #modal-... や #Modal... へのリンクを探す
            preg_match_all('/href=["\']#(modal-[a-z0-9-]+|Modal[0-9-]+)["\']/i', $html, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $targetId) {
                    $cleanParentName = strip_tags($it['labelHtml']);
                    $modalToParent[$targetId] = $cleanParentName;
                }
            }
        }
    }
}

// サブモーダル（categoryId 99）を精査・クリーンアップ
foreach ($data as $idx => &$cat) {
    if ($cat['categoryId'] === 99) {
        $cleanItems = [];
        $seenIds = [];

        foreach ($cat['items'] as $item) {
            $body = trim(strip_tags($item['modalContentHtml'] ?? ''));
            // 内容が空（または極端に短い「（内容なし）」等）のものは除外
            if (empty($body) || $body === '内容なし' || strlen($body) < 5) {
                continue;
            }

            // 重複IDの除外（最初に見つかった内容のある正規データを優先）
            if (isset($seenIds[$item['id']])) {
                continue;
            }
            $seenIds[$item['id']] = true;

            // 親項目名の紐付け
            $parentName = $modalToParent[$item['id']] ?? $modalToParent[$item['targetModalId']] ?? '';
            if ($parentName) {
                $item['parentLabel'] = $parentName;
            } else {
                $item['parentLabel'] = '共通ダイアログ';
            }

            $cleanItems[] = $item;
        }

        $cat['items'] = array_values($cleanItems);
        echo "Cleaned SubModals! Remaining valid submodals count: " . count($cleanItems) . "\n";
    }
}

file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "checklist.json successfully updated with parent labels and cleaned!\n";
