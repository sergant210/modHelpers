## timer()
Time logger.

```timer($timer = 'Default')```

- $timer (string) - timer name.


### Examples
#### Default timer
```php
timer()->checkpoint('Start counting');
...some code 1
timer()->checkpoint('Step 1');  // the shorter syntax is cp()
...some code 2
echo timer()->cp('Step 2')->total();
// or somewhere below - timer()->total();

# Result
0,000: Start counting.
1,000: Step 1.
1,000: Step 2.
2,000: Total time.
```
#### Multiple timers
```php
timer('timer1')->cp('Start counting');
...some code
timer('timer2')->cp('Start counting');
...some code
echo timer('timer1')->cp('Stop timer1 countdown')->total();
...some code
echo timer('timer2')->cp('Stop timer2 countdown')->total('Custom total message');
```

### Formatting
- checkpoint - checkpoint output format. By-default, "%.3f: %s.".
- total - total output format. By-default, "%.3f: %s."
- eol - end of line. By-default, "\n"
```php
timer()->format([
    'checkpoint'=>'%.6f: %s.', 
    'total' => '<span style="color:red">%.6f: %s.</span>',
]);
```

### Other methods
- totalSec - gets total difference in seconds.
- isEmpty - checks if the time log contains any checkpoints.
- reset - clears the time log.