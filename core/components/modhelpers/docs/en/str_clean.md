## str_clean()
Sanitizes the string - strips HTML tags and removes the specified characters. Similar to ```$modx->sanitizeString```.

```str_clean($str, $chars = '/\'"();><', $allowedTags = array())```
- $str (string) - a source string to clean.
- $chars (string|array) - chars to remove or an array of allowed tags. Magic. Can be omitted.
- $allowedTags (array) - an array of allowed tags.

```php
str_clean('<div>He said: "Hello, fellas!"</div>'); // He said: "Hello, fellas!
str_clean('<div>He said: "Hello, fellas!"</div>',':",!', ['<div>']); // <div>He said Hello fellas</div>

```