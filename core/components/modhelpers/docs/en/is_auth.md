## is_auth()
Determines if the user is authenticated in a specific context.

```auth($ctx='')```
- $ctx (string) - Context key. Optional. If not specified it's used the current context.  

This is an analogue of ```$modx->user->isAuthenticated($modx->context->key)```.

```php
if (is_auth('en')) {
    // The current user is authenticated in the context "en".
}
```