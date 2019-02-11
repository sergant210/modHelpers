## string()
Wraps the string for further manipulation.

```string($string)```
- (string) $string - a string for manipulation.

```php
$string = string('Some String')
                               ->toLower()                 // some string
                               ->replaceAll(' ', '_')      // some_string
                               ->replace('some', 'new')    // new_string
                               ->first(4)                  // new_
                               ->sha1(10)                  // cdabae2ca0
                               ->undo()                    // Some String
                               ->wrap('<div>','</div>')    // <div>Some String</div>
                               ->erase(0, 5)               // Some String</div>
                               ->special_encode()		   // Some String&lt;\/div&gt;
                               ->length();                 // 23
							
echo $string->origin(); // Some String
echo $string->get();    // Some String</div>
// OR
echo $string;           // Some String</div>
```
To know about all methods see the [Str](https://github.com/sergant210/modHelpers/blob/master/core/components/modhelpers/classes/Str.php) class.