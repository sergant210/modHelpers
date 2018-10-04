## tag_encode()
Converts MODX tag chars to corresponding HTML codes. It can be useful to filter the request data.

```tag_encode($string, array $chars = array ("[", "]", "{" , "}" , "`"))```
- $string(string) - A string to filters. Require.
- $chars(array) - MODX tag chars to convert. Optional.

```php
$string = tag_encode('Some MODX [[tag]]'); // Some MODX &#91;&#91;tag&#93;&#93;
```
Filter the POST data:
```php
foreach ($_POST as $key => &$value) {
	if (is_string($value)) {
		$value = tag_encode($value);
	}
}
```