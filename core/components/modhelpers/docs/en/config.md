## config()
Manage the config settings.

```config($key, $default)```

- $key (string|array) - a system settings key to get or an associative array to set.
- $default - a default value if the setting doesn't exist.
```php
// To get a setting
$siteName = config('site_name', 'Default value');
// To set a setting
config(['site_name'=>'My new site name']);
```