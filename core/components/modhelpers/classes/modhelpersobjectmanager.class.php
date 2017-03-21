<?php

class modHelpersObjectManager
{
    /** @var  modX $modx */
    protected $modx;
    /** @var  xPDOQuery $query */
    protected $query;
    /** @var string class */
    protected $class;


    public function __construct(&$modx, $class)
    {
        /** @var modX $modx */
        $this->modx =& $modx;
        $this->class = $class;

        $this->query = $this->modx->newQuery($class);
        $this->query->setClassAlias($class);
        $this->query->limit(1);
        if ($class == 'modUser') {
            $this->query->innerJoin('modUserProfile', 'Profile');
            $this->query->select($modx->getSelectColumns('modUser', 'modUser') . ',' . $modx->getSelectColumns('modUserProfile', 'Profile', '', array('id'), true));
        }
    }

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

    public function set(array $data)
    {
        if (empty($data)) return $this;
        if (!$object = $this->modx->getObject($this->class, $this->query)) {
            return false;
        }
        /** @var xPDOObject $object */
        $object->fromArray($data, '', true);
        $object->save();
        return $object->save();
    }

    public function remove()
    {
        if (!$object = $this->modx->getObject($this->class, $this->query)) {
            return false;
        }
        /** @var xPDOObject $object */
        $object->remove();
        return $object->remove();
    }

    public function get($name = null)
    {
        if (!$object = $this->modx->getObject($this->class, $this->query)) {
            return false;
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

    public function first($name = null)
    {
        $this->query->sortby('id', 'ASC');

        return $this->get($name);
    }

    public function last($name = null)
    {
        $this->query->sortby('id', 'DESC');

        return $this->get($name);
    }

    public function limit()
    {
        return $this;
    }

    public function toSql()
    {
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class));
        $this->query->prepare();
        return $this->query->toSQL();
    }

    /**
     * Выполняет динамические методы.
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->query = call_user_func_array(array($this->query, $method), $parameters);
        return $this;
    }
}