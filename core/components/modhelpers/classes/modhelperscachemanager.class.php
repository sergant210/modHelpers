<?php

class modHelpersCacheManager
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