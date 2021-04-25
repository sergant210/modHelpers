## default_if()
Returns default value if a given value equals null or the specified value.

```default_if($value, $default, $compared):mixed```
- $value (mixed) - a value or variable to check. 
- $default (mixed) - default value.
- $compared (mixed) - a value to compare instead of null.

It can be used instead of this 
```php
if (!isset($var)) {
    $var = 'default';
}
```
Example 1.
```php
$var = default_if($var, 'default'); 
```
Example 2.
```php
$var = default_if($object->method(), 'default'); 
```
Example 3.
```php
$message = default_if($data, 'The array is empty!', array()); 
// is equivalent to
if (isset($data) && $data == array()) {
	$message = 'The array is empty!';
}
```