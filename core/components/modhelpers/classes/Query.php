<?php
namespace modHelpers;

use modX;
use PDO;
use xPDO;

class Query
{
    /** @var  xPDO $modx */
    protected $modx;
    /** @var  string */
    protected $query;
    /** @var  array */
    protected $bindings;

    public function __construct(xPDO $modx, $query)
    {
        $this->modx = $modx;
        $this->query = $query;
    }

    /**
     * @param array|mixed $bindings
     * @return Query
     */
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
     * Returns a string representation of the query data.
     * @param null $bindings
     * @return array|bool|mixed
     */
    public function toString($bindings = NULL)
    {
        $result = $this->execute(is_array($bindings) ? $bindings : func_get_args());
        return is_array($result) ? print_r($result,1) : $result;
    }

    /**
     * Executes this query.
     * @param array|mixed $bindings
     * @return array|null
     */
    public function execute($bindings = NULL)
    {
        $tstart = microtime(true);
        if (($stmt = $this->prepare(is_array($bindings) ? $bindings : func_get_args())) && $stmt->execute($this->bindings)) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * Retrieves a number of xPDOObjects by the specified xPDOCriteria.
     * @param array|mixed $bindings
     * @return bool|int
     */
    public function count($bindings = NULL)
    {
        $tstart = microtime(true);
        if (($stmt = $this->prepare(is_array($bindings) ? $bindings : func_get_args())) && $stmt->execute($this->bindings)) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            return $stmt->rowCount();
        }
        return false;
    }

    /**
     * Executes the query and retrieve the first row.
     * @param array|mixed $bindings
     * @return array|null
     */
    public function first($bindings = NULL)
    {
        $tstart = microtime(true);
        if (($stmt = $this->prepare(is_array($bindings) ? $bindings : func_get_args())) && $stmt->execute($this->bindings)) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * @param array $bindings
     * @return bool|\PDOStatement
     */
    protected function prepare($bindings = null)
    {
        $this->bind($bindings);
        return $this->modx->prepare($this->query);
    }
}