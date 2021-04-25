## build_tree
Builds a tree of resources from a given array.

```build_tree(array $data, $parent = 0, $level = 10, array $options = []):array```

- (array) $data - an array. For example, list of the resources. 
- (int) $parent - filters the data array by the parent field.
- (int) $level - number of levels.
- (array) $options - allows you to redefine the names of the id and parent fields - `['idField' => 'custom_id', 'parentField' => 'custom_parent',]`. By default, the idField name is 'id' and the parentField name is 'parent'.
  
### Example of creating a site menu using the Smarty template engine syntax (see [ZoomX](https://github.com/sergant210/ZoomX))
#### 1. Create a snippet with the name BuildMenu
```php
// BuildMenu
<?php
$query = $modx->newQuery('modResource', [...]);
if ($query->prepare() && $query->stmt->execute()) {
    $resource = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
}
return build_tree($resource);
```

#### 2. Create HTML partial (file shunk) 
```html
// MenuItem.html
{if isset($item['children'])}
<li class="tplParentRow {$classes}">
  <a href="{$item['uri']}">{$item['pagetitle']}</a>
  <ul class="tplInner">
    {foreach $item['children'] as $child}
      {include 'MenuItem.html' item=$child}
    {/foreach}
  </ul>
</li>
{else}
<li>
  <a href="{$item['uri']}">{$item['pagetitle']}</a>
</li>
{/if}
```


#### 3. Create an HTML layout (template)
```html
<body>
  <nav class="navbar">
    <ul class="tplOuter">
      {$items = $modx->runSnippet('BuildMenu')}
      {foreach $items as $item}
        {include 'MenuItem.html' item=$item}
      {/foreach}
    </ul>
  </nav>
...
</body>
```