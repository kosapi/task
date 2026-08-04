<?php
$json = file_get_contents('c:/xampp/htdocs/task/data/checklist.json');
$data = json_decode($json);
if ($data === null) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "Valid JSON: " . count($data) . " categories loaded successfully.\n";
}
