##pls()
Works with placeholders.

```pls($key = '', $default = '')```

- $key (string|array) - a placeholder to get or an associative array to set.
- $default (mixed) - Default value if the placeholder does not exist.

If no arguments are passed the function return an array of all stored placeholders.
```php
# Get a placeholder
$val = pls('my.Placeholder', 'Default value');

# Set placeholder
pls(array('my.Placeholder'=>'value'));

# Set multiple placeholders
pls([
    'my.Placeholder1'=>'value1',
    'my.Placeholder2'=>'value2',
    'my.Placeholder3'=>'value3',
]);
```