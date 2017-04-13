## load_model()
Loads a model for a custom table.

```load_model($class, $tableName, $callable)```    
- $class - name of the new or the existing class.
- $tableName - table name without the table prefix.
- $callable - the closure. For existing models can be call as the second argument. 

This function is intended to simplify working with custom tables. It is very easy to use.
####Step 1. Create a table via phpMyAdmin.
For example, lets create a table for some objects and call it 'modx_objects'. 

#### Step 2. Create a model file for your table.
```php
<?php # core/models/objects.php
// Our model will be called 'Object'
if (!class_exists('Object')) {
    class Object extends xPDOObject{}
    class Object_mysql extends Object{}

    load_model('Object', 'objects', function ($model) {
        /** @var modHelpersModelBuilder $model */
        $model->id('id')->pk(); // unsigned integer type column with the primary index.
        $model->varchar('name', 100)->setDefault('string')->rulePregMatch('invalid','/^[a-zA-Z\s]+$/','You can not use digits in the name!');
        $model->text('description')->null()->alias('desc');
        $model->arr('properties')->null(); // array phptype
        $model->int('rid',true)->aggregate('Resource',array('class'=>'modResource','foreign'=>'id','cardinality'=>'one', 'owner'=>'foreign'))->index();
        $model->int('createdby')->unsigned()->aggOneForeign('CreateUser','modUser','id')->index(); 
        $model->int('editedby', true)->aggOneForeign('EditUser','modUser','id')->index(); 
        $model->datetime('createdon'); // if the type of the table field is datetime or timestamp.
        $model->bigint('editedon',true)->phpType('datetime');// if you store the date in the UNIX format.
    });
}
```
By the way, you can enhance any existing model. For example, the model of the resource
```php
load_model('modResource', function ($model) {
    $model->text('tags'); // add "tags" field for the resource model
}
```
#### Step 3. Load the model file in a plugin.
```php
switch ($modx->event->name) {
	case 'OnMODXInit':
	    include_once MODX_CORE_PATH . 'models/objects.php';
		break;
}
```
That's all. Now you can use any xPDO methods.
```php
$object = $modx->getObject('Object', 1); // or object('Object',1).
$Creater = $object->CreateUser->username;
// Validation
$obj->set('name', 'Super 777'); // Digits are not allowes -> validation error
if (!$obj->validate()) {
    $validator = $obj->getValidator();
    log_error('[Validator] ' . $validator->getMessages()[0]['message']);
} else {
	$obj->save();
}
```
Created model is saved to the cache after the first use. So if you change your model you should delete the cached model file lying in the cache folder *core/cache/default/yourmodelclassname_map.php*. 

### Model class methods
* char($name, $presision=255) - adds a char column to the model. 
* varchar($name, $presision=255) - adds a varchar column to the model.
* text($name) - adds a text column to the model.
* mediumText($name) - adds a mediumText column to the model.
* longText($name) - adds alongText column to the model.
* id($name, $length=10) - adds an unsigned integer column to the model.
* int($name, $length=10, $unsigned=false) - adds an integer column to the model. The third argument can be pass instead of the second one. Magic. 
* tinyInt($name, $length=3, $unsigned=false) - adds a timyint column to the model. The third argument can be pass instead of the second one. Magic. 
* smallInt($name, $length=5, $unsigned=false) - adds a smallint column to the model. The third argument can be pass instead of the second one. Magic. 
* mediumInt($name, $length=8, $unsigned=false) - adds a mediumint column to the model. The third argument can be pass instead of the second one. Magic. 
* bigInt($name, $length=20, $unsigned=false) - adds a bigint column to the model. The third argument can be pass instead of the second one. Magic. 
* float($name, $precision='12,2', $unsigned=false) - adds a float column to the model. The third argument can be pass instead of the second one. Magic. 
* decimal($name, $precision='12,2', $unsigned=false) - adds a float column to the model. The third argument can be pass instead of the second one. Magic. 
* double($name, $precision='20,2', $unsigned=false) - adds a float column to the model. The third argument can be pass instead of the second one. Magic. 
* bool($name) - adds a boolean column to the model. 
* arr/asArray($name) - adds an array column to the model.
* json($name) - adds an json column to the model.
* date($name) - adds a date column to the model.
* datetime($name) - adds a datetime column to the model.
* timestamp($name) - adds a datetime column to the model.
* time($name) - adds a time column to the model.
* aggregate($alias, $attributes) - adds an aggregate relationship. $attributes is an array with keys 'class', 'local', 'foreign', 'cardinality' and 'owner'.
* composite($alias, $attributes) - adds an composite relationship. $attributes is an array with keys 'class', 'local', 'foreign', 'cardinality' and 'owner'.
All these methods return a model's column object.

### Column class methods
* phpType($type) - sets a phptype of the column. Needed for some cases. For example, if you store the date in the UNIX timestamp - the dbtype is integer, but the phptype is datetime or timestamp. 
* null() - column can be nullable.
* setDefault($value) - sets the default value for a column.
* index($alias=''') - sets an index for a column. If you specify the same alias for multiple columns, the group index will be created.
* pk() - sets the primary index for column or columns.
* fk($alias='') - works like the "index" method.
* unique($alias='') - sets the unique index.
* fulltext($alias='') - sets the fulltext index.
* alias($alias) - set an alias for a column.
* rulePregMatch($name, $rule, $message) - sets a rule for a Regex validation (see [documentation](https://docs.modx.com/xpdo/2.x/getting-started/creating-a-model-with-xpdo/defining-a-schema/validation-rules-in-your-schema)).
* ruleXPDO($name, $rule, $message, $value = NULL) - sets a rule for a xPDOValidationRule validation.
* ruleCallback($name, $rule, $message) - sets a rule for a callback validation.
* aggregate($alias, $attributes) - specified an aggregate relationship for a columns. $attributes is a associative array with keys 'class', 'foreign', 'cardinality' and 'owner'. You can use these short methods - 
	* aggregateManyForeign/aggManyForeign($alias,$class,$foreign) - adds an aggregate relationship with the specified cardinality "many" and owner "foreign".
	* aggregateOneForeign/aggOneForeign($alias,$class,$foreign) - adds an aggregate relationship with the specified cardinality "one" and owner "foreign".
	* aggregateManyLocal/aggManyLocal($alias,$class,$foreign) - adds an aggregate relationship with the specified cardinality "many" and owner "local".
	* aggregateOneLocal/aggOneLocal($alias,$class,$foreign) - adds an aggregate relationship with the specified cardinality "one" and owner "local".
* composite($alias, $attributes) - specified a composite relationship for a columns. $attributes is a associative array with keys 'class', 'foreign', 'cardinality' and 'owner'. You can use these short methods - 
	* compositeManyForeign/comManyForeign($alias,$class,$foreign) - adds a composite relationship with the specified cardinality "many" and owner "foreign".
	* compositeOneForeign/comOneForeign($alias,$class,$foreign) - adds a composite relationship with the specified cardinality "one" and owner "foreign".
	* compositeManyLocal/comManyLocal($alias,$class,$foreign) - adds a composite relationship with the specified cardinality "many" and owner "local".
	* compositeOneLocal/comOneLocal($alias,$class,$foreign) - adds a composite relationship with the specified cardinality "one" and owner "local".
