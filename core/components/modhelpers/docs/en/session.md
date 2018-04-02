## session()
Manages the session using dot notation.

```session($key = null, $default = null, $flat = false)```
- $key (string|array) - a key. Can be specified using the dot notation. Optional.
- $default (mixed) - default value. When putting a value to the session it works as the $flat parameter. Optional.
- $flat (bool) - don't parse the key. Optional.
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
### Session manager
The session manager is more powerful. It can be got by calling the function without arguments:
```
$session = session();
```
#### Methods
- all() -  Gets all of the session attributes.
- get($key, $default = null,  $flat = false) - Gets an item from the session.
- set($key, $value = null, $flat = false) - Puts a key / value pair or array of key / value pairs in the session. If $key is an array, $value is used instead of $flat.
- push($key, $value) - Push a value onto a session array.
- pull($key) - Gets the value of a given key and then removes it.
- remove($key) - Removes one or many items from the session.
- clear() - Removes all of the items from the session.
- exists($key) - Checks if a key exists.
- has($key) - Checks if a key is present and not null.
- flash($key, $value) - Stores a key / value pair in the session for only one request.
- count() - Returns the number of attributes.
