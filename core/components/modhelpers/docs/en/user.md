## user()
Gets a user object or an array of user's data.

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

$user = user(['Profile.email' => 'user@mail.org']);
```
Get the current user
```php
$user = user(true);
$username = $user->username;
```