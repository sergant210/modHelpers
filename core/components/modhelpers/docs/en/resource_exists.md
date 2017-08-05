## resource_exists()
Checks if the specified resource exists.

```resource_exists($criteria = null)```
- $criteria (integer|array) - Criteria to check.

```php
if (resource_exists(100)) {
    // The resource with id=100 exists
}
if (resource_exists(['alias'=>'page100'])) {
    // The resource with the specified alias exists
}
```