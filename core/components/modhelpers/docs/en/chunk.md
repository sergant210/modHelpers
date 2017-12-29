## chunk()
Gets the specified chunk or file. Can be used instead of ```$modx->getChunk()```.

```chunk($chunkName, $properties = array ())```
- $chunkName (string) - Chunk name or the full path to a file. Required.
- $properties (array) - Chunk parameters. Optional.

```php
# Simple using
$output = chunk('myChunk', $params);
# Get the file chunk
 $output = chunk(MODX_CORE_PATH . 'chunks/myChunk.tpl', $params);
# Get the file chunk from the folder specified in the "modhelpers_chunks_path" system setting.
 $output = chunk('./myChunk.tpl', $params);
```