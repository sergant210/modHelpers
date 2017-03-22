## str_limit()
Limits the number of characters in a string.

```str_limit($string, $limit = 100, $end = '...')```
- $string(string) - the input string.
- $start(integer) - number of characters to output.
- $end(string) - the ending.

```php
$str = "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus accusantium asperiores, consectetur cumque dolores ex hic, nam omnis, placeat provident quas quos sapiente similique tempora tempore vero vitae voluptates voluptatum?";
echo str_limit($str,50); // Lorem ipsum dolor sit amet, consectetur adipisi...
```