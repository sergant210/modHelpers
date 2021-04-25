## dump() 
Dump the passed variables. You can call it as many times as you want.

```dump($var1, $var2, ...)```
- $var(mixed) - Variable to dump.
  
```php
# 1.
dump($var);
# 2.
dump($modx->user, $array, $string, $bool);
```

### Themes
There are 2 themes for the dump result - dark and light. The dark theme is default. To change it create the "modhelpers_debug_theme" system setting and put "light" in it.