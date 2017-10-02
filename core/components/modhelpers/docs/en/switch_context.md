## switch_context()  
Switches the context according to the conditions.

```switch_context($key, $excluded = array())```
 - $key (string|array) - Context key or an array of conditions.
 - $excluded (array) - Excluded contexts.
 Returns True if the switch was successful, otherwise false.
 
#### Simple example
```php
// Plugin (OnHandleRequest)

switch_context('context_key'); // equivalent to $modx->switchContext();
```
#### Switch to "subdomain" context.
```php
// Plugin (OnHandleRequest)

if (switch_context(['http_host' => $_SERVER['HTTP_HOST'])) {
    // Switched successfully
}
```
#### Switch to "folder" context.
```php
// Plugin (OnHandleRequest)

if (switch_context(['base_url' => request()->segment(1))) {
    // Switched successfully
}
```
