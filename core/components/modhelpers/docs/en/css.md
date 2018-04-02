## css()
Register CSS to be injected inside the HEAD tag of a resource.

```css($src, $attr = null)```
- $src (string) - style code or the path to css file to inject.
- $attr (string|array) - attributes for the link tag (type, media, id and so on). Optional. 

```php
css('assets/css/styles.css'); 
# Set the attributes.
css('/path/to/styles.css', ['media' => 'print']); // <css src="/path/to/styles.css" media="print" rel="stylesheet"></script>
css('/path/to/styles.css', ['media' => 'print', 'rel' => 'alternate']); // <css src="/path/to/styles.css" media="print" rel="alternate"></script>
```
