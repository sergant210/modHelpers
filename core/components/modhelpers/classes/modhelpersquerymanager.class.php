<?php

class modHelpersQueryManager
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

    /**
     * @param array|mixed $bindings
     * @return modHelpersQueryManager $this
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

    public function toString($bindings = NULL)
    {
        if (func_num_args()) {
            if (!is_array($bindings)) {
                $bindings = func_get_args();
            }
        }
        $result = $this->execute($bindings);
        return is_array($result) ? print_r($result,1) : $result;
    }
    /**
     * @param array|mixed $bindings
     * @return array|bool
     */
    public function execute($bindings = NULL)
    {
        if (!empty($bindings)) {
            if (!is_array($bindings)) {
                $this->bindings = func_get_args();
            } else {
                $this->bindings = $bindings;
            }
        }
        $tstart = microtime(true);
        $stmt = $this->modx->prepare($this->query);
        if ($stmt->execute($this->bindings)) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
        return false;
    }

    /**
     * @param array|mixed $bindings
     * @return bool|int
     */
    public function count($bindings = NULL)
    {
        if (!empty($bindings)) {
            if (!is_array($bindings)) {
                $this->bindings = func_get_args();
            } else {
                $this->bindings = $bindings;
            }
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