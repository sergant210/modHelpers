##email()
Send emails.

```email($email='', $subject='', $content = ''):bool```
- $email (string|array) - email or an array of emails.
- $subject (string|array) - email subject or an array of email options. Magic.
- $content (string) - email body.

If no arguments are passed the function return an object of the special mailer class which allows to use chain methods.
```php
# Simple using
email('user@mail.ru', 'Subject', 'Message body');
# Sending to multiple users
email(['user1@mail.ru','user2@mail.ru','user3@mail.ru'], 'Subject', 'Message body');
# Use parameters
$params = array(
    // Required
    'subject' => 'Тема',
    'content' => 'Содержание письма',
    // Optional
    'sender'   => 'admin@mail.ru',
    'from'     => 'Administrator',
    'fromName' => 'The Greatest MODX society',
    // 'cc' => '',
    // 'bcc' => '',
    // 'replyTo' => '',
    // 'attach' => '',
);
if (! email('user@mail.ru', $params)) {
    // Some trouble. See the MODX error log.
}
# Use the mailer object
email()
	->to('user1@mail.ru')
	->toUser(5)
	->cc('user2@mail.ru') 
	->subject('Subject')
	->content('Message body')
	->from('Administrator')
	->replyTo('admin@mysite.com')
	->attach('path/to/file1.jpg')
	->attach('path/to/file2.png')
	->send();
```