## optional()
Provide access to optional objects.

```optional()```

```php
$nullObj = null;
// Error
$nullObj->property;  // trying to get property of non-object
// Using the optional function prevent this error
optional($nullObj)->property; // null
```