## redirect()
Redirects to the url or site page if the id is passed.

```redirect($url, $options = false)```
- $url (string|integer) - Url or page id. Required.
- $options (array|string|bool) An array of options for the redirect. Optional.

```php
redirect('about.html');

// redirect to the page with id 5
redirect(5);

// Use context
$options = ['context'=>'en']
redirect(5, $options); 
// or 
redirect(5, 'en');
```