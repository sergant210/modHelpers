##config()

```config($key, $default)```

- $key - a system settings key to get or an associative array to set.
- $default - a default value if the setting doesn't exist.
```php
// To get the setting "site_name"
$siteName = config('site_name', 'Default value');
// To set 
config(['site_name'=>'My new site name']);
```