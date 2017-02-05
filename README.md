## modHelpers
Library of the helpfull functions for MODX. Most of these functions can be used by the template engines.

Available functions:

* url() - makes an url. Alias of the method ```$modx->makeUrl()```.
* redirect() - to redirect to the url or site page if the id is passed. Wrapper for ```$modx->redirect```.
* forward() - to forward the request to another resource without changing the URL. The short call of ```$modx->forward```.
* [abort()](./core/components/modhelpers/docs/en/abort.md) - to redirect to the error or unauthorized page.
* [config()](./core/components/modhelpers/docs/en/config.md) - manages the config settings.
* [session()](./core/components/modhelpers/docs/en/session.md) - manages the session using dot notation.
* [cache()](./core/components/modhelpers/docs/en/cache.md) - manages the MODX cache.
* parents() - gets all of the parent resource ids for a given resource. The short call of ```$modx->getParentIds```.
* children() - gets all of the child resource ids for a given resource. The short call of ```$modx->getChildIds```.
* [pls()](./core/components/modhelpers/docs/en/pls.md) - to work with placeholders.
* [pls_delete()](./core/components/modhelpers/docs/en/pls_delete.md) - removes the specified placeholders.
* lang() - to work with lexicon records. Can be used instead of ```$modx->lexicon()```.
* table_name() - gets the table name of the specified class. Can be used instead of ```xPDO::getTableName()```.
* columns() - gets select columns from a specific class for building a query. Can be used instead of ```xPDO::getSelectColumns()```.
* [email()](./core/components/modhelpers/docs/en/email.md) - send emails.
* [email_user()](./core/components/modhelpers/docs/en/email_user.md) - sends email to the specified user.
* [str_clean()](./core/components/modhelpers/docs/en/str_clean.md) - sanitizes the string. Wrapper for ```$modx->sanitizeString```.
* quote() - quote the string.
* escape() - escapes the provided string using the platform-specific escape character.
* css() - registers CSS to be injected inside the HEAD tag of a resource.
* [script()](./core/components/modhelpers/docs/en/script.md) - registers JavaScript to be injected inside the HEAD tag or before the closing BODY tag. Available the script attributes "async" and "defer".
* [html()](./core/components/modhelpers/docs/en/html.md) - registers HTML to be injected inside the HEAD tag or before the closing BODY tag.
* chunk() - gets the specified chunk or file. Can be used instead of ```$modx->getChunk()```.
* [snippet()](./core/components/modhelpers/docs/en/snippet.md) - runs the specified snippet from DB or file. The result can be cached.
* processor() - runs the specified processor. Can be used instead of ```$modx->runProcessor()```.
* [is_auth()](./core/components/modhelpers/docs/en/is_auth.md) - determines if the user is authenticated in a specific context.
* is_guest() - determines if the user is a guest. Checks equality ```$modx->user->id == 0```
* can() - returns true if user has the specified policy permission. Can be used instead of ```$modx->hasPermission()```.
* resource_id() | res_id() - gets the id of the current resource. Returns the value of $modx->resource->id. 
* template_id() - gets the template id of the current resource. Returns the value of $modx->resource->template.
* user_id() - gets the id of the current user. Returns the value of $modx->user->id.
* tv() - gets the specified TV of the current resource. 
* object() - to work with objects of MODX.
* collection() - to work with collections.
* resource() - works with a resource object.
* resources() - works with a resource collection.
* user() - works with a user object.
* users() - works with a user collection.
* object_exists() - checks if the specified object exists.
* user_exists() - checks if the specified user exists.
* resource_exists() - checks if the specified resource exists.
* is_email() - validates the email. Can be used to validate the form data.
* is_url() - validates the url.
* [log_error()](./core/components/modhelpers/docs/en/logger.md) — logs to the error log for the ERROR log level.
* [log_warn()](./core/components/modhelpers/docs/en/logger.md) — logs to the error log for the WARN log level.
* [log_info()](./core/components/modhelpers/docs/en/logger.md) — logs to the error log for the INFO log level.
* [log_debug()](./core/components/modhelpers/docs/en/logger.md) — logs to the error log for the DEBUG log level.
* context() - gets the specified property of the current context. By default, returns the key.
* [query()](./core/components/modhelpers/docs/en/query.md) - works with raw SQL queries.
* [memory()](./core/components/modhelpers/docs/en/memory.md) - returns the formatted string of the amount of memory allocated to PHP.
* [img()](./core/components/modhelpers/docs/en/img.md) - prepares the HTML tag "img".
* [faker()](./core/components/modhelpers/docs/en/faker.md) - creates a faked data.
* [load_model()](./core/components/modhelpers/docs/en/load_model.md) - loads a model for a custom table.
* is_ajax() - returns true if the current request is asynchronous (ajax).
* [login()](./core/components/modhelpers/docs/en/login.md) - force login the specified user to the current context.
* [logout()](./core/components/modhelpers/docs/en/logout.md) - force logout the current user.


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

**Send an email**
```php
email('pussycat@mail.ru', 'Subject','Email content');
// To the user
email_user('admin', $subject, $content); 
// or use the user id
email_user(5, $subject, chunk($tplEmail));
// or like that
email()->to('some.email@gmail.com')->cc('copymail@mail.com')->subject('Hello')->content('Content')->attach('path/to/file.jpg')->send();
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
session('key1.key2', 'value'); // => $_SESSION['key1']['key2'] = $value;
```
**Get the value from session**
```php
$value = session('key1.key2');  // $value = $_SESSION['key1']['key2']
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
// Can use the prepared query
$userArray = query('select * from ' . table_name('modUser'). ' WHERE id < ?')->execute(( (int) $_POST['user_id']);
```
**Log error to the error log**
```php
log_error($array); // The array wil be converted to a string using print_r().
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