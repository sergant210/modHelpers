## cache()
Manages the MODX cache.

```cache($key, $options)```
- $key - a unique key identifying the item being set.
- $options - an array of options OR lifetime in seconds OR a partition.

The $options array can contain the following options indicating the cache partition to write to, the cache handler to use and the default expiry time.
- xPDO::OPT_CACHE_KEY: the cache partition to write to.
- xPDO::OPT_CACHE_HANDLER: the cache handler to use. Typically you shouldn’t hardcode this and instead let the specific implementation handle the cache handler via system settings (ie cache_PARTITION_handler system setting).
- xPDO::OPT_CACHE_EXPIRES: the default expiry time. 

See [documentation](https://docs.modx.com/revolution/2.x/developing-in-modx/advanced-development/caching).
### Store to the cache
```php
# To set data to the core/cache/default/key.cache.php
cache(['key' => $some_data]);
# To set data to the core/cache/my_data/key.cache.php
$options = array(
    xPDO::OPT_CACHE_KEY = 'my_data'
)
cache(['key' => $some_data], $options);
// is equivalent to
cache(['key' => $some_data], 'my_data');
# Store data for 60 seconds
cache(['key' => $some_data], 60);
```
### Getting from the cache
```php
# The simplest way. To get the cached data from core/cache/default/key.cache.php
$value = cache('key');

// From core/cache/my_data/key.cache.php
$options = array(
    xPDO::OPT_CACHE_KEY = 'my_data' 
)
$value = cache('key', $options);
// is equivalent to
$value = cache('key', 'my_data');
```
### Use the modHelper's CacheManager
The function "cache" returns the object of the special CacheManager class if no arguments are passed. 
#### Store data in the cache
```set($key, $value, $lifetime = 0, $options = array())```  

Examples.
```php
cache()->set('key', $some_data); 
$options = array(
    xPDO::OPT_CACHE_KEY = 'my_data'
)
cache()->set('key', $some_data, 0, $options);
// is equivalent to
cache()->set('key', $some_data, $options);
// is equivalent to
cache()->set('key', $some_data, 'my_data');
# Set the lifetime
cache()->set('key', $some_data, 7200, 'my_data');
```
#### Retrieve from the cache
```get($key, $options = array())```  

Example.
```
$options = array(
    xPDO::OPT_CACHE_KEY = 'my_data'
)
$value = cache()->get('key', $options);
// is equivalent to
$value = cache()->get('key', 'my_data');
```
#### Retrieve & Store
Retrieve a value from the cache. if the requested value doesn't exist, store a default value.
```remember($key, $options, Closure $callback)```  

Example. Get the number of all active users from the cache. If it doesn't exist, retrieve it from the database and store it to the cache.
```php
cache()->remember('count_users', 300, function() {
    return collection('modUser')->where(['active' => 1])->count();
});
```
#### Delete from the cache
```delete($key, $options = array())```  
```php
// Cache file core/cache/default/key.cache.php
cache()->delete('key');
// Cache file core/cache/my_data/key.cache.php
cache()->delete('key', 'my_data');
```
