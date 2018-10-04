## tag_decode()
Decodes HTML codes back to MODX tag chars.

```tag_decode($string, array $chars = array ("[", "]", "{" , "}" , "`"))```
- $string(string) - A string to filters. Require.
- $chars(array) - MODX tag chars to decode. Optional.

```php
$string = tag_decode('Some MODX &#91;&#91;tag&#93;&#93;'); // Some MODX [[tag]]
```