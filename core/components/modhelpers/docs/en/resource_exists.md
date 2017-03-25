## resource_exists()
Checks if the specified user exists

```user_exists($criteria = null)```
- resource_exists(integer|array) - Criteria to check.

```php
if (resource_exists(100) {
    // The resource with id=100 exists
}
if (resource_exists(['alias'=>'page100']) {
    // The resource with the specified alias exists
}
```