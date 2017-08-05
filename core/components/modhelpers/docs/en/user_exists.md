## user_exists()
Checks if the specified user exists

```user_exists($criteria = null)```
- $criteria(string|integer|array) - User id, username or an array.

```php
if (user_exists(['email'=>'admin@mail.com'])) {
    // The user with the specified email exists
}
if (user_exists(['username'=>'manager'])) {
    // The user with the specified username exists
}
```