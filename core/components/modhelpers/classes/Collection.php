<?php
namespace modHelpers;

use modX;
use xPDOQuery;
use PDO;
use modUser;
use modResource;

class Collection
{
    /** @var  modX $modx */
    protected $modx;
    /** @var  xPDOQuery $query */
    protected $query;
    /** @var string $class Class name */
    protected $class;
    /** @var string $alias Class alias*/
    protected $alias;
    /** @var int $rows */
    protected $rows = 0;
    /** @var bool $arrayCollection Flag - array collection or class collection. */
    protected $arrayCollection = false;
    /** @var array $where */
    protected $where = array();
    /** @var array $tvSelects */
    protected $tvSelects = array();
    /** @var array $tvJoins */
    protected $tvJoins = array();
    /** @var array $unions */
    protected $unions = array();

    /**
     * Collection constructor.
     * @param modX $modx
     * @param string $class
     */
    public function __construct(modX $modx, $class = '')
    {
        $this->modx = $modx;
        if (empty($class) || is_numeric($class)) {
            //$this->class = 'modResource';
            $this->arrayCollection = true;
            if (is_numeric($class)) {
                $this->rows = abs($class);
            }
        } else {
            $this->class = $class;

            $this->alias = $this->class;
            $this->query = $this->modx->newQuery($this->class);
            $this->query->setClassAlias($this->alias);
        }
    }

    public function elements($number = 10)
    {
        if ($this->arrayCollection) {
            $this->rows = abs($number);
        }
        return $this;
    }

    public function setClass($name = '', $alias = '')
    {
        if (!empty($name)) {
            $this->class = $name;
            $this->alias = $alias ?: $name;
            $this->query = $this->modx->newQuery($name);
            $this->query->setClassAlias($this->alias);
            //$this->query->limit(100);
            $this->arrayCollection = false;
        }
        return $this;
    }

    public function select($columns= '*') {
        $this->query->query['columns'] = array();
        $this->query->select($columns);

        return $this;
    }

    public function from($table, $alias = '')
    {
        if (0 === stripos($table, "select")) {
            $table = '(' . $table . ')';
        }
        $this->query->query['from']['tables'][] = array('table'=>$table, 'alias' => $alias);
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
        if ($this->arrayCollection) {
            $data = $this->rows ? range(1, $this->rows) : array();
        } elseif (!$this->class) {
            $data = array();
        } else {
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
        }
        return $toString ? print_str($data, 1) : $data;
    }

    public function all($name = null)
    {
        if (!$this->class) return '';
        $this->query->limit(0);
        return $this->get($name);
    }

    public function each($callback)
    {
        $collection = $this->arrayCollection ? range(1, $this->rows) : $this->toArray();
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
        if (!$this->class) {
            return '';
        }
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
        if (!$this->class) {
            return false;
        }
        if (!$this->modx->hasPermission('save')) {
            return 0;
        }
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
        if (!$this->class) {
            return false;
        }
        if (!$this->modx->hasPermission('remove')) {
            return 0;
        }
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
        if (!$this->class) {
            return $this;
        }
        $this->query->sortby('id', 'ASC');
        $this->query->limit($num);
        return $this->process();
    }

    public function last($num = 0)
    {
        if (!$this->class) {
            return $this;
        }
        $this->query->sortby('id', 'DESC');
        $this->query->limit($num);

        return $this->process();
    }

    public function union($query)
    {
        if (!$this->class) {
            return $this;
        }
        $this->unions[] = $query;
        return $this;
    }

    public function where($criteria, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!$this->class) {
            return $this;
        }
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }

    public function orWhere($criteria)
    {
        if (!$this->class) {
            return $this;
        }
        $this->where[] = array('conjunction' => xPDOQuery::SQL_OR, 'where' => $criteria);
        return $this;
    }

    public function whereExists($table, $criteria, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!$this->class) {
            return $this;
        }
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
        if (!$this->class) {
            return $this;
        }
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
        if (!$this->class) {
            return $this;
        }
        $criteria = array($field.':LIKE' => $value);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }

    public function whereNotLike($field, $value, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!$this->class) {
            return $this;
        }
        $criteria = array($field.':NOT LIKE' => $value);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }

    public function whereIn($field, $array, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!$this->class) {
            return $this;
        }
        if (!is_array($array)) {
            $array = array($array);
        }
        $criteria = array($field.':IN' => $array);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }

    public function whereNotIn($field, $array, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!is_array($array)) {
            $array = array($array);
        }
        $criteria = array($field.':NOT IN' => $array);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }

    public function whereIsNull($field, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!$this->class) {
            return $this;
        }
        $criteria = array($field.':IS' => NULL);
        $this->where[] = array('conjunction' => $conjunction, 'where' => $criteria);
        return $this;
    }

    public function whereIsNotNull($field, $conjunction = xPDOQuery::SQL_AND)
    {
        if (!$this->class) {
            return $this;
        }
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
            $this->tvSelects = array();
        }
        if (empty($this->query->query['columns'])) {
            $this->query->select($this->modx->getSelectColumns($this->class, $this->alias));
        }
    }

    protected function addWhere($query = '')
    {
        if (empty($query)) {
            $query = $this->query;
        }
        if (!empty($this->where)) {
            foreach ($this->where as $where) {
                $query->where($where['where'], $where['conjunction']);
            }
            $this->where = array();
        }
    }

    protected function addJoins()
    {
        if (!empty($this->tvJoins)) {
            foreach ($this->tvJoins as $k => $v) {
                $class = !empty($v['class']) ? $v['class'] : $k;
                $alias = !empty($v['alias']) ? $v['alias'] : $k;
                if (!is_numeric($alias) && !is_numeric($class)) {
                    $this->query->leftJoin($class, $alias, $v['on']);
                }
            }
            $this->tvJoins = array();
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
            $this->unions = array();
        }
        return $unions;
    }

    public function withTV($TV, $prefix = 'TV.')
    {
        if (!$this->class || $this->class !== 'modResource') {
            return $this;
        }
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
        if (empty($this->query->query['columns'])) {
            $this->query->select($this->modx->getSelectColumns($this->class, $this->alias));
        }
        if (!$collection = $this->modx->getCollection($this->class, $this->query)) {
            return false;
        }
        return $collection;
    }

    public function profile($alias = 'Profile')
    {
        if ($this->class === 'modUser') {
            $this->query->innerJoin('modUserProfile', $alias);
            if (empty($this->query->query['columns'])) {
                $this->query->select($this->modx->getSelectColumns('modUser', $this->alias) . ',' . $this->modx->getSelectColumns('modUserProfile', $alias, '', array('id'), true));
            }
        }
        return $this;
    }

    public function count()
    {
        $array = $this->toArray();
        return is_array($array) ? count($array) : 0;
    }

    public function max($name)
    {
        if (!$this->class) {
            return $this;
        }
        $this->query->query['columns'] = array("max($name) as max");
        $value = $this->toArray();
        return $value[0]['max'];
    }

    public function min($name)
    {
        if (!$this->class) {
            return $this;
        }
        $this->query->query['columns'] = array("min($name) as min");
        $value = $this->toArray();
        return $value[0]['min'];
    }

    public function avg($name)
    {
        if (!$this->class) {
            return $this;
        }
        $this->query->query['columns'] = array("avg($name) as avg");
        $value = $this->toArray();
        return $value[0]['avg'];
    }

    public function sum($name)
    {
        if (!$this->class) {
            return $this;
        }
        $this->query->query['columns'] = array("sum($name) as sum");
        $value = $this->toArray();
        return $value[0]['sum'];
    }

    /**
     * Retrieve the parsed SQL.
     * @return string
     */
    public function toSql()
    {
        if (!$this->class) {
            return '';
        }
        $this->addSelect();
        $this->addJoins();
        $this->addWhere($this->query);
        if (empty($this->query->query['columns'])) {
            $this->query->select($this->modx->getSelectColumns($this->class, $this->alias));
        }
        $this->query->prepare();
        return $this->query->toSQL();
    }

    /**
     * Prepare the query for users which belong to the specified group.
     * @param integer|string $group
     * @return Collection
     */
    public function members($group)
    {
        if ($this->class === 'modUser') {
            $alias = !empty($this->alias) ? escape($this->alias) . '.' : '';
            switch (true) {
                case is_numeric($group):
                    $this->query->where($alias.'`id` IN (SELECT `member` FROM ' . table_name('modUserGroupMember') . " WHERE `user_group` = $group)");
                    break;
                case is_string($group):
                    $query = $alias.'`id` IN (SELECT `groupMember`.`member` FROM ' . table_name('modUserGroupMember') . ' as `groupMember`' .
                        ' JOIN ' . table_name('modUserGroup') . ' as `Groups` ON `Groups`.`id` = `groupMember`.`user_group`' .
                        " WHERE `Groups`.`name` LIKE " . $this->modx->quote($group) . ")";
                    // TODO-sergant: Add to the where array instead of the query instance
                    $this->query->where($query);
                    break;
            }
        }

        return $this;
    }

    /**
     * Joins a resource or user to the appropriate group.
     * @param string|integer $groupId Id or name of the group.
     * @param mixed $roleId
     * @param integer $rank Rank for users.
     * @return Collection
     */
    public function joinGroup($groupId, $roleId = null,$rank = null)
    {
        if ($this->class === 'modUser' || $this->class === 'modResource') {
            $this->addWhere();
            $collection = $this->modx->getIterator($this->class, $this->query);
            /** @var modUser|modResource $object */
            foreach ($collection as $object) {
                $object->joinGroup($groupId, $roleId, $rank);
            }
        }
        return $this;
    }

    /**
     * Removes a resource or user from the specified group.
     * @param mixed $groupId
     * @return Collection
     */
    public function leaveGroup($groupId)
    {
        if ($this->class === 'modUser' || $this->class === 'modResource') {
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
     * Dump the collection and end the script.
     *
     * @return void
     */
    public function dd()
    {
        http_response_code(500);

        $this->dump();

        die(1);
    }

    /**
     * Dump the collection.
     *
     * @return $this
     */
    public function dump()
    {
        $collection = clone $this;
        $collection->modx = null;

        return $this;
    }

    function __clone()
    {
        $this->query = clone $this->query;
    }
    /**
     * Call xPDOQuery methods.
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ( !$this->arrayCollection ) {
            $this->query = call_user_func_array(array($this->query, $method), $parameters);
        }
        return $this;
    }
}