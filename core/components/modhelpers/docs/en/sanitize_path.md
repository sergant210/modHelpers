## sanitize_path
Removes "../" from a specified path.

```sanitize_path($path):string```

- (string) $path - Path to sanitize. 
  
### 
```php
sanitize_path('../../path/to/file.php'); 
// Output: '/path/to/file.php'
```