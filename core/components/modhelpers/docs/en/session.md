##session()

```session($key, $default)```
- $key - a key. Can be specified using the dot notation.
- $default - a value to set.
```php
# To get data from the session
$value = session('key1.key2'); 
// is equivalent to
$value = $_SESSION['key1']['key2']

# To set data to the session
session('key1.key2', 'value'); 
// is equivalent to
$_SESSION['key1']['key2'] = $value;

# Reset the key
session('key1.key2', null); // isset($_SESSION['key1']['key2'] is false.
```