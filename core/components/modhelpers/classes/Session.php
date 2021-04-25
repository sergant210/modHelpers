<?php

namespace modHelpers;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class Session extends SymfonySession
{
    /**
     * The session ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The session name.
     *
     * @var string
     */
    protected $name;
    /**
     * The session data.
     *
     * @var array
     */
    protected $attributes = [];

    public function __construct()
    {
    	$this->name = session_name();
    	$this->id = session_id();
    	$this->initialize();
    }

    /**
     * Load the session data.
     *
     * @return void
     */
    public function initialize()
    {
        $this->attributes = &$_SESSION;
        $this->remove($this->get('flash'));
        $this->attributes['flash'] = array();
    }
    /**
     * Get all of the session data.
     *
     * @return array
     */
    public function all()
    {
        return $this->attributes;
    }

    /**
     * Checks if a key exists.
     *
     * @param  string $key
     * @param bool $flat
     * @return bool
     */
    public function exists($key, $flat = false)
    {
        return !is_null($this->get($key, null, $flat));
    }

    /**
     * Checks if a key is present and not null.
     *
     * @param  string $key
     * @param bool $flat
     * @return bool
     */
    public function has($key, $flat = false)
    {
        return !empty($this->get($key, null, $flat));
    }
    /**
     * Get an item from the session.
     *
     * @param  string $key
     * @param  mixed  $default
     * @param  bool   $flat Don't use the dot notation
     * @return mixed
     */
    public function get($key, $default = null,  $flat = false)
    {
        if ($flat || isset($this->attributes[$key])) {
            return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
        }
        $array = $this->attributes;
        foreach (explode('.', $key) as $segment) {
            if (isset($array[$segment])) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        return $array;
    }
    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array $key
     * @param  mixed        $value
     * @param  bool         $flat Don't use the dot notation
     * @return void
     */
    public function set($key, $value = null, $flat = false)
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        } else {
            $flat = $value;
        }
        foreach ($key as $arrayKey => $arrayValue) {
            // Flat mode
            if ($flat) {
                $this->attributes[$arrayKey] = $arrayValue;
                continue;
            }
            $keys = explode('.', $arrayKey);
            if (count($keys) === 1) {
                $this->attributes[array_shift($keys)] = $arrayValue;
            } else {
                $_key = array_shift($keys);
                if (!isset($this->attributes[$_key]) || !is_array($this->attributes[$_key])) {
                    $this->attributes[$_key] = array();
                }
                $array =& $this->attributes[$_key];
                while (count($keys) > 1) {
                    $_key = array_shift($keys);
                    $array[$_key] = array();
                    $array = &$array[$_key];
                }
                $array[array_shift($keys)] = $arrayValue;
            }
        }
    }
    /**
     * Get the value of a given key and then remove it.
     *
     * @param  string  $key
     * @param  string  $default
     * @param  bool    $flat Don't use the dot notation
     * @return mixed
     */
    public function pull($key, $default = null, $flat = false)
    {
        $value = $this->get($key, $default, $flat);
        $this->remove($key, $flat);

        return $value;
    }

    /**
     * Remove one or many items from the session.
     *
     * @param  string|array $keys
     * @param bool $flat
     * @return void
     */
    public function remove($keys, $flat = false)
    {
        $original = &$this->attributes;
        if (!is_array($keys)) {
            $keys = array($keys);
        }

        foreach ($keys as $key) {
            if ($flat) {
                if ($this->exists($key)) {
                    unset($this->attributes[$key]);
                }
                continue;
            }
            $array = &$original;
            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            unset($array[array_shift($parts)]);
        }
    }
    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function clear()
    {
        $this->attributes = [];
    }
    /**
     * Returns the number of attributes.
     *
     * @return int The number of attributes
     */
    public function count()
    {
        return count($this->attributes);
    }
    /**
     * Flash a key / value pair to the session.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function flash($key, $value)
    {
        $this->set($key, $value);
        $this->push('flash', $key);
    }
    /**
     * Push a value onto a session array.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);
        $array[] = $value;
        $this->set($key, $array);
    }
}