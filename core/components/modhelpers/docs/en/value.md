## value
Returns the default value of the given value or Closure if they are null.

```value($value, $default = null):mixed```

- (mixed) $value - a value or a Closure to check. 
- (mixed) $default - default value.
  
### Pass a variable
```php
$foo = 'bar';
$someVar = null;

value($foo, 'default value');  // Output: 'bar'.
value($someVar, 'default value');  // Output: 'default value'.
```

### Pass a Closure
```php
$func = function() {
// some calculation
return $result;
};

value($func, 'default value');
```
