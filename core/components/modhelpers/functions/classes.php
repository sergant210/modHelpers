<?php
/***********************************************/
/*                Classes                      */
/***********************************************/
class extCacheManager {
    /**
     * @var modCacheManager $cacheManager
     */
    public $cacheManager;

    public function __construct($cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
    public function get($key, $options) {
        if (is_string($options)) {
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        }
        return $this->cacheManager->get($key, $options);
    }
    public function set($key, $value, $lifetime = 0, $options = array()) {
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
    public function delete($key, $options) {
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

    public static function error($message, $changeLevel = false)
    {
        self::process(modX::LOG_LEVEL_ERROR, $message, $changeLevel);
    }
    public static function warn($message, $changeLevel = false)
    {
        self::process(modX::LOG_LEVEL_WARN, $message, $changeLevel);
    }
    public static function info($message, $changeLevel = false)
    {
        self::process(modX::LOG_LEVEL_INFO, $message, $changeLevel);
    }
    public static function debug($message, $changeLevel = false)
    {
        self::process(modX::LOG_LEVEL_DEBUG, $message, $changeLevel);
    }

    protected static function process($level, $message, $changeLevel)
    {
        if (!isset(self::$modx) || !(self::$modx instanceof modX)) {
            self::$modx = new modX();
        }
        if (is_array($message) || is_object($message)) $message = print_r($message,1);
        self::$modx->setLogTarget('HTML');
        if (self::$modx->getLogTarget() == 'HTML') {
            $message = '<style>.modx-debug-block{background-color:#002357;color:#fcffc4;margin:0;padding:5px} .modx-debug-block h5,.modx-debug-block pre {margin:0}</style>' . $message;
        }
        if ($changeLevel) {
            $oldLevel = self::$modx->setLogLevel($level);
            self::$modx->log($level, $message);
            self::$modx->setLogLevel($oldLevel);
        } else {
            self::$modx->log($level, $message);
        }
    }
}

class ObjectManager {
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
            $this->query->select($modx->getSelectColumns('modUser','modUser').','.$modx->getSelectColumns('modUserProfile','Profile','',array('id'),true));
        }
    }

    public function toArray()
    {
        $data = array();
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class));
        if ($this->query->prepare() && $this->query->stmt->execute()) {
            $data = $this->query->stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function set(array $data) {
        if (empty($data)) return $this;
        if (!$object = $this->modx->getObject($this->class, $this->query)) {
            return false;
        }
        /** @var xPDOObject $object */
        $object->fromArray($data,'', true);
        $object->save();
        return $object;
    }

    public function remove() {
        if (!$object = $this->modx->getObject($this->class, $this->query)) {
            return false;
        }
        /** @var xPDOObject $object */
        $object->remove();
        return $this;
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
                $tv = $object->getOne('TemplateVars', array('name'=> $name));
                if (! $value = $tv->renderOutput($object->get('id'))) {
                    $value = $tv->get('default_text');
                }
            }
        }
        return isset($value) ? $value : $object;
    }
    public function first($name = null) {
        $this->query->sortby('id', 'ASC');

        return $this->get($name);
    }
    public function last($name = null) {
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
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->query = call_user_func_array(array($this->query, $method), $parameters);
        return $this;
    }
}

class CollectionManager {
    /** @var  modX $modx */
    protected $modx;
    /** @var  xPDOQuery $query */
    protected $query;
    /** @var string class */
    protected $class;

    protected $where = array();

    public function __construct(&$modx, $class)
    {
        /** @var modX $modx */
        $this->modx =& $modx;
        $this->class = $class;

        $this->query = $this->modx->newQuery($class);
        $this->query->setClassAlias($class);
        if ($class == 'modUser') {
            $this->query->setClassAlias('User');
        } else {
            $this->query->setClassAlias($class);
        }

        $this->query->limit(20);
    }

    public function toArray()
    {
        $data = array();
        $this->addWhere($this->query);
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class));
        if ($this->query->prepare() && $this->query->stmt->execute()) {
            $data = $this->query->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function all($name = null) {
        $this->query->limit(0);
        return $this->get($name);
    }

    public function get($name = null)
    {
        if (!empty($name)) {
            $this->addWhere($this->query);
            $this->query->select($name);
            $array = array();
            $stmt = $this->query->prepare();
            if ($stmt && $stmt->execute()) {
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $array[] = $row[$name];
                }
            }
            return $array;
        }
        return $this->process();
    }
    public function set(array $data) {
        $query = clone $this->query;
        $query->command('UPDATE');
        $this->addWhere($query);
        $query->set($data);
        $query->limit(0);
        if (!($query->prepare() && $query->stmt->execute())) {
            return $this;
        }
        return $this->process();
    }

    public function remove()
    {
        $query = clone $this->query;
        $query->command('DELETE');
        $this->addWhere($query);
        $query->limit(0);
        if (!($query->prepare() && $query->stmt->execute())) {
            return $this;
        }
        return $this;
    }
    public function first($num = 0) {
        $this->query->sortby('id', 'ASC');
        $this->query->limit($num);
        return $this->process();
    }

    public function last($num = 0) {
        $this->query->sortby('id', 'DESC');
        $this->query->limit($num);

        return $this->process();
    }
    public function where($condition) {
        $this->where[] = $condition;
        return $this;
    }

    protected function addWhere(&$query) {
        if (!empty($this->where)) {
            foreach ($this->where as $where) {
                $query->where($where);
            }
        }
    }
    /**
     * @return array|bool
     */
    protected function process()
    {
        $this->query->setClassAlias($this->class);
        $this->addWhere($this->query);
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class, $this->class));
        if (!$collection = $this->modx->getCollection($this->class, $this->query)) {
            return false;
        }
        return $collection;
    }

    public function profile()
    {
        if ($this->class == 'modUser') {
            $this->query->innerJoin('modUserProfile', 'Profile');
            if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns('modUser','User').','.$this->modx->getSelectColumns('modUserProfile','Profile','',array('id'),true));
        }
        return $this;
    }

    public function count() {
        return count($this->toArray());
    }

    /**
     * @return string
     */
    public function toSql()
    {
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class));
        $this->query->prepare();
        return $this->query->toSQL();
    }
    /**
     * Выполняет динамические методы.
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->query = call_user_func_array(array($this->query, $method), $parameters);
        return $this;
    }
}