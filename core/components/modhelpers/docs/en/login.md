##login
Force login the specified user to the current context.

```
login($user):bool
```

- $user (int|modUser) - User id or modUser object. 
  
```php
login(4);
// OR
$user = user(4); // ~ $modx->getObject('modUser', 4);
login($user);
```