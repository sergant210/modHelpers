<?php
namespace modHelpers;

class ModelColumn
{
    public $attributes = array();
    public $index = array();
    public $aggregate = array();
    public $composite = array();
    public $alias = array();
    public $rules = array();
    public $name;
    protected $_default;

    public function __construct($dbtype, $name, $attributes = array())
    {
        $this->name = $name;
        $types = $this->getTypes($dbtype);
        if (isset($attributes['unsigned'])) {
            if ($attributes['unsigned']) $attributes['attributes'] = 'unsigned';
            unset($attributes['unsigned']);
        }
        $this->attributes = array_merge($types, $attributes);
        $this->attributes['null'] = false;
    }

    protected function getTypes($dbtype)
    {
        if ($dbtype == 'boolean') $dbtype = 'tinyint';
        switch ($dbtype) {
            case 'int': case 'tinyint': case 'smallint': case 'mediumint': case 'bigint': case 'year':
            $phptype = 'integer';
            $this->_default = 0;
            break;
            case 'char': case 'varchar': case 'tinytext': case 'text': case 'mediumtext': case 'longtext':
            $phptype = 'string';
            $this->_default = '';
            break;
            case 'date':
                $phptype = 'date';
                $this->_default = '0000-00-00';
                break;
            case 'datetime': case 'timestamp': case 'time':
            $phptype = 'datetime';
            $this->_default = '0000-00-00 00:00:00';
            break;
            case 'float': case 'decimal': case 'double':
            $phptype = 'float';
            $this->_default = 0;
            break;
            case 'array':
                $dbtype = 'text';
                $phptype = 'array';
                $this->_default = '';
                break;
            case 'json':
                $dbtype = 'text';
                $phptype = 'json';
                $this->_default = '';
                break;
        }
        return compact('dbtype','phptype');
    }
    public function phpType($type)
    {
        $this->attributes['phptype'] = $type;

        return $this;
    }
    public function unsigned()
    {
        $this->attributes['attributes'] = 'unsigned';
        return $this;
    }
    public function null($isNullable = true)
    {
        $this->attributes['null'] = $isNullable;
        return $this;
    }
    public function setDefault($value)
    {
        $this->attributes['default'] = $value;
        $this->attributes['null'] = false;
        return $this;
    }

    public function index($alias = '')
    {
        $this->attributes['index'] = 'index';
        return $this->setIndex($alias);
    }
    public function pk()
    {
        $this->attributes['index'] = 'pk';
        return $this->setIndex('PRIMARY', true);
    }
    public function unique($alias = '')
    {
        $this->attributes['index'] = 'unique';
        return $this->setIndex($alias, false, true);
    }
    public function fk($alias = '')
    {
        $this->attributes['index'] = 'fk';
        return $this->setIndex($alias);
    }
    public function fulltext($alias = '')
    {
        $this->attributes['index'] = 'fulltext';
        return $this->setIndex($alias, false, false, 'FULLTEXT');

    }
    protected function setIndex($alias='', $primary=false, $unique=false, $type='BTREE')
    {
        if (empty($alias)) {
            $index = $alias = $this->name;
        } else {
            $index = $alias;
        }
        $this->index[$index] = array(
            'alias' => $alias,
            'primary' => $primary,
            'unique' => $unique,
            'type' => $type,
            'columns' =>
                array(
                    $this->name =>
                        array(
                            'length' => '',
                            'collation' => 'A',
                            'null' => $this->attributes['null'],
                        ),
                ),
        );
        return $this;
    }

    public function getDefault()
    {
        if (isset($this->attributes['default'])) {
            $value = $this->attributes['default'];
        } else {
            $value = $this->attributes['null'] === true ? NULL : $this->_default;
        }
        return $value;
    }

    public function alias($alias)
    {
        $this->alias = array($alias => $this->name);
        return $this;
    }

    protected function addRule($name, $type, $rule, $message, $value = NULL)
    {
        $this->rules[$this->name][$name] = isset($value) ? compact('type','rule','message','value') :compact('type','rule','message');
        return $this;
    }

    public function rulePregMatch($name, $rule, $message)
    {
        return $this->addRule($name, 'preg_match', $rule, $message);
    }
    public function ruleXPDO($name, $rule, $message, $value = NULL)
    {
        return $this->addRule($name, 'xPDOValidationRule', $rule, $message, $value);
    }
    public function ruleCallback($name, $rule, $message)
    {
        return $this->addRule($name, 'callback', $rule, $message);
    }

    public function aggregate($alias, $attributes)
    {
        $this->composite = array();
        $this->aggregate[$alias] = array(
            'class' => $attributes['class'],
            'local' => $this->name,
            'foreign' => $attributes['foreign'],
            'cardinality' => $attributes['cardinality'],
            'owner' => $attributes['owner'],
        );
        return $this;
    }

    public function composite($alias, $attributes)
    {
        $this->aggregate = array();
        $this->composite[$alias] = array(
            'class' => $attributes['class'],
            'local' => $this->name,
            'foreign' => $attributes['foreign'],
            'cardinality' => $attributes['cardinality'],
            'owner' => $attributes['owner'],
        );
        return $this;
    }
    /**
     * Call aggregate and composite methods.
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (count($parameters) < 3) return $this;
        switch ($method) {
            case 'aggregateManyForeign':
            case 'aggManyForeign':
                $method = 'aggregate';
                $cardinality = 'many';
                $owner = 'foreign';
                break;
            case 'aggregateOneForeign':
            case 'aggOneForeign':
                $method = 'aggregate';
                $cardinality = 'one';
                $owner = 'foreign';
                break;
            case 'aggregateManyLocal':
            case 'aggManyLocal':
                $method = 'aggregate';
                $cardinality = 'many';
                $owner = 'local';
                break;
            case 'aggregateOneLocal':
            case 'aggOneLocal':
                $method = 'aggregate';
                $cardinality = 'one';
                $owner = 'local';
                break;
            case 'compositeManyForeign':
            case 'comManyForeign':
                $method = 'composite';
                $cardinality = 'many';
                $owner = 'foreign';
                break;
            case 'compositeOneForeign':
            case 'comOneForeign':
                $method = 'composite';
                $cardinality = 'one';
                $owner = 'foreign';
                break;
            case 'compositeManyLocal':
            case 'comManyLocal':
                $method = 'composite';
                $cardinality = 'many';
                $owner = 'local';
                break;
            case 'compositeOneLocal':
            case 'comOneLocal':
                $method = 'composite';
                $cardinality = 'one';
                $owner = 'local';
                break;
            default:
                log_error('[Model] Incorrect method name.');
                return $this;
        }
//        if (!isset($cardinality)) return $this;
        $arguments = array();
        $arguments[] = $parameters[0];
        $arguments[] = array(
            'class' => $parameters[1],
            'foreign' => $parameters[2],
            'cardinality' => $cardinality,
            'owner' => $owner,
        );

        return call_user_func_array(array($this, $method), $arguments);
    }
}