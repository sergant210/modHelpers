## modHelpers
Library of the helpfull functions for MODX.

Available functions:

* url() - make an url.
* redirect() - redirect to the url or site page if the id is passed.
* abort() - redirect to the error page.
* config() - manage the config settings.
* session() - manage the session using dot notation.
* cache() - manage the cache.
* parents() - gets all of the parent resource ids for a given resource. 
* children() - gets all of the child resource ids for a given resource.
* pls() - to work with placeholders.
* pls_delete() - removes the specified placeholders.
* lang() - to work with lexicon records.
* table_name() - gets the table name of the specified class.
* columns() - gets select columns from a specific class for building a query.
* email() - send email.
* email_user() - send email to the specified user.
* pdotools() - get the pdoTools object.
* pdofetch() - get the pdoFetch object.
* str_clean() - sanitize the string.
* quote() - quote the string.
* esc() - escapes the provided string using the platform-specific escape character.
* css() - register CSS to be injected inside the HEAD tag of a resource.
* script() - register JavaScript to be injected inside the HEAD tag or before the closing BODY tag. Available the script attributes "async" and "defer".
* html() - register HTML to be injected inside the HEAD tag or before the closing BODY tag.
* chunk() - gets the specified chunk. Uses pdoTools if installed.
* snippet() - runs the specified snippet. Uses pdoTools if installed.
* processor() - runs the specified processor.
* is_auth() - determines if this user is authenticated in a specific context.
* is_guest() - determines if the user is a guest.
* can() - returns true if user has the specified policy permission.
* resource_id() - gets the id of the current resource. 
* template_id() - gets the template id of the current resource.
* user_id() - gets the id of the current user. 
* tv() - gets the specified TV of the current resource. 
* object() - to work with objects of MODX.
* collection() - to work with object collections of MODX.
* resource() - works with a resource object.
* resources() - works with a resource collection.
* user() - works with a user object.
* users() - works with a user collection.
* object_exists() - checks if the specified object exists.
* user_exists() - checks if the specified user exists.
* resource_exists() - checks if the specified resource exists.
* is_email() - validates the email.
* is_url() - validates the url.
* error() — logs to the error log for the ERROR log level.
* warn() — logs to the error log for the WARN log level.
* info() — logs to the error log for the INFO log level.
* debug() — logs to the error log for the DEBUG log level.
* context() - gets the name of the current context.
* query() - runs the query.


### Examples
**Check the user exists**
```
if (user_exists(['email'=>'admin@mail.com']) {
    // Exists
}
```

**Get the data from the cache**
```
$value = cache('key', 'my_data');
// Or 
$value = cache()->get('key', 'my_data');
```

**Send email**
```
email('pussycat@mail.ru', 'Subject','Email content');
// To the user
email_user('admin', $subject, $content);
```

**Redirect**
```
redirect($url);
//To the resource with the id = 5
redirect(5);
```

**The latest resource**
```
$resourceObject = resource()->last(); // Resource object
$resourceArray = resource()->last()->toArray(); // Resource data as array
```

**The last 10 resources**
```
$resObjects = resources()->last(10); 
```

**Array of the resource pagetitles of the parent with id = 20.**
```
$titles= resources()->where(['parent'=>20])->get('pagetitle'); // array('pagetitle 1', 'pagetitle 2', 'pagetitle 3')
```

**Set a value to the session**
```
session('key1.key2', 'value'); // => $_SESSION['key1']['key2'] = $value;
```
**Get the value from session**
```
$value = session('key1.key2');  // $value = $_SESSION['key1']['key2']
```

**Validates the email**
```
if (is_email($email)) {
   // Valid
}
```
**Remove child resources of the one with the id = 10**
```
resources()->where(['parent'=>10])->remove();
```

**Count blocked users**
```
$count = users()->profile()->where(['Profile.blocked'=>1])->count();
```
**Load script with the async attribute**
```
script('/path/to/script.js', 'async'); // <script async type="text/javascript" src="/path/to/script.js"></script>
```
**Get an array of users**
```
$userArray = query('select * from ' . table_name('modUser'). ' WHERE id < ?')->execute(10);
```
  
[Russian documentation](https://modzone.ru/blog/2016/12/31/helper-functions-for-modx/).