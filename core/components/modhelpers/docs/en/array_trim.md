## array_trim(), array_ltrim(), array_rtrim()
Strip whitespace (or other characters) from the beginning and end of an array values. Can be applies for multi-dimensional array.

```array_trim($value, $chars = '', $func = 'trim')```
- $value(array|string) - an array or string to trim.
- $chars(string) - list of characters that will be stripped.
- $func(string) - The name of function. Optional

```php
$array = array(
    'key1' => '/    Value 1 ',
    'key2' => '  Value 2      /',
    'key3' => array(
        '       Sub Array Value 1    ', 
        '/  Sub Array Value 2/'
    )
);

return array_trim($array, ' /');
// Output
Array
(
    [key1] => 'Value 1'
    [key2] => 'Value 2'
    [key3] => 'Array'
        (
            [0] => 'Sub Array Value 1'
            [1] => 'Sub Array Value 2'
        )
)
```
Example for array_rtrim().
```php
Array
(
    [key1] => '/    Value 1'
    [key2] => '  Value 2'
    [key3] => 'Array'
        (
            [0] => '       Sub Array Value 1'
            [1] => '/  Sub Array Value 2'
        )
)
```
Example for array_ltrim().
```php
Array
(
    [key1] => 'Value 1 '
    [key2] => 'Value 2      /'
    [key3] => 'Array'
        (
            [0] => 'Sub Array Value 1    '
            [1] => 'Sub Array Value 2/'
        )
)
```