## email()
Sends an email.

```email($email='', $subject='', $content = ''):bool```
- $email (string|array) - email or an array of emails.
- $subject (string|array) - email subject or an array of email options. Magic.
- $content (string) - email body.

If no arguments are passed the function returns an object of the special mailer class which allows to use chains of methods.
#### Simple using
```php
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
```
#### Use the mailer object
```php
email()
	->to('user1@mail.ru')
	->toUser(5)
	->cc('user2@mail.ru') 
	->subject('Subject')
	->content('Message body')
	->tpl('chunkName or file', $params) // ignored if the content is set.
	->from('Administrator')
	->replyTo('admin@mysite.com')
	->attach('path/to/file1.jpg')
	->attach('path/to/file2.png')
	->send();
```
### Queues
Use queues to defer the sending emails.
```php
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
	->queue(); // or save()
```
You can create different queues for different tasks.
```php
// Notify managers about a new order.
email()...->save('for_manager');
// Inform the admin about some user action (add to the queue "for_admin"). 
email()...->save('for_admin'); // Set the second parameters to true to rewrite the queue.
```
All email will be stored to the cache. Then set the cron job.
```php
// email_admin.php
# Connect to MODX
...
# Send emails from the queue (for example, every hour).
email()->sendFromQueue('for_admin');
// or
email()->saved('for_admin');
```
Testing the email functionality. Use the log() method to save the email data to the log or use the toArray() method to output the data to the page.
```php
# Email to log
email()
	->to('user1@mail.ru')
	->toUser(5)
	->cc('user2@mail.ru') 
	->subject('Subject')
	->tpl(MODX_CORE_PATH . 'chunks/myChunk.tpl', $params)
	->from('Administrator')
	->replyTo('admin@mysite.com')
	->attach('path/to/file1.jpg')
	->attach('path/to/file2.png')
	->log() // to get the email data in the json format use log(true)
```