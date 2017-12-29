## snippet()
Runs the specified snippet from DB or file. The result can be cached.

```snippet($snippetName, $scriptProperties = array (), $cacheOptions = array())```
- $snippetName (string) - Snippet name. Required.
- $scriptProperties (array) - Snippet parameters. Optional.
- $cacheOptions (array|string|integer) - Cache options. Magic. Can be passed an array of options OR lifetime in seconds OR a cache partition (see [cache()](./core/components/modhelpers/docs/en/cache.md)). Optional.   

```php
# Simple using
$output = snippet('mySnippet', $params);
# Run the file snippet
 $output = snippet(MODX_CORE_PATH . 'snippets/mySnippet.php', $params);
# Run the file snippet from the folder specified in the "modhelpers_snippet_path" system setting.
 $output = snippet('./mySnippet.php', $params);
# Cache the snippet result for 60 seconds
$output = snippet('mySnippet', $params, 60); // Usefull for heavy snippets
```