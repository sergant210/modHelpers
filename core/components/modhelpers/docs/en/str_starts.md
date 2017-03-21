## str_starts()
Determine if a given string starts with a given substring. 

```str_starts($haystack, $needles, $case = false)```
- $haystack(string) - the string to search in.
- $needles(string|array) - a substring to search.
- $case(bool) - match the case.

```php
$str = "Hello, John!";
return str_starts($str, 'he'); // TRUE

# Match the case
return str_starts($str, 'he', true); // FALSE

# Use some needles
return str_starts($str, ['he','hi']); // TRUE
```