## app()  
Returns the available container instance. Can be used to register singleton classes or class dependencies.

```app($abstract = null, array $parameters = array())```
 - $abstract - alias or a class name.
 - $parameters - parameters.

### Methods
- bind() - to register a dependency.
- singleton() - to register a singleton class.
- instance() - to store an existing object as a singleton.
- bound() - checks if the given abstract type or alias has been bound.
- resolved() - checks if the given abstract type or alias has been resolved.

```php
# At the beginning define the dependency
app()->bind('classAlias', function($app, $parameters) use ($modx) {
	$modx->addPackage('myClass', 'class/path');
	return new myClass($parameters);
});
// And then you can use this call anywhere
$obj = app('classAlias', $param); 
```
If you need only one instance of some class in the application use the singleton method. 
```php
# At the beginning (plugin)
app()->singleton('alias', 'className');
// And then you can get it anywhere in the application. It will be the same object.
$serv = app('alias');

# Example with the Closure
app()->singleton('service', function($app, $parameters) use ($modx) {
	// define some login and return the result
	return new myServiceClass($parameters);
});
// Anywhere in the application
$obj = app('service', $param);

# Register already existing object as a singleton.
app()->instance('alias', $object);
```
Determine if the given abstract type or alias has been bound.
```php
if (!app()->bound('alias')) {
    app()->bind('alias', function(){...});
}
```
Check if the given abstract type or alias has been already resolved.
```php
if (!app()->resolved('service')) {
    $service = app('service', $config);  // create an instance and initiate it. 
}
```
