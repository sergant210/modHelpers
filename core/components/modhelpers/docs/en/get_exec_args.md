## get_exec_args()
Prepare exec function arguments as an array. Intended for use with exec_bg_script().

```get_exec_args() :array```

#### Connector example
```php
<?php
// Allow only CLI requests
if (php_sapi_name() != 'cli') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// Init MODX
define('MODX_API_MODE', true);
require_once __DIR__.'/index.php';
$modx->getService('error','error.modError');

// Get exec arguments
$_REQUEST = get_exec_args();

// Process tasks
switch ($_REQUEST['task']) {
    case 'email':
        email(...);
        break;
    case 'thumb':
        // make a thumbnail
        break;
}
```