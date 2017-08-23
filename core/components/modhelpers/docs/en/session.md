## session()
Manages the session using dot notation.

```session($key = null, $default = null, $flat = false)```
- $key (string|array) - a key. Can be specified using the dot notation.
- $default (mixed) - default value. When putting a value to the session it works as the $flat parameter.
- $flat (bool) - don't parse the key.
#### To get data from the session
```php
# Get data using the dot notation
$value = session('key1.key2'); 
// is equivalent to
$value = $_SESSION['key1']['key2']

# Disable the dot notation parsing
$value = session('key1.key2', '', true); 
// is equivalent to
$value = isset($_SESSION['key1.key2']) ? $_SESSION['key1.key2'] : '';
```
#### To set data to the session
```php
# Set values to the session ising the dot notation.
session(['key1.key2' => 'value']); 
// is equivalent to
$_SESSION['key1']['key2'] = $value;

# Disable the dot notation parsing
session(['key1.key2' => 'value'], true); 
// is equivalent to
$_SESSION['key1.key2'] = $value;
```
#### Reset the key
```php
session(['key1.key2' => null]); // $_SESSION['key1']['key2'] = null.
```