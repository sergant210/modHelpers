<?php
/**
 * Helpers files autoloader
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/functions.php';

$path = __DIR__ . '/classes/';
$files= scandir($path);
foreach ($files as $file) {
    if (preg_match('/.+\.(class|trait)\.php$/',$file)) {
        require_once $path . $file;
    }
}


