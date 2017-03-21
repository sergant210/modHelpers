<?php

class modHelpersLogger
{
    /** @var  modX $modx */
    protected static $modx;

    public static function setModx(modX $modx)
    {
        if (!isset(self::$modx) || !(self::$modx instanceof modX)) self::$modx = $modx;
    }

    public static function error($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        self::process(modX::LOG_LEVEL_ERROR, $message, $changeLevel, $target, $def, $file, $line);
    }

    public static function warn($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        self::process(modX::LOG_LEVEL_WARN, $message, $changeLevel, $target, $def, $file, $line);
    }

    public static function info($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        self::process(modX::LOG_LEVEL_INFO, $message, $changeLevel, $target, $def, $file, $line);
    }

    public static function debug($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        self::process(modX::LOG_LEVEL_DEBUG, $message, $changeLevel, $target, $def, $file, $line);
    }

    protected static function process($level, $message, $changeLevel, $target, $def = '', $file = '', $line = '')
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
        if (!isset(self::$modx)) {
            self::setModx(new modX());
        }
        if (is_string($changeLevel)) {
            $target = $changeLevel;
            $changeLevel = false;
        }
        if (is_object($message) && method_exists($message, 'toArray')) $message = $message->toArray();
        if (is_array($message)) $message = print_r($message, 1);
        if (self::$modx->getLogTarget() == 'HTML' || $target == 'HTML') {
            $message = '<style>.modx-debug-block{ background-color:#002357;color:#fcffc4;margin:0;padding:5px } .modx-debug-block h5,.modx-debug-block pre { margin:0 }</style><div>' . $message . '</div>';
        }
        if ($changeLevel) {
            $oldLevel = self::$modx->setLogLevel($level);
            self::$modx->log($level, $message, $target, $def, $file, $line);
            self::$modx->setLogLevel($oldLevel);
        } else {
            self::$modx->log($level, $message, $target, $def, $file, $line);
        }
    }
}