## str_contains()
Determine if a given string contains a given substring.

```str_contains($haystack, $needles, $case = false)```
- $haystack(string) - the string to search in.
- $needles(string|array) - a substring to search.
- $case(bool) - match the case.

```php
$string = "What a wonderful world!";
return str_contains($string, 'wonderful'); // TRUE
return str_contains($string, 'cruel'); // FALSE

# Use some needles
return str_contains($str, ['wonderful','girl']); // TRUE
```