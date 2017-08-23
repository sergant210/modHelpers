## filter_data()  
Filters a given data according to the specified rules.

```filter_data(array $data, array $rules, $intersect = false)```
 - $data - An array of the source data.
 - $rules - rules.
 - $intersect - True - to return all data. False - to return only filtered values. 
 
 ### Filters
 - int, integer - The scalar value being converted to an integer.
 - string - Strips tags from the given value and trims it.
 - float - Returns the float value of the given variable.
 - array - Converts a value to an array.
 - bool, boolean - Returns TRUE for "1", "true", "on" and "yes". Returns FALSE otherwise. 
 - alpha - Returns the alphabetic characters of the given value.
 - alpha_num - Returns the alphabetic characters and digits of the given value.
 - num - Returns only digits of the given value.
 - email - Removes all characters except letters, digits and !#$%&'*+-=?^_`{|}~@.[]. 
 - url - Removes all characters except letters, digits and $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=.
 - limit - Limits the number of characters in the given value. After colon you can specify two parameters - number of symbols and the ending string, separated by comma ('limit:20,...').
 - fromJSON - Decodes a JSON string.
 - toJSON -  Returns the JSON representation of a value.
 - default - Returns the default value if the specified value does not exist ('default:Default value').
 
 In addition you can specify any php function, Closure or class name.
 
 ### Examples
```php
# Form data
// $_POST = ['id' => '5', 'name' => '   John', 'fullname' => '   Silver   ', 'checkbox1' => 'on']
$rules = [
	'id' => 'int', // $_POST['id'] being converted to an integer.
	'name' => 'string', // Strip tags from $_POST['name'] and trim it.
	'checkbox1' => 'bool',  // Validate as checkbox.
	'checkbox2' => 'bool'  //  Validate as checkbox.
];
// Return all data (fullname exists and not filtered)
$filteredData = filter_data($_POST, $rules);
//  ['id' => 5, 'name' => 'John', 'fullname' => '   Silver   ', 'checkbox1' => true, 'checkbox2' => false]

// Return only filtered (fullname does not exist)
$filteredData = filter_data($_POST, $rules, true);
//  ['id' => 5, 'name' => 'John', 'checkbox1' => true, 'checkbox2' => false]
```
Use php functions
```php
// $_POST = ['name' => 'john doe'];
$post = filter_data($_POST, ['name' => 'ucwords']);
// $post = ['name' => 'John Doe']
```
Use Closure
```php
// $_POST = ['name' => 'John Doe'];
$rules = [
	'name' => function($value) {
		list($first, $last) = explode(' ', $value);
		return compact('first','last'); 
	},
];
$post = filter_data($_POST, $rules);
// Result 
Array (
    [name] => Array (
                   [first] => John
                   [last] => Doe
              )
)
```
Use class names
```php
// $_POST = ['user' => 5];
$post = filter_data($_POST,[
                             'user' => 'modUser',
                          ]);
// $post['user'] is an object of the class modUser with id 5. 
```
Use parameters after colon (:)
```php
// $_POST = ['title' => 'It's a very long string'];
$post = filter_data($_POST,[
                             'title' => 'limit:15',
                          ]);
// $post = ['title' => 'It's a very ...']

# Set the second parameter
$post = filter_data($_POST,[
                             'title' => 'limit:15,#',
                          ]);
// $post = ['title' => 'It's a very lo#']
```
To use several filters separate them by |
```php
// $_POST = ['title' => '   It's a very long string  '];
$post = filter_data($_POST,[
                             'title' => 'trim|limit:15',
                          ]);
// $post = ['title' => 'It's a very ...']
```