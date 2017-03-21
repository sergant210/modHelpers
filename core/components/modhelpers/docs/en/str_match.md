## str_match()
Determine if a given string matches a given pattern.

```str_match($value, $pattern, $case = false)```
- $haystack(string) - the input string.
- $needles(string) - pattern. Asterisks may be used to indicate wildcards.
- $case(bool) - match the case.

```php
$string = "What a wonderful world!";
return str_match($string, '*wonder*'); // TRUE
return str_match($string, 'wonder*'); // FALSE
return str_match($string, '*Wonder*', true); // FALSE
```