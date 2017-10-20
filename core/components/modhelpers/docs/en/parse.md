## parse()  
Parses a string using an associative array of replacement variables. It can be using in two modes - simple and full. The simple mode is similar to the ```modx::parseChunk()``` method. The full mode uses the current MODX parser. 

```parse($string, $data, $prefix = '[[+', $suffix = ']]')```
- $string (string) - Source string to parse. Required.
- $data (array) - An array of placeholders to replace. Optional.
- $prefix (string|bool) Magic. The placeholder prefix for the simple mode or flag for complete parsing. Optional.
- $suffix (string) - Magic. The placeholder suffix (for simple mode) or the maximum iterations to recursively process tags (for full mode). Optional.
 
```php
# Simple mode (by default)
foreach($userArray as $user) {
    $output .= parse('<p>id: [[+id]]</p><p>Username: [[+username]]</p>');
}

# Full mode
$tpl = 'String with a chunk placeholder -  [[$chunk]].';
$output = parse($tpl, $data, true, 5);
```
