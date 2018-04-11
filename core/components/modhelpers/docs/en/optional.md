## optional()
Provide access to optional objects.

```optional()```

```php
$nullObj = null;
// Fatal error
$nullObj->method();  // Call to a member function method() on null
// Using the optional function prevent this error
optional($nullObj)->method(); // null
```