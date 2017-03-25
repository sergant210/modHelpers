## resource()
Works with a resource object.

```resource($criteria = null, $asObject = true)```
- $criteria(string|integer|array) - Resource id or an array.
- $asObject(bool) - True to return an object. Otherwise - as array.

```php
$resourceObject = resource(15);
$pagetitle = resource(15)->get('pagetitle'); // === resource(15)->pagetitle

// resource() returns a modResource object. So you can use xPDOObject methods:
// toArray(), getOne(), addOne(), save() and so on.
$resourceArray = resource(15)->toArray(); // === resource(15, false)
$pagetitle = resource(15, false)['pagetitle'];

$resource = resource([
            'pagetitle:LIKE' => 'Samsung Note*',
]);

// The current resource
$resourceObject = resource(res_id());
```

### Use the object manager
```php
// The lastest resource
$resourceObject = resource()->last();
$pagetitle = resource()->where(['id'=>1])->get('pagetitle');
// Update
resource()->where(['id'=>1])->set(['pagetitle'=>'New pagetitle']); // returns true or false;
```