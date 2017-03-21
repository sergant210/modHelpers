<?php
/**
 * Helpers files autoloader
 */
$classes = array(
    'modHelpersLogger',
    'modHelpersObjectManager',
    'modHelpersCollectionManager',
    'modHelpersCacheManager',
    'modHelpersMailer',
    'modHelpersModelBuilder',
    'modHelpersModelColumn',
    'modHelpersQueryManager',
    //'modhelpersqueue.class.php',
);
foreach ($classes as $class) {
    $file = __DIR__ . '/classes/' . strtolower($class) . '.class.php';
    if (!class_exists($class) && file_exists($file)) require_once $file;
}

require_once __DIR__ . '/vendor/fzaninotto/faker/src/autoload.php';
require_once __DIR__ . '/functions/functions.php';