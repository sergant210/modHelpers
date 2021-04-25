<?php
namespace modHelpers;

use modCacheManager;
use modX;
use xPDO;
use Closure;

class CacheManager
{
    protected static $instance;
    /** @var modX $modx */
    protected $modx;
    /** @var modCacheManager $cacheManager */
    protected $cacheManager;

    private function __construct(modX $modx)
    {
        $this->modx = $modx;
        $this->cacheManager = $modx->getCacheManager();
    }

    private function __clone() {}

    /**
     * Set the globally available instance of the cache manager.
     *
     * @param modX $modx
     * @return static
     */
    public static function getInstance(modX $modx)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($modx);
        }

        return static::$instance;
    }

    /**
     * Get an item from the cache.
     * @param string $key
     * @param string|array $options Cache options. Optional.
     * @return mixed
     */
    public function get($key, $options = array())
    {
        if (is_string($options)) {
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        }
        return $this->cacheManager->get($key, $options);
    }

    /**
     * Store an item in the cache.
     * @param string $key
     * @param mixed $value
     * @param int|string|array $lifetime Magic. Can be a number of seconds, partition name or cache options.
     * @param array $options Cache options.
     * @return bool
     */
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
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        }
        return $this->cacheManager->set($key, $value, $lifetime, $options);
    }

    /**
     * Get an item from the cache or store the specified value.
     * @param string $key
     * @param string|integer|array $options Magic. Can be a number of seconds, partition name or cache options.
     * @param Closure $callback
     * @return mixed
     */
    public function remember($key, $options, Closure $callback)
    {
        if (is_callable($options)) {
            $callback = $options;
            $options = array();
        } elseif (!is_string($options) && !is_array($options)) {
            $options = array(
                xPDO::OPT_CACHE_EXPIRES => (int) $options
            );
        }
        $value = $this->get($key, $options);
        if (is_null($value)) {
            $this->set($key, $value = $callback(), $options);
        }

        return $value;
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param  string  $key
     * @param  string|array $options Cache options. Optional.
     * @return mixed
     */
    public function pull($key, $options = array())
    {
        $value = $this->get($key, $options);
        $this->delete($key, $options);
        return $value;
    }

    /**
     * Remove an item from the cache.
     * @param string $key
     * @param string|array $options Magic. Can be a partition name or cache options. Optional.
     * @return bool
     */
    public function delete($key, $options = array())
    {
        if (is_string($options)) {
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        }
        return $this->cacheManager->delete($key, $options);
    }

    /**
     * Handle dynamic calls.
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return method_exists($this->cacheManager, $method) ? call_user_func_array(array($this->cacheManager, $method), $parameters) : null;
    }
}