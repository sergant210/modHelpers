## dd() 
Dump the passed variables. You can call it only one time.

```dd($var)```
- $var(mixed) - Variable to dump.
  
```php
dd($modx->user, $array, $string, $bool);
```

### Themes
There are 2 themes for the dump result - dark and light. The dark theme is default. To change it create the "modhelpers_debug_theme" system setting and put "light" in it.