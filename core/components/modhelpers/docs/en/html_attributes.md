## html_attributes()
Prepare the HTML attributes to output from an array. It's intended to use in the css and script functions.

```html_attributes($attributes)```
- $attributes (array) - array with HTML attributes.

```php
$attributes = [
	'type' => 'text',
	'id' => 'id-string',
	'class' => 'class1 class2',
	'required',
];
$output = '<input ' . html_attributes($attributes) . '>'; // <input type="text" id="input-id" class="class1 class2" required>
```