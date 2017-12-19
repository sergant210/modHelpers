<?php
/**
 * Helpers files autoloader
 * Not used since version 3.2.0.
 */

require_once __DIR__ . '/vendor/autoload.php';

/*$path = __DIR__ . '/classes/';
$dirIterator = new DirectoryIterator($path);

foreach ($dirIterator as $file) {
    if ($file->isFile() && $file->getExtension() == 'php') require_once $file->getPathname();
}*/