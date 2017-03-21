## img
Prepares the HTML tag "img".

```img($src, $attrs = array())```
- $src (string) - image url. 
- $attrs (array) - Tag attributes.
```php
echo img('/assets/images/pic1.jpg', ['title'=>'My picture']);
# Use the faker function to get a fake image.
echo img(faker(['imageUrl'=>[200,100]]), ['title'=>'My picture']);
```