## has_parent()
Checks the presence of the specified ancestors.

```has_parent($parent, $all = false):bool```
- $parent(integer|array) - Parent id or an array of ids.
- $all(boolean) - All parents must be present.   

This function has some magic.

#### Resource tree
```
1-----
|    |
|    3
|    |
|    4----
|    |   |
|    |   6 (current resource)
|    |
|    5----
|        |
|        7 
|
2---
   |
   8
```  
#### Check the parent for the current resource.
```php
if ( has_parent(4) ) {...}  // TRUE
if ( has_parent(1) ) {...}  // TRUE
if ( has_parent(2) ) {...}  // FALSE
// Several parents
if ( has_parent([4,2]) ) {...} // TRUE
if ( has_parent([4,2], true) ) {...} // FALSE
```
#### Check the parent for a specified resource.
The first argument - resource id.
The second argument - parent id.
```php
// Check the resource with id 7.
if ( has_parent(7, 5) ) {...}  // TRUE
if ( has_parent(7, [5,4]) ) {...}  // TRUE
if ( has_parent(7, [5,4], true) ) {...}  // FALSE
```