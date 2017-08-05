## object_exists()
Checks the object existence.

```object_exists($className, $criteria = null)```
- $className (string) - class name.
- $criteria (integer|string|array) - Criteria to check.

```php
if (if (object_exists('Ticket', array('published'=>0))) {
    // At least one unpublished ticket exists
}
```