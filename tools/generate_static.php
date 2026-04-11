<?php
// CLI utility: generate static HTML from index.html + data/content.json
// Usage: php tools/generate_static.php

// CLI-friendly defaults to avoid warnings from config.php when run from CLI
if (php_sapi_name() === 'cli') {
    if (empty($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = 'localhost';
    if (empty($_SERVER['SERVER_PORT'])) $_SERVER['SERVER_PORT'] = 80;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// decide content file like api/get-content.php
$new_structure_file = DATA_DIR . '/content_new_structure.json';
$legacy_file = CONTENT_FILE;

$content_data = null;
if (file_exists($new_structure_file) && file_exists($legacy_file)) {
    $new_mtime = filemtime($new_structure_file) ?: 0;
    $legacy_mtime = filemtime($legacy_file) ?: 0;
    $content_file = ($new_mtime >= $legacy_mtime) ? $new_structure_file : $legacy_file;
    $content_data = read_json_file($content_file);
} elseif (file_exists($new_structure_file)) {
    $content_data = read_json_file($new_structure_file);
} else {
    $content_data = get_content_data();
}

if (!is_array($content_data)) {
    echo "Error: failed to load content data.\n";
    exit(1);
}

$template_path = dirname(__DIR__) . '/index.html';
if (!file_exists($template_path)) {
    echo "Error: template index.html not found at {$template_path}\n";
    exit(1);
}

$html = file_get_contents($template_path);
if ($html === false) {
    echo "Error: failed to read template.\n";
    exit(1);
}

// Prepare slogans (7 items)
$slogans = array_values(array_slice($content_data['slogans'] ?? [], 0, 7));
while (count($slogans) < 7) $slogans[] = '';

$json = json_encode($slogans, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    echo "Error: failed to encode slogans JSON.\n";
    exit(1);
}

// Replace <script id="slogans-data">...
$newScript = '<script type="application/json" id="slogans-data">' . $json . '</script>';
$html = preg_replace('/<script[^>]*id="slogans-data"[^>]*>.*?<\/script>/s', $newScript, $html, 1, $countScript);
if ($countScript < 1) {
    echo "Warning: slogans-data script marker not found; injecting near <body>.\n";
    $html = preg_replace('/<body>/i', "<body>\n    {$newScript}\n", $html, 1);
}

// Replace weekday slogans (Sun..Sat)
$ids = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
foreach ($ids as $i => $id) {
    $escaped = htmlspecialchars($slogans[$i], ENT_QUOTES, 'UTF-8');
    $pattern = '/(<div[^>]*id="' . preg_quote($id, '/') . '"[^>]*>.*?<p class="text-center">)(.*?)(<\/p>)/s';
    if (preg_match($pattern, $html)) {
        $html = preg_replace($pattern, '$1' . $escaped . '$3', $html, 1, $countDay);
        if ($countDay < 1) {
            echo "Warning: failed to replace content for {$id}.\n";
        }
    }
}

// Find HTML templates in project root to process
$root = dirname(__DIR__);
$htmlFiles = glob($root . '/*.html');
if (!$htmlFiles) {
    echo "No HTML files found in root to process.\n";
    exit(1);
}

$outDir = $root . '/static';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

// Prepare slogans (7 items)
$slogans = array_values(array_slice($content_data['slogans'] ?? [], 0, 7));
while (count($slogans) < 7) $slogans[] = '';
$json = json_encode($slogans, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    echo "Error: failed to encode slogans JSON.\n";
    exit(1);
}
$newScript = '<script type="application/json" id="slogans-data">' . $json . '</script>';
$ids = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

foreach ($htmlFiles as $template_path) {
    $filename = basename($template_path);
    $html = file_get_contents($template_path);
    if ($html === false) {
        echo "Warning: failed to read {$template_path}, skipping.\n";
        continue;
    }

    // Replace slogans-data
    $html = preg_replace('/<script[^>]*id="slogans-data"[^>]*>.*?<\/script>/s', $newScript, $html, 1, $countScript);
    if ($countScript < 1) {
        // inject near body
        $html = preg_replace('/<body>/i', "<body>\n    {$newScript}\n", $html, 1);
    }

    // Replace weekday slogans if present
    foreach ($ids as $i => $id) {
        $escaped = htmlspecialchars($slogans[$i], ENT_QUOTES, 'UTF-8');
        $pattern = '/(<div[^>]*id="' . preg_quote($id, '/') . '"[^>]*>.*?<p class="text-center">)(.*?)(<\/p>)/s';
        if (preg_match($pattern, $html)) {
            $html = preg_replace($pattern, '$1' . $escaped . '$3', $html, 1, $countDay);
        }
    }

    $outFile = $outDir . '/' . $filename;
    $backup = null;
    if (file_exists($outFile)) {
        $backup = $outFile . '.backup_' . date('Ymd_His');
        copy($outFile, $backup);
    }

    $tmp = $outFile . '.tmp.' . getmypid();
    if (file_put_contents($tmp, $html) === false) {
        echo "Error: failed to write temporary file for {$filename}.\n";
        @unlink($tmp);
        continue;
    }
    if (!@rename($tmp, $outFile)) {
        if (!@copy($tmp, $outFile)) {
            @unlink($tmp);
            if ($backup) copy($backup, $outFile);
            echo "Error: failed to move generated file into place for {$filename}.\n";
            continue;
        }
        @unlink($tmp);
    }

    echo "Generated static file: {$outFile}\n";
    if ($backup) echo "Backup: {$backup}\n";
}

echo "Done.\n";
return 0;
