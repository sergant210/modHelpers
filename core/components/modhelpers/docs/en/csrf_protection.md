## CSRF Protection
It's needed to protect your site from cross-site request forgery attacks. Cross-site request forgeries are a type of malicious exploit whereby unauthorized commands are performed on behalf of an authenticated user.

modHelpers automatically generates a CSRF "token" for each active user session. This token is used to verify that the active user is the one actually making the requests to the site. Call the ```csrf_token``` function to get the token from the session.

```csrf_token($regenerate = false)```

#### Usage with HTML forms
Place a hidden CSRF token field in your HTML forms to enable CSRF protection. Use the ```csrf_field``` helper to generate the token field:
```php
// Plugin or snippet
$modx->setPlaceholder('csrfField', csrf_field());
// or use the "pls" helper
pls(['csrfField' => csrf_field()]);
```
Now place this placeholder in the form.
```html
<form method="POST" action="/profile">
    [[+csrfField]]  
    <!-- <input type="hidden" name="csrf_token" value="sf7Y5wgC01vaSck2rc"> -->
    ...
</form>
```
The better way is to use a template engine:
```html
<form method="POST" action="/profile">
    {csrf_field()}
    ...
</form>
```
#### Usage with request header
In addition to using CSRF token as a POST parameter you can, for example, store the token in a HTML meta tag:
```html
<meta name="csrf-token" content="[[+csrfField]]">
<!-- For a template engine -->
<meta name="csrf-token" content="{csrf_token()}">
<!-- Or use the csrf_meta function-->
{csrf_meta()}
```
Then you need to add the token to all request headers:
```javascript
// Example for jQuery
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

#### CSRF token checking
Before performing some actions with the form data you need to perform a token check. If you have the [Middlewares](https://github.com/sergant210/Middlewares) component installed you can do it in a global middleware. Othewise you can do it where you want (in your class, plugin or snippet).
```php
# Check every request
if (request()->checkCsrfToken()) {
    // The token exists and matches. 
}

# More complicated conditions
// only for authenticated users
if (is_auth() && request()->checkCsrfToken() {
    // Do what you need to do
} else {
    // Fail
}
// only for specific url
if (request()->match(['profile','tickets/*']) && request()->checkCsrfToken() {...}
// only for POST request
if (request()->isMethod('POST') && request()->checkCsrfToken() {...}
```  
You may to check the token existance in the request:
```php
if (request()->getCsrfToken()) {
    // The token exists. 
}