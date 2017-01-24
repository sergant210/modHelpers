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
            $message = '<style>.modx-debug-block{ background-color:#002357;color:#fcffc4;margin:0;padding:5px} .modx-debug-block h5,.modx-debug-block pre { margin:0}</style><div>' . $message . '</div>';
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
    protected $rows = 0;
    protected $isFaked = false;

    protected $where = array();
    protected $tvSelects = array();
    protected $tvJoins = array();
    protected $unions = array();

    public function __construct(&$modx, $class='')
    {
        /** @var modX $modx */
        $this->modx =& $modx;
        if (empty($class) || is_numeric($class)) {
            $this->class = 'modResource';
            $this->isFaked = true;
            if (is_numeric($class)) $this->rows = (int) abs($class);
        } else {
            $this->class = $class;
        }
        $this->alias = $this->class;
        $this->query = $this->modx->newQuery($this->class);
        /*if ($class == 'modUser') {
            $this->alias = 'User';
        }*/
        $this->query->setClassAlias($this->alias);
        $this->query->limit(100);

        if (empty($class)) $this->query->query['from']['tables'] = array();
    }

    public function elements($number = 10)
    {
       if ($this->isFaked) {
           $this->rows = (int) abs($number);
       }
       return $this;
    }

    public function setClass($name = '')
    {
        if (!empty($name)) {
            $this->class = $name;
            $this->query = $this->modx->newQuery($name);
            $this->query->setClassAlias($this->alias);
            $this->query->limit(100);
            $this->isFaked = false;
        }
        return $this;
    }

    public function from($table, $alias = '')
    {
        if (preg_match('/^select/i', $table)) $table = '(' . $table . ')';
        $this->query->query['from']['tables'][] = array('table'=>$table, 'alias' => $alias);
//DEBUGGING
//log_error($this->query->query, 'HTML');
        return $this;
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
        $collection = $this->isFaked ? range(1, $this->rows) : $this->toArray();
        if (is_callable($callback)) {
            $output = '';
            $idx = 1;
            foreach ($collection as $item) {
                $_res = call_user_func($callback, $item, $idx, $this->modx);
                if ($_res === false) {
                    break;
                }
                $output .= $_res;
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
                $res = call_user_func_array($data, array(&$object, $this->modx));
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

    public function where($criteria, $conjunction = xPDOQuery::SQL_AND)
    {
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }

    public function orWhere($criteria)
    {
        $this->where[] = array('conjunction' => xPDOQuery::SQL_OR, 'where' => $criteria);
        return $this;
    }

    public function whereExists($table, $criteria, $conjunction = xPDOQuery::SQL_AND)
    {
        /*if ($conjunction != strtolower('or')) {
            $conjunction = '';
        } else {
            $conjunction = strtoupper($conjunction).':';
        }*/
        if (is_array($table)) {
            $tableName = key($table);
            $alias = current($table);
            $table = escape($tableName) . ' as ' . escape($alias);
        }
        $query = 'EXISTS (SELECT 1 FROM ' . $table . ' WHERE ' . $criteria . ')';
        $this->where[] = array('conjunction' => $conjunction, 'where' => $query);
        return $this;
    }

    public function whereNotExists($table, $criteria, $conjunction = xPDOQuery::SQL_AND)
    {
        if (is_array($table)) {
            $tableName = key($table);
            $alias = current($table);
            $table = escape($tableName) . ' as ' . escape($alias);
        }
        $query = 'NOT EXISTS (SELECT 1 FROM ' . $table . ' WHERE ' . $criteria . ')';
        $this->where[] = array('conjunction' => $conjunction, 'where' => $query);
        return $this;
    }
    public function whereLike($field, $value, $conjunction = xPDOQuery::SQL_AND)
    {
        $criteria = array($field.':LIKE' => $value);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }
    public function whereNotLike($field, $value, $conjunction = xPDOQuery::SQL_AND)
    {
        $criteria = array($field.':NOT LIKE' => $value);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }
    public function whereIn($field, $array, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!is_array($array)) $array = array($array);
        $criteria = array($field.':IN' => $array);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }
    public function whereNotIn($field, $array, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!is_array($array)) $array = array($array);
        $criteria = array($field.':NOT IN' => $array);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }
    public function whereIsNull($field, $conjunction = xPDOQuery::SQL_AND)
    {
        $criteria = array($field.':IS' => NULL);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }
    public function whereIsNotNull($field, $conjunction = xPDOQuery::SQL_AND)
    {
        $criteria = array($field.':IS NOT' => NULL);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
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
        if (empty($query)) $query = $this->query;
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
//log_error($this->query->query, 'HTML');
        if (empty($this->query->query['columns'])) $this->query->select($this->modx->getSelectColumns($this->class, $this->alias));
        $this->query->prepare();
        return $this->query->toSQL();
    }

    public function members($group)
    {
        if ($this->class == 'modUser') {
            $alias = !empty($this->alias) ? escape($this->alias) . '.' : '';
            switch (true) {
                case is_numeric($group):
                    $this->query->where($alias.'`id` IN (SELECT `member` FROM ' . table_name('modUserGroupMember') . " WHERE `user_group` = $group)");
                    break;
                case is_string($group):
                    $query = $alias.'`id` IN (SELECT `groupMember`.`member` FROM ' . table_name('modUserGroupMember') . ' as `groupMember`' .
                        ' JOIN ' . table_name('modUserGroup') . ' as `Groups` ON `Groups`.`id` = `groupMember`.`user_group`' .
                        " WHERE `Groups`.`name` LIKE '$group')";
                    $this->query->where($query);
                    break;
            }
        }

        return $this;
    }

    public function joinGroup($groupId, $roleId = null,$rank = null)
    {
        if ($this->class == 'modUser' || $this->class == 'modResource') {
            $this->addWhere();
            $collection = $this->modx->getIterator($this->class, $this->query);
            /** @var modUser|modResource $object */
            foreach ($collection as $object) {
                $object->joinGroup($groupId, $roleId, $rank);
            }
        }
        return $this;
    }

    public function leaveGroup($groupId)
    {
        if ($this->class == 'modUser' || $this->class == 'modResource') {
            $this->addWhere();
            $collection = $this->modx->getIterator($this->class, $this->query);
            /** @var modUser|modResource $object */
            foreach ($collection as $object) {
                $object->leaveGroup($groupId);
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

    /**
     * @param string $bindings
     * @param bool $toString
     * @return array|bool
     */
    public function execute($bindings = '', $toString = false)
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
            $result = $stmt->fetchAll();
            return $toString ? print_r($result,1) : $result;
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

class modHelperMailer
{
    protected $modx;
    /** @var modPHPMailer $mailer */
    public $mailer;


    public function __construct($modx)
    {
        /** @var  modX $modx */
        $this->modx = $modx;
        $this->mailer = $modx->getService('mail', 'mail.modPHPMailer');
        $this->mailer->set(modMail::MAIL_SENDER, $modx->getOption('emailsender'));
        $this->mailer->set(modMail::MAIL_FROM, $modx->getOption('emailsender'));
        $this->mailer->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));
        $this->mailer->setHTML(true);
    }

    public function to($email)
    {
        $this->mailer->address('to', $email);
        return $this;
    }

    public function toUser($user)
    {
        if (is_numeric($user)) {
            $user = $this->modx->getObject('modUser', array('id' => (int) $user));
        } elseif (is_string($user)) {
            $user = $this->modx->getObject('modUser', array('username' => $user));
        }
        if ($user instanceof modUser && $email = $user->Profile->get('email')) $this->mailer->address('to', $email);
        return $this;
    }

    public function cc($email)
    {
        $this->mailer->address('cc', $email);
        return $this;
    }

    public function bcc($email)
    {
        $this->mailer->address('bcc', $email);
        return $this;
    }

    public function replyTo($email)
    {
        $this->mailer->address('reply-to', $email);
        return $this;
    }

    public function subject($subject)
    {
        $this->mailer->set(modMail::MAIL_SUBJECT, $subject);
        return $this;
    }

    public function content($content)
    {
        $this->mailer->set(modMail::MAIL_BODY, $content);
        return $this;
    }

    public function sender($email)
    {
        $this->mailer->set(modMail::MAIL_SENDER, $email);
        return $this;
    }

    public function from($name)
    {
        $this->mailer->set(modMail::MAIL_FROM, $name);
        return $this;
    }

    public function fromName($name)
    {
        $this->mailer->set(modMail::MAIL_FROM_NAME, $name);
        return $this;
    }

    public function attach($file, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        $this->mailer->attach($file, $name, $encoding, $type);
        return $this;
    }

    public function setHTML($toggle)
    {
        $this->mailer->setHTML($toggle);
        return $this;
    }

    public function send()
    {
        if (empty($this->mailer->addresses['to'])) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'No addressed to send.');
            return false;
        }

        if (!$this->mailer->send()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . $this->mailer->mailer->ErrorInfo);
            $this->mailer->reset();
            return false;
        }
        $this->mailer->reset();
        return true;
    }
}


class modHelperModelBuilder
{
    protected $table;
    public $columns = array();
    protected $indexes = array();
    protected $aggregates = array();
    protected $composites = array();

    public function __construct($table)
    {
        $this->table = $table;
    }
    /**
     * Add a new char column to the model.
     *
     * @param  string  $name
     * @param  int  $precision
     * @return modHelpersModelColumn
     */
    public function char($name, $precision = 255)
    {
        $this->columns[] = $column = new modHelpersModelColumn('char', $name, compact('precision'));
        return $column;
    }

    /**
     * Add a new varchar column to the model.
     *
     * @param  string  $name
     * @param  int  $precision
     * @return modHelpersModelColumn
     */
    public function varchar($name, $precision = 255)
    {
        $this->columns[] = $column = new modHelpersModelColumn('varchar', $name, compact('precision'));
        return $column;
    }

    /**
     * Add a new text column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function text($name)
    {
        $this->columns[] = $column = new modHelpersModelColumn('text', $name);
        return $column;
    }

    /**
     * Add a new medium text column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function mediumText($name)
    {
        $this->columns[] = $column = new modHelpersModelColumn('mediumtext', $name);
        return $column;
    }

    /**
     * Add a new long text column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function longText($name)
    {
        $this->columns[] = $column = new modHelpersModelColumn('longtext', $name);
        return $column;
    }

    /**
     * Add an unsigned integer column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @return modHelpersModelColumn
     */
    public function id($name = 'id', $precision = 10)
    {
        return $this->int($name, $precision, true);
    }
    /**
     * Add a new integer column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return modHelpersModelColumn
     */
    public function int($name, $precision = 10, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 10;
        }
        $this->columns[] = $column = new modHelpersModelColumn('int', $name, compact('precision', 'unsigned'));
        return $column;
    }
    /**
     * Add a new tiny integer (1-byte) colum to the modeln.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return modHelpersModelColumn
     */
    public function tinyInt($name, $precision = 3, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 3;
        }
        $this->columns[] = $column = new modHelpersModelColumn('tinyint', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new small integer (2-byte) column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return modHelpersModelColumn
     */
    public function smallInt($name, $precision = 5, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 5;
        }
        $this->columns[] = $column = new modHelpersModelColumn('smallint', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new medium integer (3-byte) column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return modHelpersModelColumn
     */
    public function mediumInt($name, $precision = 8, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 8;
        }
        $this->columns[] = $column = new modHelpersModelColumn('mediumint', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new big integer (8-byte) column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return modHelpersModelColumn
     */
    public function bigInt($name, $precision = 20, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 20;
        }
        $this->columns[] = $column = new modHelpersModelColumn('bigint', $name, compact('precision', 'unsigned'));
        return $column;
    }
    /**
     * Add a new float column to the model.
     *
     * @param  string  $name
     * @param  string  $precision
     * @param  bool    $unsigned
     * @return modHelpersModelColumn
     */
    public function float($name, $precision = '12,2', $unsigned = false)
    {
        $this->columns[] = $column = new modHelpersModelColumn('float', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new double column to the model.
     *
     * @param  string   $name
     * @param  string   $precision
     * @param  bool     $unsigned
     * @return modHelpersModelColumn
     */
    public function double($name, $precision = '20,2', $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = '20,2';
        }
        $this->columns[] = $column = new modHelpersModelColumn('double', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new decimal column to the model.
     *
     * @param  string  $name
     * @param  string  $precision
     * @param  bool    $unsigned
     * @return modHelpersModelColumn
     */
    public function decimal($name, $precision = '12,2', $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = '12,2';
        }
        $this->columns[] = $column = new modHelpersModelColumn('decimal', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new boolean column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function boolean($name)
    {
        $this->columns[] = $column = new modHelpersModelColumn('boolean', $name, array('precision' => 1, 'unsigned' => true));
        return $column;
    }
    /**
     * Add a new array column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function asArray($name)
    {
        $this->columns[] = $column = new modHelpersModelColumn('array', $name);
        return $column;
    }

    public function arr($name)
    {
        return $this->asArray($name);
    }
    /**
     * Add a new json column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function json($name)
    {
        $this->columns[] = $column = new modHelpersModelColumn('json', $name);
        return $column;
    }
    /**
     * Add a new date column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function date($name)
    {
        $this->columns[] = $column = new modHelpersModelColumn('date', $name);
        return $column;
    }

    /**
     * Add a new date-time column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function dateTime($name)
    {
        $this->columns[] = $column = new modHelpersModelColumn('timestamp', $name);
        return $column;
    }
    /**
     * Add a new time column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function time($name)
    {
        return $this->dateTime($name);
    }
    /**
     * Add a new timestamp column to the model.
     *
     * @param  string  $name
     * @return modHelpersModelColumn
     */
    public function timestamp($name)
    {
        return $this->dateTime($name);
    }

    public function aggregate($alias, $attributes)
    {
        $this->aggregates[$alias] = array(
            'class' => $attributes['class'],
            'local' => $attributes['local'],
            'foreign' => $attributes['foreign'],
            'cardinality' => $attributes['cardinality'],
            'owner' => $attributes['owner'],
        );
        return $this;
    }

    public function composite($alias, $attributes)
    {
        $this->composites[$alias] = array(
            'class' => $attributes['class'],
            'local' => $attributes['local'],
            'foreign' => $attributes['foreign'],
            'cardinality' => $attributes['cardinality'],
            'owner' => $attributes['owner'],
        );
        return $this;
    }

    public function output()
    {
        $output = $fields = $meta = $indexes = $indAliases = $aggregates = $composites = $aliases = $rules = array();
        /** @var modHelpersModelColumn $column */
        foreach ($this->columns as $column) {
            $fields[$column->name] = $column->getDefault();
            $meta[$column->name] = $column->attributes;
            // Indexes
            $iName = key($column->index);
            $iAlias = $column->index[$iName]['alias'];
            if (isset($indAliases[$iAlias])) {
                $indexes[$indAliases[$iAlias]]['columns'] = array_merge($indexes[$indAliases[$iAlias]]['columns'],$column->index[$iName]['columns']);
            } elseif (!empty($column->index)) {
                $indAliases[$iAlias] = $iName;
                $indexes[$iName] = $column->index[$iName];
            }
            // Aggregates and composites
            if (!empty($column->aggregate)) $aggregates = array_merge($aggregates, $column->aggregate);
            if (!empty($column->composite)) $composites = array_merge($composites, $column->composite);
            // Aliases
            if (!empty($column->alias)) $aliases = array_merge($aliases, $column->alias);
            // Validations
            if (!empty($column->rules)) $rules = array_merge($rules, $column->rules);
        }
        if (!empty($this->table)) $output['table'] = $this->table;
        $output['fields'] = $fields;
        $output['fieldMeta'] = $meta;
        if (!empty($aliases)) $output['fieldAliases'] = $aliases;

        if (!empty($indexes)) $output['indexes'] = $indexes;
        if (!empty($aggregates)) $output['aggregates'] = $aggregates;
        if (!empty($this->aggregates)) {
            if (is_null($output['aggregates'])) $output['aggregates'] = array();
            $output['aggregates'] = array_merge($output['aggregates'], $this->aggregates);
        }
        if (!empty($composites)) $output['composites'] = $composites;
        if (!empty($this->composites)) {
            if (is_null($output['composites'])) $output['composites'] = array();
            $output['composites'] = array_merge($output['composites'], $this->composites);
        }
        if (!empty($rules)) $output['validation']['rules'] = $rules;

        return $output;
    }
}

class modHelpersModelColumn
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
        }
        if (!isset($cardinality)) return $this;
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