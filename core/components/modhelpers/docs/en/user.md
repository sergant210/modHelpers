## user()
Works with a user object.

```user($criteria = null, $asObject = true)```
- $criteria(string|integer|array|bool) - User id, username, an array or TRUE to get the current user.
- $asObject(bool) - True to return an object. Otherwise - an array.

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

// The current user
$username = user(true)->username;

$user = user(['Profile.email' => 'user@mail.org']);

```