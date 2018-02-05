## response()  
Create a response to be sent back to the user's browser. It uses [symfony/http-foundation](https://symfony.com/doc/current/components/http_foundation.html) package. It's intended to use in API mode.

```response($content = '', $status = 200, array $headers = [])```
 - $content (string|array) - Content for output. Optional.
 - $status (integer) - Status code. Optional.
 - $headers (array) - Response headers. Optional.
 
#### Prepare the response.
```php
# api.php
// init modX class
...
response('Some content');
// some code
...
```
As you can see the call of the response function does not terminate the current script. If you want to do so add the send method.
```php
# api.php
// init modX class
...
response('Some content')->send();
// this code is unreachable
```
#### Set headers and cookies
```php
response()->chunk('chunkName', $data)
          ->header('myHeader1', 'Some value')
          ->header('myHeader2', 'Another value')
          ->cookie('myCookie', 'Value', 5)  // the expires must be in minutes 
```
#### Get a file
```php
response()->file('/path/to/file')->send(); 
```
#### Download a file
```php
response()->download('/path/to/file')->send(); 
```
#### Download a file and delete it
```php
response()->download('/path/to/file')->deleteFile()->send(); 
```