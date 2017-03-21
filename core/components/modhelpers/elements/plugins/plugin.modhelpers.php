<?php

switch ($modx->event->name) {
    case 'OnMODXInit':
        $file = $modx->getOption('modhelpers_core_path', null, MODX_CORE_PATH) . 'components/modhelpers/autoload.php';
        if (file_exists($file)) {
            require_once $file;
        }
        break;
}