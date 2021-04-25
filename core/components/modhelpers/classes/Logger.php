<?php
namespace modHelpers;

use modX;

class Logger
{
    protected static $instance;
    /** @var  modX $modx */
    protected $modx;

    private function __construct($modx)
    {
    	$this->modx = $modx;
    }

    private function __clone() {}

    /**
     * Set the globally available instance of the logger.
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
     * Log an error with details about where and when an event occurs.
     */
    public function error($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        $this->process(modX::LOG_LEVEL_ERROR, $message, $changeLevel, $target, $def, $file, $line);
    }

    /**
     * Log a warning with details about where and when an event occurs.
     */
    public function warn($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        $this->process(modX::LOG_LEVEL_WARN, $message, $changeLevel, $target, $def, $file, $line);
    }

    /**
     * Log a message with details about where and when an event occurs.
     */
    public function info($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        $this->process(modX::LOG_LEVEL_INFO, $message, $changeLevel, $target, $def, $file, $line);
    }

    /**
     * Log an debug info with details about where and when an event occurs.
     */
    public function debug($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        $this->process(modX::LOG_LEVEL_DEBUG, $message, $changeLevel, $target, $def, $file, $line);
    }

    protected function process($level, $message, $changeLevel, $target, $def = '', $file = '', $line = '')
    {
        if (empty($file)) {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            } elseif (version_compare(phpversion(), '5.3.6', '>=')) {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            } else {
                $backtrace = debug_backtrace();
            }
            if ($backtrace && isset($backtrace[2])) {
                $file = $backtrace[2]['file'];
                $line = $backtrace[2]['line'];
            }
        }
        if (is_string($changeLevel)) {
            $target = $changeLevel;
            $changeLevel = false;
        }
        if (is_object($message) && method_exists($message, 'toArray')) {
            $message = $message->toArray();
        }
        if (is_array($message)) {
            $message = print_r($message, 1);
        }
        if ($this->modx->getLogTarget() === 'HTML' || $target === 'HTML') {
            $message = '<style>.modx-debug-block{ background-color:#002357;color:#fcffc4;margin:0;padding:5px } .modx-debug-block h5,.modx-debug-block pre { margin:0 }</style><div>' . $message . '</div>';
        }
        if ($changeLevel) {
            $oldLevel = $this->modx->setLogLevel($level);
            $this->modx->log($level, $message, $target, $def, $file, $line);
            $this->modx->setLogLevel($oldLevel);
        } else {
            $this->modx->log($level, $message, $target, $def, $file, $line);
        }
    }
}