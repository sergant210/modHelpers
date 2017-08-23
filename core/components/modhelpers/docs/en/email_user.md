## email_user()
Send an email to the specified user.

```email_user($user, $subject, $content = ''):bool```

- $user (int|string|modUser) - User id or username or user object.
- $subject (string|array) - Magic. Email subject or an array of options. Required option keys - subject, content. Optional - sender, from, fromName, replyTo, cc, bcc, attach.
- $content (string) - email body.

```php
email_user(5, $subject, $content);
email_user('user1', $subject, $content);
# Use the collection. Slow way. The function every time will get a user object to get his email.
users()->members('Subscribers')->each(function($user){email_user($user['id'], 'subject', 'content');});
# More faster
users()->profile()->members('Subscribers')->each(function($user){email($user['email'],'subject', 'content');});
```