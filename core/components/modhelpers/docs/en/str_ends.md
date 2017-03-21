## str_ends()
Determine if a given string ends with a given substring.

```str_ends($haystack, $needles, $case = false)```
- $haystack(string) - the string to search in.
- $needles(string|array) - a substring to search.
- $case(bool) - match the case.

```php
$str = "Hello, John!";
return str_ends($str, 'john!'); // TRUE

# Use some needles
return str_ends($str, ['james!','jane!','john!']); // TRUE

# Match the case
return str_ends($str, 'john!', true); // FALSE
```