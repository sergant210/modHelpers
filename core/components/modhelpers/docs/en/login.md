## login
Force login the specified user to the specified context.

```login($user, $ctx):bool```

- (mixed) $user - User id, username, modUser object or any valid xPDO expression. 
- (string) $ctx - Context key. 
  
### Log in to the current context
```php
login(4);
// OR
$user = user(4); // ~ $modx->getObject('modUser', 4);
login($user);
// OR
login(['email' => $_POST['email']);
```

### Log in to the specified context
```php
login(4, 'en');
```