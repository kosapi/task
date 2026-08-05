<?php
$html = file_get_contents('c:/xampp/htdocs/task/dev_tools/index_backup_original.html');
$pos = strrpos($html, 'id="accordion"');
if ($pos !== false) {
    // accordion 付近から footer までの HTML
    echo substr($html, $pos + 100, 3000);
}
