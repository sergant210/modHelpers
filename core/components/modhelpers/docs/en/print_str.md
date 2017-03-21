## print_str()
Extends the print_r function. Converts a given value to the string format and prints or returns it. 

```print_str($value, $return = false, $template = '', $tag = 'output')```
- $value(mixed) - the input value.
- $return(bool) - if true the value will be returned. Otherwise it will be printed. As well as for the *print_r* function. Magical. For the print case can be omitted.
- $template(string) - HTML template to wrap the output.
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
print_str($objectVar); // Output: <pre>
									Array (
										[id] => 1
										[username] => 'admin'
										...
									)
								   </pre>

print_str($objectVar); // Output: <pre>
									Array (
										[key1] => 'value1'
                                        [key2] => 'value2'
                                        [key3] => array(
                                        			[subkey1] => 'subvalue1',
                                        			[subkey2] => 'subvalue2',
									)
								   </pre>
print_str($stringVar); // Output: 'Some text'

# Output using the return statement
return print_str($stringVar, true);

# Wrap the output
print_str($stringVar, false, '<div style="color:red">[[+output]]</div>');
// The second parameter can be omitted
print_str($stringVar, false, '<div style="color:red">[[+output]]</div>');
```