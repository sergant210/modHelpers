## exec_bg_script()
Execute a php script in the background.

```exec_bg_script($script, array $args = [], $escape = true)```

- $script (string) - script name (FQN or relative path). 
- $args (array) - query parameters.
- $escape (bool) - escape an argument.

### Examples
#### Call the function from snippet, plugin or class.
```php
exec_bg_script('action.php', ['task' => 'email', 'param' => 'value']);
```
#### action.php
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

// Get CLI parameters as an array.
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