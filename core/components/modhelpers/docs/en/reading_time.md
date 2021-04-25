## reading_time
Gets the estimated time to read the specified content.

```reading_time($content, $wpm = 200):int```

- (string) $content - Content to count. 
- (int) $wpm - Words per minute. By default, 200.
  

```php
$time = reading_time($modx->resource->getContent());
```