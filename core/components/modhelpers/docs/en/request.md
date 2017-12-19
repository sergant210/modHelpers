## request()  
Create a instance to manage the current HTTP request. It uses [symfony/http-foundation](https://symfony.com/doc/current/components/http_foundation.html) package.

```request($key = null, $default = null)```
 - $key (string) - Name of input item. Optional.
 - $default (mixed) - Default value. Optional.
 
#### Retrieve the form data.
```php
$request = request();
$key = $request->input('key');
// OR
$key = $request->key;
// OR
$key = $request['key'];
```
#### Use the request instance.
```php
if (request()->isAjax()) // ajax request
if (request()->isJson()) // json request
# Retrieve the request data 
request()->all(); // All data
request()->input(); // POST data
request()->query(); // GET data
request()->allFiles(); // Uploaded files
# Selectively
request()->only([...]); // Get the provided keys with values from the input data.
request()->except([...]); // Get all of the input except for a specified array of keys.
# URI
request()->getHost(); 
request()->path(); 
request()->url(); // Get the URL of the request without query string.
request()->fullUrl(); // Get the URL with query string.
# Filter
request()->filter($rules); 
request()->input(); // Filtered data.

// and many others
```
#### Manage uploaded files.
```php
// Uploads all files in the specified relative path for the specified media source.
$request->uploadFiles($path, $mediaSource);
// Upload the specified file.
if ($request->hasFile('file')) {
    $request->file('file')->store('assets', $mediaSource);  // File name will be hashed.
}
// Specify the file name
if ($request->hasFile('avatar')) {
    $request->file('avatar')->storeAs('assets', 'avatar.jpg', $mediaSource);
}
// Leave the original name
if ($request->hasFile('avatar')) {
    $request->file('avatar')->storeAs('assets', $request->avatar->originalName(), $mediaSource);
}
```

Use the ```isBot``` method to check if the "owner" of the request is a bot. This method compares the request user agent with the ```modhelpers_bot_user_agents``` system setting.