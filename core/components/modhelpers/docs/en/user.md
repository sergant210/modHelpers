## user()
Works with a user object.

```user($criteria = null, $asObject = true)```
- $criteria(string|integer|array) - User id, username or an array.
- $asObject(bool) - True to return an object. Otherwise - as array.

```php
$userID = 1;
$userObject = user($userID);
$email = user($userID)->get('email'); 

$userObject = user('admin'); 

// user() returns the modUser object. So you can use xPDOObject methods:
// toArray(), getOne(), addOne(), save() and so on.
$userArray = user($userID)->toArray();
// Get as array
$username = user($userID, false)['username'];

$user = user([
            'username:LIKE' => 'Admin',
]);
// Current user
$username = user(user_id())->username;
```