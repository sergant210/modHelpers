## null_if()  
Returns NULL if the given values are equal.

```null_if($value, $compared = '')```
 - $value - A given value.
 - $compared - Comparison value.
 
```php
$var = '';
...
$var = null_if($var);  // $var = NULL

# Specify the comparision value
$var = array();
...
$var = null_if($var, []);  // $var = NULL
```
