## logger
It's a special class which enhance the base functionality of the MODX log method. It's used by 4 functions:

* log_error() - logs a message for the error log level. 
* log_warn() - logs a message for the warning log level.
* log_info() - logs a message for the info log level.
* log_debug() - logs a message for the debug log level.
All these functions have the same arguments.

```
log_error($message, $changeLevel = false, $target = '')
```
- $message (string|array) - a message to log. If the message is an array it will be log via print_r(). 
- $changeLevel (bool|string) - if true the current log level temporary will be switched to level which set by the corresponding log function. Only for this message. And will bee switched back after. Magic. The third argument (target) can be set here.
- $target (string) - sets the log target. Available values: '' - for using the current log level, 'FILE' - sends the message to a log file, 'HTML' - sends output to a site page.

```php
log_error('Error message');
# Show debugging information on a site page.
log_error($debugDataArray, 'HTML');
# Log info message. The current log level is 'ERROR'.
log_info('Information message', true); // Switch the log level to "INFO" for logging the message and switch it back to "ERROR".
// It will look like this
[2016-12-24 19:59:59] (INFO @ /www/core/model/modx/modx.class.php : 777) Information message
```