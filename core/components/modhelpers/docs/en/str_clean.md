## str_clean()
Sanitizes the string. Similar to ```$modx->sanitizeString```.

* [str_clean()](./core/components/modhelpers/docs/en/str_clean.md) - sanitize the string. Similar to ```$modx->sanitizeString```.

```str_clean($str, $chars = '/\'"();><', $allowedTags = array())```
- $str (string) - a source string to clean.
- $chars (string|array) - chars to remove or an array of allowed tags. Magic.
- $allowedTags (array) - an array of allowed tags.

```php
str_clean('<div>He said: "Hello, fellas!"</div>'); // He said: "Hello, fellas!
str_clean('<div>He said: "Hello, fellas!"</div>',':",!', ['<div>']); // <div>He said Hello fellas</div>

```