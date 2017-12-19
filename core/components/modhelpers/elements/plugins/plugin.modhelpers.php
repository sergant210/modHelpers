<?php

switch ($modx->event->name) {
    case 'OnMODXInit':
        $loader = $modx->getOption('modhelpers_core_path', null, MODX_CORE_PATH) . 'components/modhelpers/vendor/autoload.php';
        if (file_exists($loader)) {
            require_once $loader;
            app()->singleton('detector', 'Mobile_Detect');
            app()->instance('modx', $modx);
            app()->singleton('request', function() {
                /** @var modHelpers\Request $requestClass */
                $requestClass = config('modhelpers_requestClass', 'modHelpers\Request', true);
                return $requestClass::capture();
            });
            app()->singleton('response', function() use ($modx) {
                /** @var modHelpers\ResponseManager $manager */
                $manager = config('modhelpers_responseManager', 'modHelpers\ResponseManager', true);
                return new $manager($modx);
            });
            csrf_token();

            $file = $modx->getOption('modhelpers_core_path', null, MODX_CORE_PATH) . 'components/modhelpers/config/config.php';
            if (file_exists($file)) $config = include_once $file;
            if (array_notempty($config)) config($config);
        }
        break;
}