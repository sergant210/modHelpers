## logout
Force logout the current authenticated user.

```logout($redirect = false, $code = 401, $relogin = true, $ctx = null)) :bool```

- $redirect (bool) - Magic. `true` to redirect after logout OR context key.
- $code (int) - Response code for redirection - 401, 403, 404. Specify 401 and 403 codes to redirect to the unauthorized page. And 404 to redirect to the error page.
- $relogin (bool) - login as the authenticated in the mgr context user.
- (string) $ctx - Context key. 

### Log out from the web context 
```php
logout(false, 401, true, 'web');
// is the shorter call
logout('web');
```