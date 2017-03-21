## abort()  
Redirects to the error or unauthorized page.

```abort($options)```
 - $option - an array of options or the response code - 401, 403, 404.
 
```php
abort(401);  // or 403
// Show the error page
abort(404); // or abort()
```
