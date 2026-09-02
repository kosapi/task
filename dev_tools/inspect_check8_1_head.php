<?php
$data = json_decode(file_get_contents('data/checklist.json'), true);
foreach ($data as $cat) {
    if ($cat['categoryId'] == 8) {
        foreach ($cat['items'] as $idx => $item) {
            if ($item['id'] === 'Check8-1') {
                $html = $item['modalContentHtml'];
                $lines = explode("\n", $html);
                for ($i = 0; $i < min(26, count($lines)); $i++) {
                    echo "$i: " . $lines[$i] . "\n";
                }
            }
        }
    }
}
