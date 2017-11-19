<?php

switch ($modx->event->name) {
    case 'OnMODXInit':
        $file = $modx->getOption('modhelpers_core_path', null, MODX_CORE_PATH) . 'components/modhelpers/autoload.php';
        if (file_exists($file)) {
            require_once $file;
            app()->singleton('detector', 'Mobile_Detect');
            app()->instance('modx', $modx);
            app()->singleton('request', function() {
                /** @var modHelpers\Request $requestClass */
                $requestClass = config('modhelpers_requestClass', 'modHelpers\Request', true);
                return $requestClass::capture();
            });
            csrf_token();
        }
        break;
}