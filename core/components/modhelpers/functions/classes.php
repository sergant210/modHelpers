<?php
/***********************************************/
/*                Classes                      */

/***********************************************/

class extCacheManager
{
    /**
     * @var modCacheManager $cacheManager
     */
    public $cacheManager;

    public function __construct($cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function get($key, $options)
    {
        if (is_string($options)) {
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        }
        return $this->cacheManager->get($key, $options);
    }

    public function set($key, $value, $lifetime = 0, $options = array())
    {
        if (empty($options)) {
            if (is_string($lifetime)) {
                $options[xPDO::OPT_CACHE_KEY] = $lifetime;
                $lifetime = 0;
            } elseif (is_array($lifetime)) {
                $options = $lifetime;
                $lifetime = isset($options[xPDO::OPT_CACHE_EXPIRES]) ? $options[xPDO::OPT_CACHE_EXPIRES] : 0;
            }
        } elseif (is_string($options)) {
            $options[xPDO::OPT_CACHE_KEY] = $options;
        }
        return $this->cacheManager->set($key, $value, $lifetime, $options);
    }

    public function delete($key, $options)
    {
        if (is_string($options)) {
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        }
        return $this->cacheManager->delete($key, $options);
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array(array($this->cacheManager, $method), $parameters);
    }
}

class LogManager
{
    /** @var  modX $modx */
    protected static $modx;

    public static function error($message, $changeLevel = false, $target = '')
    {
        self::process(modX::LOG_LEVEL_ERROR, $message, $changeLevel, $target);
    }

    public static function warn($message, $changeLevel = false, $target = '')
    {
        self::process(modX::LOG_LEVEL_WARN, $message, $changeLevel, $target);
    }

    public static function info($message, $changeLevel = false, $target = '')
    {
        self::process(modX::LOG_LEVEL_INFO, $message, $changeLevel, $target);
    }

    public static function debug($message, $changeLevel = false, $target = '')
    {
        self::process(modX::LOG_LEVEL_DEBUG, $message, $changeLevel, $target);
    }

    protected static function process($level, $message, $changeLevel, $target)
    {
        if (!isset(self::$modx) || !(self::$modx instanceof modX)) {
            self::$modx = new modX();
        }
        if (is_string($changeLevel)) {
            $target = $changeLevel;
            $changeLevel = false;
        }
        if (is_array($message) || is_object($message)) $message = print_r($message, 1);
        if (self::$modx->getLogTarget() == 'HTML' || $target == 'HTML') {
            $message = '<style>.modx-debug-block{background-color:#002357;color:#fcffc4;margin:0;padding:5px} .modx-debug-block h5,.modx-debug-block pre {margin:0}</style><div>' . $message . '</div>';
        }
        if ($changeLevel) {
            $oldLevel = self::$modx->setLogLevel($level);
            self::$modx->log($level, $message, $target);
            self::$modx->setLogLevel($oldLevel);
        } else {
            self::$modx->log($level, $message, $target);
        }
    }
}

class ObjectManager
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

class CollectionManager
{
    /** @var  modX $modx */
    protected $modx;
    /** @var  xPDOQuery $query */
    protected $query;
    /** @var string class */
    protected $class;
    protected $alias;

    protected $where = array();
    protected $tvSelects = array();
    protected $tvJoins = array();
    protected $unions = array();

    public function __construct(&$modx, $class)
    {
        /** @var modX $modx */
        $this->modx =& $modx;
        $this->class = $class;
        $this->alias = $class;

        $this->query = $this->modx->newQuery($class);
        /*if ($class == 'modUser') {
            $this->alias = 'User';
        }*/
        $this->query->setClassAlias($this->alias);

        $this->query->limit(100);
    }

    public function alias($alias = '')
    {
        if (!empty($alias)) {
            $this->query->setClassAlias($alias);
            $this->alias = $alias;
        }
        return $this;
    }

    public function setClassAlias($alias = '')
    {
        return $this->alias($alias);
    }

    public function toArray($toString = false)
    {
        $data = array();
        $this->addSelect();
        $this->addJoins();
        $this->addWhere($this->query);
        $tstart = microtime(true);
        if ($this->query->prepare() && $this->query->stmt->execute()) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            $data = $this->query->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $unions = $this->addUnion();
        if (!empty($unions)) $data = array_merge($data, $unions);
        if ($toString) $data = print_r($data,1);

        return $data;
    }

    public function all($name = null)
    {
        $this->query->limit(0);
        return $this->get($name);
    }

    public function each($callback)
    {
        $collection = $this->toArray();
        if (is_callable($callback)) {
            $output = '';
            $idx = 1;
            foreach ($collection as $item) {
                $_res = call_user_func($callback, $item, $idx);
                if ($_res === false) {
                    break;
                }
                $output .= call_user_func($callback, $item, $idx);
                $idx++;
            }
            return $output;
        }
        return $collection;
    }

    public function get($name = null)
    {
        if (!empty($name)) {
            $collection = $this->toArray();
            if (is_string($name)) {
                $array = array();
                foreach ($collection as $item) {
                    $array[] = $item[$name];
                }
                return $array;
            } elseif (is_callable($name)) {
                return call_user_func($name, $collection, $this->modx);
            }
        }
        return $this->process();
    }

    public function set($data)
    {
        if (!$this->modx->hasPermission('save')) return 0;
        if (is_callable($data)) {
            $count = 0;
            $query = clone $this->query;
            $this->addWhere($query);
            $collection = $this->modx->getIterator($this->class, $query);
            foreach ($collection as $object) {
                $res = call_user_func_array($data, array(&$object, &$this->modx));
                if ($res !== false && $object->save()) {
                    $count++;
                }
            }
            return $count;
        } elseif (is_array($data)) {
            $this->query->command('UPDATE');
            $this->addWhere();
            $this->query->set($data);
//            $this->query->limit(0);
            $tstart = microtime(true);
            if ($this->query->prepare() && $this->query->stmt->execute()) {
                $this->modx->queryTime += microtime(true) - $tstart;
                $this->modx->executedQueries++;
                return $this->query->stmt->rowCount();
            }
        }
        return false;
    }

    public function remove()
    {
        if (!$this->modx->hasPermission('remove')) return 0;
        $this->query->command('DELETE');
        $this->addWhere();
        $this->query->limit(0);
        $tstart = microtime(true);
        if ($this->query->prepare() && $this->query->stmt->execute()) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            return $this->query->stmt->rowCount();
        }
        return false;
    }

    public function first($num = 0)
    {
        $this->query->sortby('id', 'ASC');
        $this->query->limit($num);
        return $this->process();
    }

    public function last($num = 0)
    {
        $this->query->sortby('id', 'DESC');
        $this->query->limit($num);

        return $this->process();
    }

    public function union($query)
    {
        $this->unions[] = $query;
        return $this;
    }

    public function where($condition)
    {
        $this->where[] = array('conjunction' => xPDOQuery::SQL_AND, 'where' => $condition);
        return $this;
    }

    public function orWhere($condition)
    {
        $this->where[] = array('conjunction' => xPDOQuery::SQL_OR, 'where' => $condition);
        return $this;
    }

    protected function addSelect()
    {
        if (!empty($this->tvSelects)) {
            foreach ($this->tvSelects as $select) {
                $this->query->select($select);
            }
        }
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class, $this->alias));
    }

    protected function addWhere($query = '')
    {
        if (empty($query)) $query =& $this->query;
        if (!empty($this->where)) {
            foreach ($this->where as $where) {
                $query->where($where['where'], $where['conjunction']);
            }
        }
    }

    public function addJoins()
    {
        if (!empty($this->tvJoins)) {
            foreach ($this->tvJoins as $k => $v) {
                $class = !empty($v['class']) ? $v['class'] : $k;
                $alias = !empty($v['alias']) ? $v['alias'] : $k;
                if (!is_numeric($alias) && !is_numeric($class)) {
                    $this->query->leftJoin($class, $alias, $v['on']);
                }
            }
        }
    }

    protected function addUnion()
    {
        $unions = $array = array();
        if (!empty($this->unions)) {
            foreach ($this->unions as $union) {
                if (is_string($union)) {
                    $stmt = $this->modx->prepare($union);
                    $tstart = microtime(true);
                    if ($stmt && $stmt->execute()) {
                        $this->modx->queryTime += microtime(true) - $tstart;
                        $this->modx->executedQueries++;
                        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $unions = array_merge($unions, $array);
                    }
                } elseif (is_array($union)) {
                    $unions[] = $union;
                }
            }
        }
        return $unions;
    }

    public function withTV($TV, $prefix = 'TV.')
    {
        $tvs = array_map('trim', explode(',', $TV));
        $tvs = array_unique($tvs);
        if (!empty($tvs)) {
            $q = $this->modx->newQuery('modTemplateVar', array('name:IN' => $tvs));
            $q->select('id,name,type,default_text');
            $tstart = microtime(true);
            if ($q->prepare() && $q->stmt->execute()) {
                $this->modx->queryTime += microtime(true) - $tstart;
                $this->modx->executedQueries++;
                $tvs = array();
                while ($tv = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    $name = strtolower($tv['name']);
                    $alias = 'TV' . $name;
                    $this->tvJoins[$name] = array(
                        'class' => 'modTemplateVarResource',
                        'alias' => $alias,
                        'on' => '`TV' . $name . '`.`contentid` = `' . $this->alias . '`.`id` AND `TV' . $name . '`.`tmplvarid` = ' . $tv['id'],
                        'tv' => $tv,
                    );
                    $this->tvSelects[$alias] = array('`' . $prefix . $tv['name'] . '`' => 'IFNULL(`' . $alias . '`.`value`, ' . $this->modx->quote($tv['default_text']) . ')');
                    $tvs[] = $tv['name'];
                }
            }
        }
        return $this;
    }

    /**
     * @return array|bool
     */
    protected function process()
    {
        $this->query->setClassAlias($this->alias);
        $this->addSelect();
        $this->addJoins();
        $this->addWhere($this->query);
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class, $this->alias));
        if (!$collection = $this->modx->getCollection($this->class, $this->query)) {
            return false;
        }
        return $collection;
    }

    public function profile($alias = 'Profile')
    {
        if ($this->class == 'modUser') {
            $this->query->innerJoin('modUserProfile', $alias);
            if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns('modUser', $this->alias) . ',' . $this->modx->getSelectColumns('modUserProfile', $alias, '', array('id'), true));
        }
        return $this;
    }

    public function count()
    {
        return count($this->toArray());
    }

    public function max($name)
    {
        $this->query->query['columns'] = array("max($name) as max");
        $value = $this->toArray();
        return $value[0]['max'];
    }

    public function min($name)
    {
        $this->query->query['columns'] = array("min($name) as min");
        $value = $this->toArray();
        return $value[0]['min'];
    }

    public function avg($name)
    {
        $this->query->query['columns'] = array("avg($name) as avg");
        $value = $this->toArray();
        return $value[0]['avg'];
    }

    public function sum($name)
    {
        $this->query->query['columns'] = array("sum($name) as sum");
        $value = $this->toArray();
        return $value[0]['sum'];
    }

    /**
     * @return string
     */
    public function toSql()
    {
        $this->addSelect();
        $this->addJoins();
        $this->addWhere($this->query);
//DEBUGGING
//log_error($this->query->query);
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class, $this->alias));
        $this->query->prepare();
        return $this->query->toSQL();
    }

    public function members($group)
    {
        if ($this->class == 'modUser') {
            switch (true) {
                case is_numeric($group):
                    $this->query->where(escape($this->alias).'.`id` IN (SELECT `member` FROM ' . table_name('modUserGroupMember') . " WHERE `user_group` = $group)");
                    break;
                case is_string($group):
                    $query = escape($this->alias).'.`id` IN (SELECT `groupMember`.`member` FROM ' . table_name('modUserGroupMember') . ' as `groupMember`' .
                        ' JOIN ' . table_name('modUserGroup') . ' as `Groups` ON `Groups`.`id` = `groupMember`.`user_group`' .
                        " WHERE `Groups`.`name` LIKE '$group')";
                    $this->query->where($query);
                    break;
            }
        }

        return $this;
    }

    /**
     * Call xPDOQuery methods.
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


class QueryManager
{
    /** @var  modX $modx */
    protected $modx;
    protected $query;
    protected $bindings;

    public function __construct(&$modx, $query)
    {
        /** @var modX $modx */
        $this->modx =& $modx;
        $this->query = $query;

    }

    public function bind($bindings)
    {
        if (!empty($bindings)) {
            if (!is_array($bindings)) {
                $this->bindings = func_get_args();
            } else {
                $this->bindings = $bindings;
            }
        }
        return $this;
    }

    public function execute($bindings = '')
    {
        if (!empty($bindings)) {
            if (!is_array($bindings)) {
                $this->bindings = func_get_args();
            } else {
                $this->bindings = $bindings;
            }
            $this->bind($bindings);
        }
        $tstart = microtime(true);
        $stmt = $this->modx->prepare($this->query);
        if ($stmt->execute($this->bindings)) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            return $stmt->fetchAll();
        }
        return false;
    }

    public function count($bindings = '')
    {
        if (!empty($bindings)) {
            if (!is_array($bindings)) {
                $this->bindings = func_get_args();
            } else {
                $this->bindings = $bindings;
            }
            $this->bind($bindings);
        }
        $tstart = microtime(true);
        $stmt = $this->modx->prepare($this->query);
        if ($stmt->execute($this->bindings)) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            return $stmt->rowCount();
        }
        return false;
    }

}