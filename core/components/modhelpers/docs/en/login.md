## login
Force login the specified user to the current context.

```login($user):bool```

- mixed $user - User id, username, modUser object or any valid xPDO expression. 
  
```php
login(4);
// OR
$user = user(4); // ~ $modx->getObject('modUser', 4);
login($user);
// OR
login(['email' => $_POST['email']);
```