## print_str()
Extends the print_r function. Converts a given value to the string format and prints or returns it. 

```print_str($value, $return = false, $template = '', $tag = 'output')```
- $value(mixed) - the input value.
- $return(bool) - if true the value will be returned. Otherwise it will be printed. As well as for the *print_r* function. Magical. For the print case can be omitted.
- $template(string) - HTML template to wrap the output or a tag name ('p','div','li').
- $tag(string) - a tag to be replaced.

```php
$nullVar;
$boolVar = true;
$objectVar = $modx->user;
$arrayVar = array(
	'key1' => 'value1'
	'key2' => 'value2'
	'key3' => array(
			'subkey1' => 'subvalue1',
			'subkey2' => 'subvalue2',
		)
);
$stringVar = 'Some text';

# Let's go
print_str($nullVar); // Output: 'NULL'
print_str($boolVar); // Output: 'TRUE'
print_str($objectVar);  // Output: <pre>
						//          Array (
						//              [id] => 1
						//              [username] => 'admin'
						//               ...
						//          )
						//	   </pre>

print_str($arrayVar);   // Output: <pre>
						//          Array (
						//                [key1] => 'value1'
                        //                [key2] => 'value2'
                        //                [key3] => Array(
                        //                            [subkey1] => 'subvalue1',
                        //                            [subkey2] => 'subvalue2',
                        //                          )
						//          )
						//	   </pre>
print_str($stringVar); // Output: 'Some text'

# Output using the return statement
return print_str($stringVar, true);
```
You can wrap the output with HTML tags or using a template.
```
# Wrap the output
print_str('The string wrapped by the div tag', false, 'div'); //<div>The string wrapped by the div tag</div>
// If the second parameter is false it can be omitted
print_str('The string wrapped by the div tag', 'div');

# Template wrapper for one-time output
print_str($stringVar, '<div style="color:red">[[+output]]</div>');
```
Or you can define a template for every output - specify the template in the "modhelpers_print_template" system setting.