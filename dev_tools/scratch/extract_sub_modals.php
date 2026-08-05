<?php
$orig = file_get_contents('c:/xampp/htdocs/task/dev_tools/index_backup_original.html');

$modals = [
    'modal-ticket-welfare',
    'modal-welfare-many',
    'modal-additional-uncollected',
    'modal-teito-cancel',
    'modal-go-app-cancel',
    'modal-meter-mistake',
    'modal-etc-statement'
];

$subModalsHtml = '';
foreach ($modals as $mId) {
    preg_match('/<div class="modal fade" id="' . $mId . '".*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/s', $orig, $match);
    if (!empty($match[0])) {
        $subModalsHtml .= $match[0] . "\n\n";
    } else {
        echo "Warning: {$mId} not matched via regex.\n";
    }
}

file_put_contents('c:/xampp/htdocs/task/dev_tools/scratch/extracted_sub_modals.html', $subModalsHtml);
echo "Extracted sub-modals successfully! Length: " . strlen($subModalsHtml) . "\n";
