## pls()
Works with placeholders.

```pls($key = '', $default = '')```

- $key (string|array) - a placeholder to get or an associative array to set.
- $default (mixed) - Default value (if getting) or the options (if setting) - "prefix", "separator" and "restore". Options must be set as an array.

If no arguments are passed the function return an array of all stored placeholders.

#### Get a placeholder
```php
$val = pls('my.Placeholder', 'Default value');
```
#### Set placeholders
```php
# Set placeholder
pls(array('my.Placeholder'=>'value'));

# Set multiple placeholders
pls([
    'my.Placeholder1'=>'value1',
    'my.Placeholder2'=>'value2',
    'my.Placeholder3'=>'value3',
]);

# Use options
pls(['placeholder'=>'value'], [
    'prefix' => 'my',
    'separator' => '_',
]);
// my_placeholder
```