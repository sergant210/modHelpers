## session_pull()
Get the value of a given key and then unset it.

```session_pull($key, $flat)```
- $key (string) - session array key. For multi-dimention arrays use the dot notation.
- $flat (bool) - don't parse the key (don't use the dot notation).

```php
# PHP code
...
if ($someError) {
	session(['error.message' => "You'll see this message only once"]);
}
```
and then in HTML code 
```html 
<!-- Example for Fenom template engine -->
...
<div>
    {$.php.session_pull('error.message')} 
</div>
...
```
#### Unset the key
```php
// Use the dot notation
session_pull('key1.key2'); // unset($_SESSION['key1']['key2'])
// Disable the dot notation 
session_pull('key1.key2', true); // unset($_SESSION['key1.key2'])
```