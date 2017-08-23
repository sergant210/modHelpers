## explode_trim(), explode_ltrim(), explode_rtrim()  
Split a string and strip whitespace (or other characters) from the beginning and the end of an array values. 

```explode_trim($delimiter, $string, $chars = '', $func = 'trim')```
- $delimiter(string) - the boundary string.
- $string(string) - the input string.
- $chars(string) - list of characters that will be stripped.
- $func(string) - The name of function. Optional.

```php
$string = '/string1 /,    string2      /,/      string3';
# Trim the '/' symbol
explode_trim(',', $string, '/');
// Output
Array
(
    [0] => 'string1 '
    [1] => '    string2      '
    [2] => '      string3'
)

# Trim whitespace and '/'
explode_trim(',', $string, ' /');
// Output
Array
(
    [0] => 'string1'
    [1] => 'string2'
    [2] => 'string3'
)
```
#### Example for explode_ltrim().
```php
return explode_ltrim(',', $string, '/ ');
// Output
Array
(
    [0] => 'string1 /'
    [1] => 'string2      /'
    [2] => 'string3'
)
```
#### Example for explode_rtrim().
```php
return explode_rtrim(',', $string, '/ ');
// Output
Array
(
    [0] => '/string1'
    [1] => '    string2'
    [2] => '/      string3'
)
```