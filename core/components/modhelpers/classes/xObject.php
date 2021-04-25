<?php
namespace modHelpers;

use modX;
use xPDOQuery;
use PDO;
use xPDOObject;
use modTemplateVar;
use modResource;

class xObject
{
    /** @var  modX $modx */
    protected $modx;
    /** @var  xPDOQuery $query */
    protected $query;
    /** @var string $class */
    protected $class;
    /** @var  xPDOObject */
    protected $object;

    public function __construct(modX $modx, $class)
    {
        $this->modx = $modx;
        $this->class = $class;

        $this->query = $this->modx->newQuery($class);
        $this->query->setClassAlias($class);
        $this->query->limit(1);
    }

    /**
     * Copies the object fields and corresponding values to an associative array.
     * @return array
     */
    public function toArray()
    {
        $data = array();
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class));
        $tstart = microtime(true);
        if ($this->query->prepare() && $this->query->stmt->execute()) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            $data = $this->query->stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    /**
     * Set a field value by the field key or name.
     * @param array $data
     * @return bool
     */
    public function set(array $data)
    {
        if (empty($data)) return false;
        if (!$object = $this->object()) {
            return false;
        }
        /** @var xPDOObject $object */
        $object->fromArray($data, '', true);
        return $object->save();
    }

    /**
     * Create and save an object of the specified class.
     * @param array $data
     * @return bool|xPDOObject
     */
    public function create(array $data)
    {
        if (empty($data) || !class_exists($this->class) || !$object = $this->modx->newObject($this->class)) {
            return false;
        }
        /** @var xPDOObject $object */
        $object->fromArray($data, '', true);
        if (!$object->save()) {
            $this->modx->log(1, "[modHelpers] Can't create an object of class {$this->class}!");
            return false;
        }
        return $this->object = $object;
    }

    /**
     * Remove the persistent instance of an object permanently.
     * @return bool
     */
    public function remove()
    {
        if (!$object = $this->object()) {
            return false;
        }

        return $object->remove();
    }

    /**
     * Get a field value (or a set of values) by the field key(s) or name(s).
     * @param null $name
     * @return mixed
     */
    public function get($name = null)
    {
        if (!$object = $this->object()) {
            return null;
        }
        if (isset($name)) {
            $value = $object->get($name);
            if (is_null($value) && $object instanceof modResource) {
                /** @var modTemplateVar $tv */
                $tv = $object->getOne('TemplateVars', array('name' => $name));
                if (!$value = $tv->renderOutput($object->get('id'))) {
                    $value = $tv->get('default_text');
                }
            }
        }
        return isset($value) ? $value : $object;
    }

    /**
     * Get the first object of the class.
     * @param null $name
     * @return mixed
     */
    public function first($name = null)
    {
        $this->query->sortby('id', 'ASC');

        return $this->get($name);
    }

    /**
     * Get the last object of the class.
     * @param null $name
     * @return mixed
     */
    public function last($name = null)
    {
        $this->query->sortby('id', 'DESC');

        return $this->get($name);
    }

    /**
     * Add the Profile fields for the modUser class.
     * @param string $alias
     * @return \modHelpers\xObject
     */
    public function withProfile($alias = 'Profile')
    {
        if ($this->class === 'modUser') {
            $this->query->innerJoin('modUserProfile', $alias);
            $this->query->select($this->modx->getSelectColumns('modUser', 'modUser') . ',' . $this->modx->getSelectColumns('modUserProfile', $alias, '', array('id'), true));
        }
        return $this;
    }

    /**
     *
     * @return \modHelpers\xObject
     */
    public function limit()
    {
        return $this;
    }

    /**
     * Retrieve the parsed SQL.
     * @return string
     */
    public function toSql()
    {
        if (empty($this->query->query['columns'])) {
            $this->query->select($this->modx->getSelectColumns($this->class));
        }
        $this->query->prepare();
        return $this->query->toSQL();
    }

    /**
     * Dynamically handle calls to the class.
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->query = call_user_func_array(array($this->query, $method), $parameters);
        return $this;
    }
    /**
     * Check if a field is set.
     *
     * @param  string  $field
     * @return bool
     */
    public function __isset($field)
    {
        return ! is_null($this->get($field));
    }

    /**
     * Get a field value.
     *
     * @param  string  $field
     * @return mixed
     */
    public function __get($field)
    {
        return $this->get($field);
    }

    /**
     * Get object.
     * @return xPDOObject|object
     */
    public function object()
    {
        return $this->object ?: $this->modx->getObject($this->class, $this->query);
    }

    /**
     * Retrieve an object of the parent.
     * @param int $level
     * @return object
     */
    public function parent($level = 1)
    {
        $object = $existing = $this->object();
        for ($i = 1; $i<= $level; $i++) {
            if ($object && $object = $object->getOne('Parent')) {
                $existing = $object;
            }
        }
        return $object ?: $existing;
    }
}