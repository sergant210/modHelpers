##script()
Registers JavaScript to be injected inside the HEAD tag or before the closing BODY tag. Available the script attributes "async" and "defer" or other attributes.

```script($src, $start = false, $plaintext = false, $attr = false)```
- $src - a javascript code or path to js file to inject.
- $start - True to inject inside the HEAD tag of the page. False to injected before the closing BODY tag. Magic.
- $plaintext - a flag to treat the $src as plaintext. Magic.
- $attr - optional attributes for tag script (async, defer, id="script-id" and so on). Can be pass instead of the second or the third arguments. 

```php
script('assets/js/main.js'); 
# Inject inside the <head>.
script('<script>var params = ' . json_encode($params) . ';</script>', true); 

# Inject to the end of page and set the attribute async.
script('/path/to/script.js', 'async'); // <script async type="text/javascript" src="/path/to/script.js"></script>
# Inject inside the <head> and set the attribute defer.
script('/path/to/script.js', true, 'defer'); // <script defer type="text/javascript" src="/path/to/script.js"></script>
```
