## modHelpers
Library of the helpfull functions for MODX. Most of these functions can be used by the template engines. You can easily extend this library simply by specifying your classes in the corresponding system settings.

Available functions:

* [abort()](./core/components/modhelpers/docs/en/abort.md) - to redirect to the error or unauthorized page.
* [app()](./core/components/modhelpers/docs/en/app.md) - gets the available container instance.
* array_empty() - checks whether a variable is an empty array.
* array_notempty() - checks whether a variable is an array and not empty.
* array_is_assoc() - checks whether a variable is an associative array.
* [array_trim()](./core/components/modhelpers/docs/en/array_trim.md) - strips whitespace (or specified characters) from the beginning and the end of an array values.
* [array_ltrim()](./core/components/modhelpers/docs/en/array_trim.md) - strips whitespace (or specified characters) from the beginning of an array values.
* [array_rtrim()](./core/components/modhelpers/docs/en/array_trim.md) - strips whitespace (or specified characters) from the end of an array values.
* url() - makes an url. Alias of the method ```$modx->makeUrl()```.
* [cache()](./core/components/modhelpers/docs/en/cache.md) - manages the MODX cache.
* can() - returns true if user has the specified policy permission. Can be used instead of ```$modx->hasPermission()```.
* children() - gets all of the child resource ids for a given resource. The short call of ```$modx->getChildIds()```.
* [chunk()](./core/components/modhelpers/docs/en/chunk.md) - gets the specified chunk or file. Can be used instead of ```$modx->getChunk()```.
* [collection()](https://modzone.ru/documentation/modhelpers/collection-manager.html) - to work with collections.
* columns() - gets select columns from a specific class for building a query. Can be used instead of ```xPDO::getSelectColumns()```.
* [config()](./core/components/modhelpers/docs/en/config.md) - manages the config settings.
* context() - gets the specified property of the current context. By default, returns the key.
* [CSRF protection](./core/components/modhelpers/docs/en/csrf_protection.md) - protection from cross-site request forgery attacks.
* [css()](./core/components/modhelpers/docs/en/css.md) - register CSS to be injected inside the HEAD tag of a resource. 
* [dd()](./core/components/modhelpers/docs/en/dd.md) - dump the passed variables and end the script.
* [default_if()](./core/components/modhelpers/docs/en/default_if.md) - returns default value if a given value equals null or the specified value.
* [dump()](./core/components/modhelpers/docs/en/dump.md) - dump the passed variables.
* echo_br - equivalent to ```echo 'some text' . "<br>"```.
* echo_nl - equivalent to ```echo 'some text' . PHP_EOL```.  i.e. adds the end of line symbol or the specified value.
* [email()](./core/components/modhelpers/docs/en/email.md) - sends an email.
* [email_user()](./core/components/modhelpers/docs/en/email_user.md) - sends an email to the specified user.
* escape() - escapes the provided string using the platform-specific escape character.
* [exec_bg_script()](./core/components/modhelpers/docs/en/exec_bg_script.md) - executes php script in the background on both Windows and Unix.
* [explode_ltrim()](./core/components/modhelpers/docs/en/explode_trim.md) - combines two functions - explode and ltrim.
* [explode_rtrim()](./core/components/modhelpers/docs/en/explode_trim.md) - combines two functions - explode and rtrim.
* [explode_trim()](./core/components/modhelpers/docs/en/explode_trim.md) - combines two functions - explode and trim.
* [faker()](./core/components/modhelpers/docs/en/faker.md) - creates a faked data.
* [filter_data()](./core/components/modhelpers/docs/en/filter_data.md) - filters the array according to the specified rules.
* [first()](./core/components/modhelpers/docs/en/first.md) - returns the first not null parameter.
* forward() - to forward the request to another resource without changing the URL. The short call of ```$modx->forward()```.
* [has_parent()](./core/components/modhelpers/docs/en/has_parent.md) - checks the presence of the specified parent(s).
* [html()](./core/components/modhelpers/docs/en/html.md) - registers HTML to be injected inside the HEAD tag or before the closing BODY tag.
* [html_attributes()](./core/components/modhelpers/docs/en/html_attributes.md) - prepares the HTML attributes to output from an array.
* [img()](./core/components/modhelpers/docs/en/img.md) - prepares the HTML tag "img".
* is_ajax() - returns true if the current request is asynchronous (ajax).
* [is_auth()](./core/components/modhelpers/docs/en/is_auth.md) - determines if the user is authenticated in a specific context.
* is_desktop() - desktop detection.
* is_email() - validates the email. Can be used to validate the form data.
* is_guest() - determines if the user is a guest. Checks equality ```$modx->user->id == 0```
* is_even() - checks whether a variable is even.
* is_odd() - checks whether a variable is odd.
* is_mobile() - mobile detection.
* is_tablet() - tablet detection.
* is_url() - validates the url.
* lang() - to work with lexicon records. Can be used instead of ```$modx->lexicon()```.
* [log_error()](./core/components/modhelpers/docs/en/logger.md) — logs to the error log for the ERROR log level.
* [log_warn()](./core/components/modhelpers/docs/en/logger.md) — logs to the error log for the WARN log level.
* [log_info()](./core/components/modhelpers/docs/en/logger.md) — logs to the error log for the INFO log level.
* [log_debug()](./core/components/modhelpers/docs/en/logger.md) — logs to the error log for the DEBUG log level.
* [load_model()](./core/components/modhelpers/docs/en/load_model.md) - loads a model for a custom table.
* [login()](./core/components/modhelpers/docs/en/login.md) - force login the specified user to the current context.
* [logout()](./core/components/modhelpers/docs/en/logout.md) - force logout the current user.
* [memory()](./core/components/modhelpers/docs/en/memory.md) - returns the formatted string of the amount of memory allocated to PHP.
* [null_if()](./core/components/modhelpers/docs/en/null_if.md) - returns NULL if the given values are equal.
* object() - to work with objects of MODX.
* [object_exists()](./core/components/modhelpers/docs/en/object_exists.md) - checks if the specified object exists.
* [optional()](./core/components/modhelpers/docs/en/optional.md) - provides access to optional objects.
* parents() - gets all of the parent resource ids for a given resource. The short call of ```$modx->getParentIds()```.
* [parse()](./core/components/modhelpers/docs/en/parse.md) - parses a string using an associative array of replacement variables. 
* [pls()](./core/components/modhelpers/docs/en/pls.md) - to work with placeholders.
* [pls_delete()](./core/components/modhelpers/docs/en/pls_delete.md) - removes the specified placeholders.
* [print_d()](./core/components/modhelpers/docs/en/print_d.md) - prints the value and dies. Convert a given value to the string format, prints it and stops the script execution.
* [print_str()](./core/components/modhelpers/docs/en/print_str.md) - extends the print_r function. Convert a given value to the string format and print it.
* processor() - runs the specified processor. Equivalent to the ```$modx->runProcessor()```.
* quote() - quote the string.
* [query()](./core/components/modhelpers/docs/en/query.md) - works with raw SQL queries.
* [redirect()](./core/components/modhelpers/docs/en/redirect.md) - redirect to the url or site page if the id is passed. Wraps the ```$modx->redirect()``` and ```$modx->makeUrl()```.
* [request()](./core/components/modhelpers/docs/en/request.md) - for HTTP request management.
* [resource()](./core/components/modhelpers/docs/en/resource.md) - works with a resource object.
* [resource_exists()](./core/components/modhelpers/docs/en/resource_exists.md) - checks the specified resource exists.
* resource_id() | res_id() - gets the id of the current resource. Returns the value of $modx->resource->id. 
* resources() - works with a resource collection.
* [response()](./core/components/modhelpers/docs/en/response.md) - for response management.
* [script()](./core/components/modhelpers/docs/en/script.md) - registers JavaScript to be injected inside the HEAD tag or before the closing BODY tag. Available the script attributes "async" and "defer".
* [session()](./core/components/modhelpers/docs/en/session.md) - manages the session. You can use the dot notation.
* [session_pull()](./core/components/modhelpers/docs/en/session_pull.md) - gets the value of a given key and then unsets it.
* [snippet()](./core/components/modhelpers/docs/en/snippet.md) - runs the specified snippet from DB or file. The result can be cached.
* [str_between()](./core/components/modhelpers/docs/en/str_between.md) - gets a substring between two tags.
* [str_clean()](./core/components/modhelpers/docs/en/str_clean.md) - sanitizes the string. Strips HTML tags and removes the specified characters. It's like the ```modX::sanitizeString()``` method.
* [str_contains()](./core/components/modhelpers/docs/en/str_contains.md) - determines if a given string contains a given substring.
* [str_concat()](./core/components/modhelpers/docs/en/str_concat.md) - concatenates passed string arguments.
* [str_ends()](./core/components/modhelpers/docs/en/str_ends.md) - determines if a given string ends with a given substring.
* [str_limit()](./core/components/modhelpers/docs/en/str_limit.md) - limits the number of characters in a string. 
* [str_match()](./core/components/modhelpers/docs/en/str_match.md) - determines if a given string matches a given pattern.
* [str_starts()](./core/components/modhelpers/docs/en/str_starts.md) - determines if a given string starts with a given substring.
* [string()](./core/components/modhelpers/docs/en/string.md) - wraps the string for further manipulation.
* [switch_context()](./core/components/modhelpers/docs/en/switch_context.md) - switches the context according to the conditions.
* table_name() - gets the table name of the specified class. Can be used instead of ```xPDO::getTableName()```.
* template_id() - gets the template id of the current resource. Returns the value of $modx->resource->template.
* [tag_encode()](./core/components/modhelpers/docs/en/tag_encode.md) - converts MODX tag chars to corresponding HTML codes.
* [tag_decode()](./core/components/modhelpers/docs/en/tag_decode.md) - decodes HTML codes back to MODX tag chars.
* [timer](./core/components/modhelpers/docs/en/timer.md) - time logger.
* tv() - gets the specified TV of the current resource. 
* [user()](./core/components/modhelpers/docs/en/user.md) - gets a user object or an array of user's data.
* [user_exists()](./core/components/modhelpers/docs/en/user_exists.md) - checks the specified user exists.
* user_id() - gets the id of the current user. Returns the value of ```$modx->user->id```.
* users() - works with a user collection.


### System settings for class extending
- modhelpers_cacheManagerClass — custom cache manager class.
- modhelpers_collectionClass — custom collection class.
- modhelpers_containerClass — custom container class.
- modhelpers_loggerClass — custom logger class.
- modhelpers_mailerClass — custom mailer class.
- modhelpers_modelBuilderClass — custom model builder class.
- modhelpers_modelColumnClass — custom model column class.
- modhelpers_objectClass — custom object class.
- modhelpers_optionalClass —  name of the custom Optional class.
- modhelpers_queryClass — custom query class.
- modhelpers_requestClass — custom request class.
- modhelpers_responseManagerClass — name of the response manager class.
- modhelpers_stringClass —  custom String class.
- modhelpers_timerClass —  custom Timer class.
- modhelpers_varDumperClass — class name for the dump and dd functions.

### Examples
**Check the user exists**
```php
if (user_exists(['email'=>'admin@mail.com']) {
    // Exists
}
```

**Get the data from the cache**
```php
//Gets the data from the file *core/cache/my_data/key.cache.php*. 
$value = cache('key', 'my_data');
// Or 
$value = cache()->get('key', 'my_data');
```

**Send email**
```php
email('pussycat@mail.ru', 'Subject','Email content');
// To the user
email_user('admin', $subject, $content); 
// or use the user id
email_user(5, $subject, chunk($tplEmail));
// or like that
email()->to('some.email@gmail.com')->cc('copymail@mail.com')->subject('Hello')->content('Content')->attach('path/to/file.jpg')->send();
// or send to queue
email()->to('some.email@gmail.com')->cc('copymail@mail.com')->subject('Hello')->content('Content')->attach('path/to/file.jpg')->save();
// and then send from the queue
email()->saved();
```

**Run a snippet and save the result to the cache**
```php
$output = snippet('mySnippet', $params, 60); // store the snippet result for 60 seconds
```

**Run a snippet from file**
```php
$output = snippet(MODX_CORE_PATH . 'elements/snippets/mysnippet.php', $params);
```

**Get a chunk from file**
```php
$output = chunk(MODX_CORE_PATH . 'elements/chunks/mychunk.html', $placeholders);
```

**Redirect**
```php
redirect($url);
//To the resource with the id = 5
redirect(5);
```

**The latest resource**
```php
$resourceObject = resource()->last(); // Resource object
$resourceArray = resource()->last()->toArray(); // Resource data as array
```

**The last 10 resources**
```php
$resObjects = resources()->last(10); 
```

**Array of the resource pagetitles of the parent with the id = 20.**
```php
$titles = resources()->where(['parent'=>20])->get('pagetitle'); // array('pagetitle 1', 'pagetitle 2', 'pagetitle 3')
```
**Use a Closure for child resources of the category with the id = 20.**
```php
return resources()->where(['id:IN'=>children(20)])->each(function($resource, $idx) {return "<div>{$idx}. " . $resource['pagetitle'] . "</div>";}); 
```
**Set a value to the session**
```php
session(['key1.key2', 'value']); // => $_SESSION['key1']['key2'] = $value;
# Disable the dot notation parsing
session(['key1.key2' => 'value'], true); // ==> $_SESSION['key1.key2'] = $value;
```
**Get the value from session**
```php
$value = session('key1.key2');  // $value = $_SESSION['key1']['key2']
# Disable the dot notation parsing
$value = session('key1.key2', 'default', true); // ==> $value = isset($_SESSION['key1.key2']) ? $_SESSION['key1.key2'] : 'default';
```

**Validates the email**
```php
if (is_email($_POST['email'])) {
   // Valid
}
```
**Remove child resources of the resource with the id = 10**
```php
resources()->where(['parent'=>10])->remove();
```
**Count blocked users**
```php
$count = users()->profile()->where(['Profile.blocked'=>1])->count();
```
**Load script with the async attribute**
```php
script('/path/to/script.js', 'async'); // <script async type="text/javascript" src="/path/to/script.js"></script>
```
**Get an array of users**
```php
// Use the prepared query
$userArray = query('select * from ' . table_name('modUser'). ' WHERE id = ?')->execute( (int) $_POST['user_id']);
```
**Log error to the error log**
```php
log_error($array); // The array will be converted to string using the print_r() function.
log_error($message, 'HTML'); // Show message on the page.
```
**Get the list of the pagetitles**
```php
return resources()->where(['id:IN'=>children(5)])->each(function($resource, $idx){ return "<li>{$idx}. ".$resource['pagetitle']."</li>";});
```
**Get users which are members of the "Manager" group**
```php
$usersArray = users()->members('Managers')->toArray();
// Get all users from "ContentManagers" and "SaleManagers" groups 
$users = users()->members('%Managers')->get();
foreach($users as $user) {
  echo $user->username;
}
```
**Add the first ten users to the "Manager" group**
```php
users()->whereIn('id',range(1,10))->joinGroup('Manager');
```
**Make a list of faked news (using a snippet)**
```php
return collection(10)->each(function($item, $idx, $modx){return "<div>" . faker()->date() . img(faker()->imageUrl(500,300),['class'=>'img-news']) . '<br>' . faker()->text(700) . '</div>';});
```

[Russian documentation](https://modzone.ru/documentation/modhelpers/).