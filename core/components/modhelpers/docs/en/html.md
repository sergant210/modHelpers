## html()
Registers HTML to be injected inside the HEAD tag or before the closing BODY tag.

```html($src, $start = false)```
- $src - a javascript code or path to js file to inject.
- $start - True to inject the $src before the HEAD tag. False to injected before the closing BODY tag. 

```php
# Add html block to the end of page.
html('<div>Some HTML block.</div>');
# Inject inside the HEAD tag.
html('<meta name="viewport" content="width=device-width, initial-scale=1.0">', true);
```
