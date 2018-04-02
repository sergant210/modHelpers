## script()
Registers JavaScript to be injected inside the HEAD tag or before the closing BODY tag. Available the script attributes "async" and "defer" or other attributes.

```script($src, $start = false, $plaintext = false, $attr = false)```
- $src (string) - javascript code or the path to js file to inject.
- $start (bool) - True to inject inside the HEAD tag of the page. False to inject before the closing BODY tag. Magic.
- $plaintext (bool) - flag to treat the $src as plaintext. Magic.
- $attr (string|array) - optional attributes for tag script (async, defer, id, class and so on). Can be pass instead of the second or the third arguments. 

```php
script('assets/js/main.js'); 
# Inject inside the <head>.
script('<script>var params = ' . json_encode($params) . ';</script>', true); 

# Inject to the end of page and pass the attribute async as the second argument.
script('/path/to/script.js', 'async'); // <script async src="/path/to/script.js"></script>
# Inject inside the <head> and pass an array of attributes as the third argument. 
script('/path/to/script.js', true, ['async', 'defer', 'id'=>'some-id', class="some classes"]); // <script async defer id="some-id" class="some classes" src="/path/to/script.js"></script>
```
