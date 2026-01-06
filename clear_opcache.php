<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!";
} else {
    echo "OPcache is not enabled.";
}
echo "\n\nServer time: " . date('Y-m-d H:i:s');
?>