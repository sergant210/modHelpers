<?php
/**
 * Helpers files autoloader
 */
require_once __DIR__ . '/functions/functions.php';
require_once __DIR__ . '/vendor/autoload.php';

/*$path = __DIR__ . '/classes/';
$dirIterator = new DirectoryIterator($path);

foreach ($dirIterator as $file) {
    if ($file->isFile() && $file->getExtension() == 'php') require_once $file->getPathname();
}*/