<?php
require_once __DIR__ . '/assets/SimpleXLSX.php';

use Shuchkin\SimpleXLSX;

if (class_exists('Shuchkin\SimpleXLSX')) {
    echo "✅ SimpleXLSX loaded correctly with namespace!";
} else {
    echo "❌ Still failed to load SimpleXLSX.";
}
