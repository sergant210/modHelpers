## query()
Works with raw SQL queries.

```query($query)```
- $query - SQL query.   

A few methods are available yet.
* bind(). It used to bind placeholders. Magical. The data can be transmitted, or in an array or a comma.
* execute(). Executes the query. It returns an array with the data or false.
* toString(). Executes the query and returns a string of data.
* count(). Returns the number of rows or false.
```php
# Simple query
$array = query('select * from ' . table_name('modUser'). ' WHERE id < 10')->execute();
# Prepared statement
$array = query('select * from ' . table_name('modUser'). ' WHERE id < ?')->execute(10);

# Example
$count = query('select * from ' . table_name('modUser'). ' WHERE id BETWEEN ? AND ?')->bind(10,20)->count();
// or so
$count = query('select * from ' . table_name('modUser'). ' WHERE id BETWEEN ? AND ?')->count(10,20);
// or so
$count = query('select * from ' . table_name('modUser'). ' WHERE id BETWEEN ? AND ?')->count(array(10,20));
```