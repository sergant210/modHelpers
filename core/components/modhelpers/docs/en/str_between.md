## str_between()
Gets a substring between two tags.

```str_between($string, $start, $end, $greedy = true)```
- $string(string) - the input string.
- $start(string) - start tag.
- $end(string) - end tag.
- $greedy(bool) - greedy mode.

```php
$string = "My first name is [[+firstname]] and my last name is [[+lastname]]. ";
// Greedy mode
echo str_between($string,'[[+',']]'); // firstname]] and my last name is [[+lastname
// Not greedy mode (lazy)
echo str_between($string,'[[+',']]', false); // firstname

$string = "<p>Lorem ipsum <span>dolor</span> sit amet</p>";
echo str_between($string,'<span>','</span>'); // dolor
```