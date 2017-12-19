<?php
/**
 * Functions for MODX Revolution.
 * @package modHelpers
 * @version 3.2.0-beta
 */

if (!function_exists('url')) {
    /**
     * Формирует Url
     * @param int $id Page id
     * @param string $context Context
     * @param array $arg Ling arguments
     * @param mixed $scheme Scheme
     * <pre>
     *      -1 : (default value) URL is relative to site_url
     *       0 : see http
     *       1 : see https
     *    full : URL is absolute, prepended with site_url from config
     *     abs : URL is absolute, prepended with base_url from config
     *    http : URL is absolute, forced to http scheme
     *   https : URL is absolute, forced to https scheme
     * </pre>
     * @param array $options Option
     * @return string
     */
    function url($id, $context = '', $arg = array(), $scheme = -1, array $options = array())
    {
        global $modx;
        return $modx->makeUrl($id, $context, $arg, $scheme, $options);
    }
}
if (!function_exists('redirect')) {
    /**
     * Redirect to the specified url or page
     * @param string|int $url Url or page id
     * @param array|string|bool $options Options
     */
    function redirect($url, $options = false)
    {
        global $modx;
        if (is_numeric($url)) {
            if (is_string($options)) {
                $ctx = $options;
            } else {
                $ctx = $options['context'] ?: '';
            }
            $url = url($url, $ctx);
        }
        if (!empty($url)) $modx->sendRedirect($url, $options);
    }
}
if (!function_exists('forward')) {
    /**
     * Forwards the request to another resource without changing the URL.
     *
     * @param integer $id The resource identifier.
     * @param string $options An array of options for the process.
     */
    function forward($id, $options = null)
    {
        global $modx;

        if (!empty($id)) $modx->sendForward($id, $options);
    }
}
if (!function_exists('abort')) {
    /**
     * Send to the error page or to the unauthorized page
     * @param array|int $options Options or response code - 401,403,404
     */
    function abort($options = null)
    {
        global $modx;
        if (!is_array($options) && is_numeric($options)) {
            $error_type = (int) $options;
        } elseif (is_array($options) && isset($options['error_type'])) {
            $error_type = $options['error_type'];
        } else {
            $error_type = 404;
        }
        switch ($error_type) {
            case 401:
            case 403:
                $modx->sendUnauthorizedPage($options);
                break;
            case 404:
            default:
                $modx->sendErrorPage($options);
        }
    }
}
if (!function_exists('config')) {
    /**
     * Gets and sets the system settings
     * @param string|array $key
     * @param null|mixed $default
     * @param bool $skipEmpty
     * @return array|null|string
     */
    function config($key = '', $default = '', $skipEmpty = false)
    {
        global $modx;
        if (!empty($key)) {
            if (is_array($key)) {
                //if (!can('settings')) return false;
                foreach ($key as $itemKey => $itemValue) {
                    if (!empty($itemKey)) $modx->config[$itemKey] = $itemValue;
                }
                return true;
            }
            return $skipEmpty
                            ? (!empty($modx->config[$key]) ? $modx->config[$key] : $default)
                            : (isset($modx->config[$key]) ? $modx->config[$key] : $default);

        } else {
            return $modx->config;
        }
    }
}
if (!function_exists('session')) {
    /**
     * Manages the session.
     * @param string $key Use the dot notation.
     * @param string|bool $default In getting mode - default value. If setting - don't use the dot notation.
     * @param bool $flat Don't use the dot notation
     * @return mixed|null
     */
    function session($key = null, $default = null, $flat = false)
    {
        if (PHP_SESSION_ACTIVE !== session_status()) {
            return null;
        }
        if (is_null($key)) {
            return $_SESSION;
        }
        // Set the value
        if (is_array($key)) {
            foreach ($key as $arrKey => $arrValue) {
                // Flat mode
                if ($default) {
                    $_SESSION[$arrKey] = $arrValue;
                    continue;
                }
                $keys = explode('.', $arrKey);
                if (count($keys) == 1) {
                    $_SESSION[array_shift($keys)] = $arrValue;
                } else {
                    $_key = array_shift($keys);
                    if (!isset($_SESSION[$_key]) || !is_array($_SESSION[$_key])) {
                        $_SESSION[$_key] = array();
                    }
                    $array =& $_SESSION[$_key];
                    while (count($keys) > 1) {
                        $_key = array_shift($keys);
                        $array[$_key] = array();
                        $array = &$array[$_key];
                    }
                    $array[array_shift($keys)] = $arrValue;
                }
            }
//            return $_SESSION;
        } else {
            // Get the value
            if ($flat) {
                return $_SESSION[$key] ?: $default;
            }
            $array = $_SESSION;
            foreach (explode('.', $key) as $segment) {
                if (isset($array[$segment])) {
                    $array = $array[$segment];
                } else {
                    return $default;
                }
            }
            return $array;
        }
    }

}
if (!function_exists('session_pull')) {
    /**
     * Get the value of a given key and then unset it.
     * @param string $key Use the dot notation.
     * @param bool $flat Don't use the dot notation.
     * @return mixed
     */
    function session_pull($key, $flat = false)
    {
        if (empty($key)) {
            return $_SESSION ?: null;
        }
        if ($flat) {
            $output = $_SESSION[$key];
            unset($_SESSION[$key]);
        } else {
            $array =& $_SESSION;
            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array =& $array[$part];
                } else {
                    return null;
                }
            }
            $part = array_shift($parts);
            $output = $array[$part];
            unset($array[$part]);
        }
        return $output;
    }
}
if (!function_exists('cache')) {
    /**
     * Manages the cache
     * @see https://docs.modx.com/revolution/2.x/developing-in-modx/advanced-development/caching
     * @param string|array $key
     * @param null|int|string|array $options
     * @return mixed|modHelpers\CacheManager
     */
    function cache($key = '', $options = NULL)
    {
        global $modx;
        /** @var modHelpers\CacheManager $class */
        $class = config('modhelpers_cacheManagerClass', 'modHelpers\CacheManager', true);
        /** @var modHelpers\CacheManager $cacheManager */
        $cacheManager = $class::getInstance($modx);
        if (func_num_args() == 0) {
            return $cacheManager;
        }
        if (is_string($options)) {
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        } elseif (is_numeric($options)) {
            $options = array(xPDO::OPT_CACHE_EXPIRES => (int) $options);
        }
        if (is_array($key)) {
            foreach ($key as $itemKey => $itemValue) {
                $lifetime = isset($options[xPDO::OPT_CACHE_EXPIRES]) ? $options[xPDO::OPT_CACHE_EXPIRES] : 0;
                $cacheManager->set($itemKey, $itemValue, $lifetime, $options);
            }
//            return $cacheManager ?: null;
        } else {
            return $cacheManager->get($key, $options);
        }
    }
}
if (!function_exists('parents')) {
    /**
     * Gets all of the parent resource ids for a given resource.
     * @param int $id
     * @param int $height
     * @param array $options
     * @return array
     */
    function parents($id = null, $height = 10,array $options = array())
    {
        global $modx;
        return $modx->getParentIds($id, $height, $options);
    }
}
if (!function_exists('children')) {
    /**
     * Gets all of the child resource ids for a given resource.
     * @param int $id
     * @param int $depth
     * @param array $options
     * @return array
     */
    function children($id = null, $depth = 10,array $options = array())
    {
        global $modx;
        return $modx->getChildIds($id, $depth, $options);
    }
}

if (!function_exists('pls')) {
    /**
     * Gets/sets placeholders
     * @param string|array $key String to get a placeholder, array ('key'=>'value') - to set one/ones.
     * @param string $default Default value (if getting) or the options (if setting).
     * @return mixed
     */
    function pls($key = '', $default = '')
    {
        global $modx;

        if (empty($key)) {
            return $modx->placeholders;
        }
        if (is_array($key)) {
            $options = array(
                'prefix' => '',
                'separator' => '.',
                'restore' => false,
            );
            if (is_array($default)) {
                $options = array_merge($options, $default);
            }
            extract($options);
            /** @var string $prefix */
            /** @var string $separator */
            /** @var bool $restore */
            return $modx->toPlaceholders($key, $prefix, $separator, $restore);
        } else {
            return default_if(@$modx->placeholders[$key], $default);
        }
    }
}
if (!function_exists('pls_delete')) {
    /**
     * Removes the specified placeholders
     * @param string|array $keys Key/array of keys
     */
    function pls_delete($keys)
    {
        global $modx;
        if (is_array($keys)) {
            $modx->unsetPlaceholders($keys);
        } else {
            $modx->unsetPlaceholder($keys);
        }
    }
}
if (!function_exists('email')) {
    /**
     * Send Email.
     * @param string|array $email Email.
     * @param string|array $subject Subject or an array of options. Required option keys - subject, content. Optional - sender, from, fromName.
     * @param string $content
     * @return bool|modHelpers\Mailer
     */
    function email($email='', $subject='', $content = '')
    {
        global $modx;
        /** @var modHelpers\Mailer $class */
        $class = config('modhelpers_mailerClass', 'modHelpers\Mailer', true);
        /** @var modHelpers\Mailer $mailer */
        $mailer = $class::getInstance($modx); // new $class($modx);
        if (func_num_args() == 0) return $mailer;
        if (is_array($subject)) {
            $options = $subject;
        } else {
            $options = compact('subject','content');
        }
        if (empty($email)) return false;
        $mailer->to($email);
        foreach (array('sender','from','fromName','subject','content','cc','bcc','replyTo','tpl') as $prop) {
            if (!empty($options[$prop])) $mailer->$prop($options[$prop]);
        }
        if (!empty($options['attach'])) {
            if (is_array($options['attach'])) {
                foreach ($options['attach'] as $name => $file) {
                    if (!is_string($name)) $name = '';
                    $mailer->attach($file, $name);
                }
            } else {
                $mailer->attach($options['attach']);
            }
        }
        return $mailer->send();
    }
}
if (!function_exists('email_user')) {
    /**
     * Sends email to the specified user.
     * @param int|string|modUser $user User id or username or user object.
     * @param string|array $subject Magic. Subject or an array of options. Required option keys - subject, content. Optional - sender, from, fromName.
     * @param string $content
     * @return bool
     */
    function email_user($user, $subject, $content = '')
    {
        global $modx;
        if (!is_array($user)) $user = compact('user');
        $email = array();
        foreach ($user as $usr) {
            if (is_numeric($usr)) {
                $usr = $modx->getObject('modUser', array('id' => (int)$usr));
            } elseif (is_string($usr)) {
                $usr = $modx->getObject('modUser', array('username' => $usr));
            }
            if ($usr instanceof modUser && $eml = $usr->Profile->get('email')) $email[] = $eml;
        }
        return !empty($email) ? email($email, $subject, $content) : false;
    }
}

if (!function_exists('css')) {
    /**
     * Register CSS to be injected inside the HEAD tag of a resource.
     * @param string $src
     * @param string $media
     */
    function css($src, $media = null)
    {
        global $modx;
        $modx->regClientCSS($src, $media);
    }
}
if (!function_exists('script')) {
    /**
     * Register JavaScript.
     * @param string $src
     * @param bool|string $start Inject inside the HEAD tag of a resource.
     * @param bool|string $plaintext
     * @param string|null $attr async defer
     */
    function script($src, $start = false, $plaintext = false, $attr = null)
    {
        global $modx;

        switch (true) {
            case is_string($start):
                $attr = $start;
                $start = false;
                $plaintext = true;
                break;
            case is_string($plaintext):
                $attr = $plaintext;
                $plaintext = true;
                break;
            case is_string($attr):
                $plaintext = true;
                break;
        }
        if ($attr) $src = '<script '. $attr .' type="text/javascript" src="' . $src . '"></script>';

        if ($start) {
            $modx->regClientStartupScript($src, $plaintext);
        } else {
            $modx->regClientScript($src, $plaintext);
        }
    }
}
if (!function_exists('html')) {
    /**
     * Register HTML
     * @param string $src
     * @param bool $start Inject inside the HEAD tag of a resource.
     */
    function html($src, $start = false)
    {
        $plaintext = true;
        script($src, $start, $plaintext);
    }
}
if (!function_exists('lang')) {
    /**
     * Grabs a processed lexicon string.
     * @param $key
     * @param array $params
     * @param string $language
     * @return null|string
     */
    function lang($key, $params = array(), $language = '')
    {
        global $modx;
        return $modx->lexicon($key, $params, $language);
    }
}
if (!function_exists('chunk')) {
    /**
     * Process and return the output from a Chunk by name.
     * @param $chunkName
     * @param array $properties
     * @return string
     */
    function chunk($chunkName, array $properties= array ())
    {
        global $modx;
//        $output = '';
        //$store = isset($modx->getCacheManager()->store) ? $modx->getCacheManager()->store : array('modChunk'=>array());
        $chunkName = preg_replace('#(\.)+\/#', '/', $chunkName);
        if ($chunkName[0] == '/') {
            $chunkName = rtrim(config('modhelpers_chunks_path', MODX_CORE_PATH . 'elements/chunks'),'/') . $chunkName;
        }
        if (strpos($chunkName, '/') !== false && file_exists($chunkName)) {
            $content = @file_get_contents($chunkName);
            /** @var modChunk $chunk */
            $chunk = $modx->newObject('modChunk', array('name' => basename($chunkName)));
            $chunk->_cacheable = false;
            $chunk->_processed = false;
            $chunk->_content = '';
            $output = $chunk->process($properties, $content);
/*        } elseif ($pdo = pdotools()) {
            $output = $pdo->getChunk($chunkName, $properties);*/
        } else {
            $output = $modx->getChunk($chunkName, $properties);
        }
        return $output;
    }
}
if (!function_exists('snippet')) {
    /**
     * Run the specified MODX snippet or file snippet.
     * @param string $snippetName
     * @param array $scriptProperties
     * @param int|string|array $cacheOptions
     * @return string
     */
    function snippet($snippetName, array $scriptProperties = array (), $cacheOptions = array())
    {
        $result = cache($snippetName);
        if (isset($result)) {
            return $result;
        }
        global $modx;
        $snippetName = preg_replace('#(\.)+\/#', '/', $snippetName);
        if ($snippetName[0] == '/') {
            $snippetName = rtrim(config('modhelpers_snippets_path', MODX_CORE_PATH . '/elements/snippets'),'/') . $snippetName;
        }
        if (strpos($snippetName, '/') !== false && @file_exists($snippetName)) {
            ob_start();
            extract($scriptProperties, EXTR_SKIP);
            $result = include $snippetName;
            $result = ($result === null ? '' : $result);
            if (ob_get_length()) {
                $result = ob_get_contents() . $result;
            }
            ob_end_clean();
/*        } elseif ($pdo = pdotools()) {
            $result =  $pdo->runSnippet($snippetName, $scriptProperties);*/
        } else {
            $result = $modx->runSnippet($snippetName, $scriptProperties);
        }
        if (!empty($cacheOptions)) {
            cache(array($snippetName => $result), $cacheOptions);
        }
        return $result;
    }
}
if (!function_exists('processor')) {
    /**
     * Run the specified processor.
     * @param string $action
     * @param array $scriptProperties
     * @param array $options
     * @return mixed
     */
    function processor($action = '',$scriptProperties = array(),$options = array())
    {
        global $modx;
        return $modx->runProcessor($action, $scriptProperties, $options);
    }
}
if (!function_exists('object')) {
    /**
     * Get an object of the specified class.
     * @param string $class
     * @param integer|array|xPDOCriteria $criteria A valid xPDO criteria expression.
     * @return modHelpers\Object
     */
    function object($class, $criteria = null)
    {
        global $modx;
        $objectClass = config('modhelpers_objectClass', 'modHelpers\Object', true);
        /** @var modHelpers\Object $object */
        $object = new $objectClass($modx, $class);
        if (isset($criteria)) {
            if (is_scalar($criteria)) {
                $pk = $modx->getPK($class);
                $where = array($pk => $criteria);
            } else {
                $where = $criteria;
            }
            if (isset($where)) {
                $object->where($where);
            }
        }
        return $object;
    }
}
if (!function_exists('collection')) {
    /**
     * Get a collection of the specified class.
     * @param string $class
     * @param mixed $criteria A valid xPDO criteria expression.
     * @return modHelpers\Collection
     */
    function collection($class = '', $criteria = null)
    {
        global $modx;

        $collectionClass = config('modhelpers_collectionClass', 'modHelpers\Collection', true);
        /** @var modHelpers\Collection $collection */
        $collection = new $collectionClass($modx, $class);
        if (!empty($criteria)) {
            $collection->where($criteria);
        }
        return $collection;
    }
}
if (!function_exists('resource')) {
    /**
     * Get a resource object/array.
     * @param int|array $criteria Resource id or array with criteria.
     * @param bool $asObject True to return an object. Otherwise - an array.
     * @return array|modResource|bool|modHelpers\Object
     */
    function resource($criteria = null, $asObject = true)
    {
        /** @var modHelpers\Object $resourceManager */
        if (is_numeric($criteria)) {
            $criteria = array('id' => (int) $criteria);
        }
        $resourceManager = object('modResource', $criteria);
        if (!isset($criteria)) return $resourceManager;

        return $asObject ? $resourceManager->get() : $resourceManager->toArray();
    }
}
if (!function_exists('resources')) {
    /**
     * Get a collection of the resources.
     * @param array $criteria Criteria
     * @param bool $asObject True to return an array of the objects. Otherwise - an array of resources data arrays.
     * @return array|bool|modHelpers\Collection
     */
    function resources($criteria = null, $asObject = false)
    {
        global $modx;
        /** @var modHelpers\Collection $collection */
        $collection = collection('modResource');

        if (!isset($criteria)) {
            return $collection;
        }
        if (is_array($criteria)) {
            $select = isset($criteria['select']) ? $criteria['select'] : $modx->getSelectColumns('modResource');
            $collection->select($select);
            if (isset($criteria['sortby'])) {
                list($sortby, $sortdir) = explode(',', $criteria['sortby']);
                $sortdir = is_null($sortdir) ? 'ASC' : trim($sortdir);
                $collection->sortby($sortby, $sortdir);
            }
            if (isset($criteria['limit'])) {
                list($limit, $offset) = explode(',', $criteria['limit']);
                $offset = is_null($offset) ? 0 : $offset;
                $collection->limit($limit, $offset);
            }
            $complex = isset($criteria['select']) || isset($criteria['sortby']) || isset($criteria['limit']) || isset($criteria['where']);
            if ($complex) {
                $where = isset($criteria['where']) ? $criteria['where'] : false;
            } else {
                $where = $criteria;
            }
            if ($where) $collection->where($where);
        }
        if (!isset($criteria)) return $collection;

        return $asObject ? $collection->get() : $collection->toArray();
    }
}

if (!function_exists('user')) {
    /**
     * Get a user object or an array of user's data.
     * @param int|string|array $criteria User id, username or array.
     * @param bool $asObject True to return an object. Otherwise - an array.
     * @return array|modUser
     */
    function user($criteria = null, $asObject = true)
    {
        /** @var modHelpers\Object $userManager */
        if (is_numeric($criteria)) {
            $criteria = array('id' => (int) $criteria);
        } elseif (is_string($criteria)) {
            $criteria = array('username' => $criteria);
        }
        $userManager = object('modUser', $criteria)->withProfile();

        return (isset($criteria) && $asObject) ? $userManager->get() : $userManager->toArray();
    }
}
if (!function_exists('users')) {
    /**
     * Get a collection of user's objects or user's data.
     * @param array $criteria
     * @param bool $asObject True to return an array of the user objects. Otherwise - an array of users data arrays.
     * @return array|modHelpers\Collection
     */
    function users($criteria = null, $asObject = false)
    {
        global $modx;
        /** @var modHelpers\Collection $collection */
        $collection = collection('modUser');

        if (!isset($criteria)) {
            return $collection;
        }
        if (is_array($criteria)) {
            $select = isset($criteria['select']) ? $criteria['select'] : $modx->getSelectColumns('modUser');
            $collection->select($select);
            if (isset($criteria['sortby'])) {
                list($sortby, $sortdir) = explode(',', $criteria['sortby']);
                $sortdir = is_null($sortdir) ? 'ASC' : trim($sortdir);
                $collection->sortby($sortby, $sortdir);
            }
            if (isset($criteria['limit'])) {
                list($limit, $offset) = explode(',', $criteria['limit']);
                $offset = is_null($offset) ? 0 : $offset;
                $collection->limit($limit, $offset);
            }
            $complex = isset($criteria['select']) || isset($criteria['sortby']) || isset($criteria['limit']) || isset($criteria['where']);
            if ($complex) {
                $where = isset($criteria['where']) ? $criteria['where'] : false;
            } else {
                $where = $criteria;
            }
            if ($where) $collection->where($where);
        }
        return $asObject ? $collection->get() : $collection->toArray();
    }
}
if (!function_exists('is_auth')) {
    /**
     * Determines if this user is authenticated in a specific context or current context.
     * @param string $ctx
     * @return bool
     */
    function is_auth($ctx = '')
    {
        global $modx;
        if (!trim($ctx)) $ctx = $modx->context->get('key');
        return is_object($modx->user) ? $modx->user->isAuthenticated($ctx) : false;
    }
}
if (!function_exists('is_guest')) {
    /**
     * Checks if the user is a guest
     * @return bool
     */
    function is_guest()
    {
        global $modx;
        return $modx->user->id == 0;
    }
}
if (!function_exists('can')) {
    /**
     * Returns true if the user has the specified policy permission.
     * @param string $pm Permission
     * @return bool
     */
    function can($pm)
    {
        global $modx;

        return $modx->hasPermission($pm);
    }
}
if (!function_exists('quote')) {
    /**
     * Quote the string.
     * @see http://php.net/manual/ru/function.pdo-quote.php
     * @param string $string
     * @param int $parameter_type
     * @return string
     */
    function quote($string, $parameter_type = PDO::PARAM_STR)
    {
        global $modx;

        return $modx->quote($string, $parameter_type);
    }
}
if (!function_exists('escape')) {
    /**
     * Escapes the provided string using the platform-specific escape character.
     * @param string $string
     * @return string
     */
    function escape($string)
    {
        global $modx;

        return $modx->escape($string);
    }
}
if (!function_exists('object_exists')) {
    /**
     * Checks the object existence
     * @param string $className
     * @param int|string|array $criteria
     * @return bool
     */
    function object_exists($className, $criteria = null)
    {
        global $modx;
        if (is_scalar($criteria)) {
            $pk = $modx->getPK($className);
            $criteria = array($pk => $criteria);
        }

        return $modx->getCount($className, $criteria) ? true : false;
    }
}
if (!function_exists('resource_exists')) {
    /**
     * Checks the resource existence
     * @param array $criteria
     * @return bool
     */
    function resource_exists($criteria = null)
    {
        return object_exists('modResource', $criteria);
    }
}
if (!function_exists('user_exists')) {
    /**
     * Checks the user existence.
     * @param array $criteria
     * @return bool
     */
    function user_exists($criteria = null)
    {
        function trimFields ($value) {
            return trim($value,' `');
        }
        function prepareUserFields ($value) {
            return 'modUser.' . $value;
        }
        function prepareProfileFields ($value) {
            return 'Profile.' . $value;
        }
        global $modx;
        $userFields = explode(',', $modx->getSelectColumns('modUser'));
        $userFields = array_map('trimFields',$userFields);
        $fullUserFields = array_map('prepareUserFields',$userFields);

        $profileFields = explode(',', $modx->getSelectColumns('modUserProfile','','',array('id'),true));
        $profileFields = array_map('trimFields',$profileFields);
        $fullProfileFields = array_map('prepareProfileFields',$profileFields);

        $fields = array_merge($userFields, $profileFields);
        $fullFields = array_merge($fullUserFields, $fullProfileFields);
        $query = $modx->newQuery('modUser');
        $query->innerJoin('modUserProfile', 'Profile');
        if (is_numeric($criteria)) {
            $criteria = array('id' => $criteria);
        } elseif (is_string($criteria)) {
            $criteria = array('username' => $criteria);
        }
        if (is_array($criteria)) {
            $where = array();
            foreach ($criteria as $key => $value) {
                $key = str_replace($fields,$fullFields, $key);
                $where[$key] = $value;
            }
            $query->where($where);
        }
        $rowCount = null;
        if ($query->prepare() && $query->stmt->execute()) {
            $rowCount = $query->stmt->rowCount();
        }
        return isset($rowCount) ? $rowCount > 0 : false;
    }
}
if (!function_exists('user_id')) {
    /**
     * Gets id of the current user.
     * @return int|null
     */
    function user_id()
    {
        global $modx;
        return isset($modx->user) ? $modx->user->id : null;
    }
}
if (!function_exists('res_id')) {
    /**
     * Gets id of the current resource.
     * @return int|null
     */
    function res_id()
    {
        return resource_id();
    }
}
if (!function_exists('resource_id')) {
    /**
     * Gets id of the current resource.
     * @return int|null
     */
    function resource_id()
    {
        global $modx;
        return isset($modx->resource) ? $modx->resource->id : null;
    }
}
if (!function_exists('template_id')) {
    /**
     * Gets the template id of the current resource.
     * @return int
     */
    function template_id()
    {
        global $modx;
        return isset($modx->resource) ? $modx->resource->template : null;
    }
}

if (!function_exists('tv')) {
    /**
     * Gets TV of the current resource.
     * @param mixed $id Either the ID of the TV, or the name of the TV.
     * @return null|mixed The value of the TV for the Resource, or null if the TV is not found.
     */
    function tv($id)
    {
        global $modx;

        return isset($modx->resource) ? $modx->resource->getTVValue($id) : null;
    }
}

if (!function_exists('str_clean')) {
    /**
     * Sanitize the specified string
     *
     * @param string $str
     * @param string|array $chars Magic. Chars or allowed tags.
     * @param array $allowedTags Allowed tags.
     * @return string
     */
    function str_clean($str, $chars = '/\'"();><', $allowedTags = array())
    {
        if (is_string($chars)) {
            $chars = str_split($chars);
        } elseif (is_array($chars)) {
            $allowedTags = implode('', $chars);
            $chars = str_split('/\'"();><');
        }
        if (!empty($allowedTags) && is_array($allowedTags)) $allowedTags = implode('', $allowedTags);

        return str_replace($chars, '', strip_tags($str, $allowedTags));
    }
}
if (!function_exists('table_name')) {
    /**
     * Gets the actual run-time table name from a specified class name.
     *
     * @param string $className
     * @param bool $includeDb
     * @return string
     */
    function table_name($className, $includeDb = false)
    {
        global $modx;
        return $modx->getTableName($className, $includeDb);
    }
}

if (!function_exists('columns')) {
    /**
     * Gets select columns from a specific class for building a query
     *
     * @param string $className Имя класса
     * @param string $tableAlias
     * @param string $columnPrefix
     * @param array $columns
     * @param bool $exclude
     * @return string Колонки.
     */
    function columns($className, $tableAlias = '', $columnPrefix = '', $columns = array (), $exclude= false)
    {
        global $modx;
        return $modx->getSelectColumns($className, $tableAlias, $columnPrefix, $columns, $exclude);
    }
}
if (!function_exists('is_email')) {
    /**
     * Validates the email
     *
     * @param string
     * @return bool
     */
    function is_email($string)
    {
        return preg_match('/^[a-zA-Z0-9_.+-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$/',$string);
    }
}
if (!function_exists('is_url')) {
    /**
     * Validates the URL
     *
     * @param string
     * @return bool
     */
    function is_url($string)
    {
        return preg_match('/^((https|http):\/\/)?([a-z0-9]{1})([\w\.]+)\.([a-z]{2,6}\.?)(\/[\w\.]*)*\/?$/',$string);
    }
}
if (!function_exists('log_error')) {
    /**
     * $modx->log(modX::LOG_LEVEL_ERROR,$message)
     *
     * @param string|array $message
     * @param bool $changeLevel Change log level
     * @param string $target
     * @param string $def
     * @param string $file
     * @param string $line
     */
    function log_error($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        global $modx;
        /** @var modHelpers\Logger $class */
        $class = config('modhelpers_loggerClass', 'modHelpers\Logger', true);
        $class::getInstance($modx)->error($message, $changeLevel, $target, $def, $file, $line);
    }
}
if (!function_exists('log_warn')) {
    /**
     * $modx->log(modX::LOG_LEVEL_WARN, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     * @param string $target
     * @param string $def
     * @param string $file
     * @param string $line
     */
    function log_warn($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        global $modx;
        /** @var modHelpers\Logger $class */
        $class = config('modhelpers_loggerClass', 'modHelpers\Logger', true);
        $class::getInstance($modx)->warn($message, $changeLevel, $target, $def, $file, $line);
    }
}
if (!function_exists('log_info')) {
    /**
     * $modx->log(modX::LOG_LEVEL_INFO, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     * @param string $target
     * @param string $def
     * @param string $file
     * @param string $line
     */
    function log_info($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        global $modx;
        /** @var modHelpers\Logger $class */
        $class = config('modhelpers_loggerClass', 'modHelpers\Logger', true);
        $class::getInstance($modx)->info($message, $changeLevel, $target, $def, $file, $line);
    }
}
if (!function_exists('log_debug')) {
    /**
     * $modx->log(modX::LOG_LEVEL_DEBUG, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     * @param string $target
     * @param string $def
     * @param string $file
     * @param string $line
     */
    function log_debug($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        global $modx;
        /** @var modHelpers\Logger $class */
        $class = config('modhelpers_loggerClass', 'modHelpers\Logger', true);
        $class::getInstance($modx)->debug($message, $changeLevel, $target, $def, $file, $line);
    }
}
if (!function_exists('context')) {
    /**
     * Gets the specified property of the current context
     * @param string $key
     * @return string
     */
    function context($key = 'key')
    {
        global $modx;
        return $modx->context->get($key);
    }
}
if (!function_exists('query')) {
    /**
     * Manages a SQL query
     * @param string $query
     * @return modHelpers\Query
     */
    function query($query)
    {
        global $modx;
        $queryClass = config('modhelpers_queryClass', 'modHelpers\Query', true);
        return new $queryClass($modx, $query);
    }
}
if (!function_exists('memory')) {
    /**
     * Return the formatted amount of memory allocated to PHP
     * @param string $unit
     * @return string
     */
    function memory($unit = 'KB')
    {
        switch ($unit) {
            case 'byte':
                $value = number_format(memory_get_usage(true), 0,","," ") . " $unit";
                break;
            case 'MB':
                $value = number_format(memory_get_usage(true) / (1024*1024), 0,","," ") . " $unit";
                break;
            case 'KB':
            default:
                $value = number_format(memory_get_usage(true) / 1024, 0,","," ") . " $unit";
        }

        return $value;
    }
}
if (!function_exists('faker')) {
    /**
     * Makes fake data
     * @see https://github.com/fzaninotto/Faker
     * @param string|array $property
     * @param string $locale
     * @return mixed
     */
    function faker($property = '', $locale = '')
    {
        if (!app()->bound('faker')) {
            app()->singleton('faker', function() use ($locale) {
                if (empty($locale)) {
                    $lang = config('cultureKey');
                    switch ($lang) {
                        case 'ru':
                            $locale = 'ru_RU';
                            break;
                        case 'de':
                            $locale = 'de_DE';
                            break;
                        case 'fr':
                            $locale = 'fr_FR';
                            break;
                        default:
                            $locale = 'en_US';
                    }
                }
                return \Faker\Factory::create($locale);
            });
        }
        $faker = app('faker');
        if (func_num_args() == 0) return $faker;

        try {
            if (is_array($property)) {
                $func = key($property);
                $params = current($property);
                $output = call_user_func_array(array($faker, $func), $params);
            } else {
                $output = $faker->$property;
            }
        } catch (Exception $e) {
            log_error($e->getMessage());
            $output = '';
        }
        return  $output;
    }
}
if (!function_exists('img')) {
    /**
     * Returns the HTML tag "img".
     * @param string $src
     * @param array $attrs
     * @return string
     */
    function img($src, $attrs = array())
    {
        $attributes = '';
        if (!empty($attrs) && is_array($attrs)) {
            foreach ($attrs as $k => $v) {
                $attributes .= $k . '="' . $v . '" ';
            }
        }
        return '<img src="'. $src.'" ' . $attributes . '>';
    }
}

if (!function_exists('load_model')) {
    /**
     * Load a model for a custom table.
     * @param string $class Class name.
     * @param string|callable $table Table name without the prefix or Closure.
     * @param callable $callback Closure
     * @return bool
     */
    function load_model($class, $table, $callback = NULL)
    {
        global $modx;
        if (func_num_args() == 2 && is_callable($table)) {
            $callback = $table;
            $table = '';
        }
        $key = strtolower($class) . '_map';
        if (config('modHelpers_cache_model', true) && $map = cache($key)) {
            if (!empty($table)) {
                $modx->map[$class] = $map;
            } else {
                $modx->map[$class] = array_merge_recursive($modx->map[$class], $map);
            }
            return true;
        }
        $builderClass = config('modhelpers_modelBuilderClass', 'modHelpers\ModelBuilder', true);
        /** @var modHelpers\ModelBuilder $model */
        $model = new $builderClass($table);
        if (is_callable($callback)) {
            $callback($model);
            $map = $model->output();
            if (!empty($map)) {
                if (!empty($table)) {
                    $modx->map[$class] = $map;
                } else {
                    $modx->map[$class] = array_merge_recursive($modx->map[$class], $map);
                }
                if (config('modHelpers_cache_model', true)) cache()->set($key, $map);
                return true;
            }
        }
        return false;
    }
}
if (!function_exists('login')) {
    /**
     * Logs in the specified user.
     * @param mixed $user
     * @return bool
     */
    function login($user)
    {
        global $modx;
        if (is_scalar($user) || is_array($user)) $user = user($user);
        if ($user instanceof modUser && !$user->hasSessionContext($modx->context->key)) {
            $modx->user = $user;
            $modx->user->addSessionContext($modx->context->key);
            $modx->getUser(context(), true);
        }
        return ($user instanceof modUser) ? true : false;
    }
}
if (!function_exists('logout')) {
    /**
     * Logs out the current user.
     * @param bool $redirect True to redirect to the unauthorized page.
     * @param int $code Response code
     * @param bool $relogin Only logout or login as admin (if the user is authenticated in the mgr context).
     * @return bool
     */
    function logout($redirect = false, $code = 401, $relogin = true)
    {
        global $modx;
        $response = $modx->runProcessor('security/logout');
        if ($response->isError()) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Logout error of the user: '.$modx->user->get('username').' ('.$modx->user->get('id').'). Response: ' . $response->getMessage());
            return false;
        }
        $modx->user = null;
        if ($relogin) {
            $modx->getUser();
        } else {
            $modx->user = $modx->newObject('modUser');
            $modx->user->fromArray(array(
                'id' => 0,
                'username' => $modx->getOption('default_username', '', '(anonymous)', true)
            ), '', true);
            $modx->toPlaceholders($modx->user->get(array('id','username')),'modx.user');

        }
        if ($redirect) abort($code);
        return true;
    }
}
if (!function_exists('is_ajax')) {
    /**
     * Checks request is ajax or not.
     * @return bool
     */
    function is_ajax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return mixed|modHelpers\Container
     */
    function app($abstract = null, array $parameters = array())
    {
        /** @var modHelpers\Container $class */
        $class = config('modhelpers_containerClass', 'modHelpers\Container', true);
        if (is_null($abstract)) {
            return $class::getInstance();
        }

        return empty($parameters)
            ? $class::getInstance()->make($abstract)
            : $class::getInstance()->makeWith($abstract, $parameters);
    }
}
if (!function_exists('is_mobile')) {
    /**
     * Mobile detector.
     * @return bool
     * @see http://mobiledetect.net/
     */
    function is_mobile()
    {
        $detector = app('detector');
        return $detector->isMobile();
    }
}
if (!function_exists('is_tablet')) {
    /**
     * Mobile detector.
     * @return bool
     * @see http://mobiledetect.net/
     */
    function is_tablet()
    {
        $detector = app('detector');
        return $detector->isTablet();
    }
}
if (!function_exists('is_desktop')) {
    /**
     * Mobile detector.
     * @return bool
     * @see http://mobiledetect.net/
     */
    function is_desktop()
    {
        $detector = app('detector');
        return !$detector->isMobile() && !$detector->isTablet();
    }
}
if (!function_exists('array_empty')) {
    /**
     * Checks the given variable is an array and empty.
     *
     * @param $array
     * @return bool
     */
    function array_empty($array)
    {
        return empty($array) && is_array($array);
    }
}
if (!function_exists('array_notempty')) {
    /**
     * Checks the given variable is an array and not empty.
     *
     * @param $array
     * @return bool
     */
    function array_notempty($array)
    {
        return !empty($array) && is_array($array);
    }
}

if (!function_exists('array_trim')) {
    /**
     * Execute the trim function for array values. Recursive.
     *
     * @param mixed $value
     * @param string $chars
     * @param string $func Trim functions - trim, ltrim, rtrim.
     * @return string|array
     */
    function array_trim($value, $chars = '', $func = 'trim')
    {
        if (is_array($value)) {
            return array_map(function ($v) use ($chars, $func) {
                return array_trim($v, $chars, $func);
            }, $value);
        }
        return empty($chars) ? $func($value) : $func($value, $chars);
    }
}
if (!function_exists('array_ltrim')) {
    /**
     * Strip whitespace (or other characters) from the beginning of a string in the array
     *
     * @param $value
     * @param string $chars
     * @return string
     */
    function array_ltrim($value, $chars = '')
    {
        return array_trim($value, $chars, 'ltrim');
    }
}
if (!function_exists('array_rtrim')) {
    /**
     * Strip whitespace (or other characters) from the end of a string in the array
     *
     * @param $value
     * @param string $chars
     * @return string
     */
    function array_rtrim($value, $chars = '')
    {
        return array_trim($value, $chars, 'rtrim');
    }
}
if (!function_exists('explode_trim')) {
    /**
     * Combine two functions - explode and trim
     *
     * @param string $delimiter
     * @param string $string
     * @param string $chars
     * @param string $func
     * @return array
     */
    function explode_trim($delimiter, $string, $chars = '', $func = 'trim')
    {
        $array = explode($delimiter, $string);
        return array_trim($array, $chars, $func);
    }
}
if (!function_exists('explode_rtrim')) {
    /**
     * Combine two functions - explode and rtrim
     *
     * @param string $delimiter
     * @param string $string
     * @param string $chars
     * @return array
     */
    function explode_rtrim($delimiter, $string, $chars = '')
    {
        $array = explode($delimiter, $string);
        return array_trim($array, $chars, 'rtrim');
    }
}
if (!function_exists('explode_ltrim')) {
    /**
     * Combine two functions - explode and ltrim
     *
     * @param string $delimiter
     * @param string $string
     * @param string $chars
     * @return array
     */
    function explode_ltrim($delimiter, $string, $chars = '')
    {
        $array = explode($delimiter, $string);
        return array_trim($array, $chars, 'ltrim');
    }
}
if (!function_exists('echo_nl')) {
    /**
     * Output a string with the end of line symbol.
     *
     * @param mixed $string
     * @param string $nl.
     * @return void
     */
    function echo_nl($string, $nl = PHP_EOL)
    {
        echo $string . $nl;
    }
}if (!function_exists('echo_br')) {
    /**
     * Add a tag to the end of the string and output the result.
     *
     * @param mixed $string
     * @return void
     */
    function echo_br($string)
    {
        echo $string . "<br>";
    }
}
if (!function_exists('print_str')) {
    /**
     * Convert the specified variable to the string type and print or print it.
     *
     * @param mixed $value The input value
     * @param bool $return If true the value will be returned. Otherwise it will be printed.
     * @param string $template Wrapper.
     * @param string $tag Tag to replace with the prepared string.
     * @return string
     */
    function print_str($value, $return = false, $template = '', $tag = 'output')
    {
        if (is_null($value)) {
            $value = 'NULL';
        } elseif (is_bool($value)) {
            $value = $value ? 'TRUE' : 'FALSE';
        } elseif (is_array($value)) {
            $value = '<pre>' . print_r($value, true) . '</pre>';
        } elseif (is_object($value) && (method_exists($value, 'toArray'))) {
            $value = '<pre>' . print_r($value->toArray(), true) . '</pre>';
        }
        if (is_string($return)) {
            $tag = $template ?: $tag;
            $template = $return;
            $return = false;
            //echo !empty($template) ? str_replace("[[+{$tag}]]", $value, $template) : $value;
        }
        if (!empty($template)) {
            switch ($template) {
            	case 'p':
                    $output = '<p>'.$value.'</p>';
            		break;
            	case 'div':
                    $output = '<div>'.$value.'</div>';
            		break;
            	case 'li':
                    $output = '<li>'.$value.'</li>';
            		break;
                default:
                    $output = str_replace("[[+{$tag}]]", $value, $template);
            }
        } else {
            $template = config('modhelpers_print_template');
            $output = empty($template) ? $value : str_replace("[[+{$tag}]]", $value, $template);
        }

        if ((is_bool($return) || is_numeric($return)) && !$return) {
            echo $output;
        } elseif (is_string($return)) {
            $tag = $template ?: $tag;
            $template = $return;
            echo !empty($template) ? str_replace("[[+{$tag}]]", $value, $template) : $value;
        } else {
            return $output;
        }
        return '';
    }
}
if (!function_exists('print_d')) {
    /**
     * Prints the specified variable and die.
     *
     * @param mixed $string
     * @param string $template
     * @param string $tag Tag to replace with the prepared string.
     * @return string
     */
    function print_d($string, $template = '', $tag = 'output')
    {
        $output = print_str($string, $template, $tag);
        die($output);
    }
}
if (! function_exists('parse')) {
    /**
     * Parse a string using an associative array of replacement variables.
     *
     * @param string $string Source string to parse.
     * @param array $data An array of placeholders to replace.
     * @param string|bool $prefix Magic. The placeholder prefix or flag for complete parsing.
     * @param string|int $suffix Magic. The placeholder suffix (for simple mode) or
     * the maximum iterations to recursively process tags.
     * @return string The processed string with the replaced placeholders.
     */
    function parse($string, $data, $prefix = '[[+', $suffix = ']]')
    {
        global $modx;
        if (!empty($string)) {
            if (is_array($data)) {
                if (is_bool($prefix) && $prefix) {
                    // NEEDTEST
                    /** @var modChunk $chunk */
                    /*$chunk = $this->modx->newObject('modChunk', array('name' => str_random(), 'content' => $string));
                    $chunk->setCacheable(false);
                    $string = $chunk->process($data);
                    */
                    $parser = $modx->getParser();
                    $maxIterations = (is_numeric($suffix)) ? (int) $suffix : (int) $modx->getOption('parser_max_iterations', null, 10);
                    $scope = $modx->toPlaceholders($data, '', '.', true);
                    $parser->processElementTags('', $string, false, false, '[[', ']]', array(), $maxIterations);
                    $parser->processElementTags('', $string, true, true, '[[', ']]', array(), $maxIterations);
                    if (isset($scope['keys'])) $modx->unsetPlaceholders($scope['keys']);
                    if (isset($scope['restore'])) $modx->toPlaceholders($scope['restore']);
                } else {
                    foreach ($data as $key => $value) {
                        $string = str_replace($prefix . $key . $suffix, $value, $string);
                    }
                }
            }
        }
        return $string;
    }
}
if (! function_exists('str_starts')) {
    /**
     * Determine if a given string starts with a given substring.
     * (Taken from Laravel helpers)
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @param bool $case Match the case
     * @return bool
     */
    function str_starts($haystack, $needles, $case = false)
    {
        if (!$case) {
            $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack) : strtolower($haystack);
        }
        foreach ((array) $needles as $needle) {
            if (!$case) $needle = function_exists('mb_strtolower') ? mb_strtolower($needle) : strtolower($needle);
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }
}
if (! function_exists('str_ends')) {
    /**
     * Determine if a given string ends with a given substring.
     * (Taken from Laravel helpers)
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @param bool $case Match the case
     * @return bool
     */
    function str_ends($haystack, $needles, $case = false)
    {
        if (!$case) {
            $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack) : strtolower($haystack);
        }

        foreach ((array) $needles as $needle) {
            if (!$case) $needle = function_exists('mb_strtolower') ? mb_strtolower($needle) : strtolower($needle);
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }
}
if (! function_exists('str_contains')) {
    /**
     * Determine if a given string contains a given substring.
     * (Taken from Laravel helpers)
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @param bool $case Match the case
     * @return bool
     */
    function str_contains($haystack, $needles, $case = false)
    {
        if ($case) {
            $func = function_exists('mb_strpos') ? 'mb_strpos' : 'strpos';
        } else {
            $func = function_exists('mb_stripos') ? 'mb_stripos' : 'stripos';
        }
        foreach ((array) $needles as $needle) {
            if ($needle != '' && $func($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('str_match')) {
    /**
     * Determine if a given string matches a given pattern.
     * (Taken from Laravel helpers - str_is() )
     *
     * @param  string $value
     * @param  string $pattern
     * @param bool $case Match the case
     * @return bool
     */
    function str_match($value, $pattern, $case = false)
    {
        if ($pattern == $value) {
            return true;
        }
        $ci = $case ? '' : 'i';
        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);
        return (bool) preg_match('#^'.$pattern.'\z#u'.$ci, $value);
    }
}
if (! function_exists('str_between')) {
    /**
     * Get a substring between two tags.
     *
     * @param  string $string
     * @param  string $start Start tag
     * @param  string $end End tag
     * @param bool $greedy
     * @return string
     */
    function str_between($string, $start, $end, $greedy = true)
    {
        $mask = $greedy ? '(.*)' : '(.*?)';
        preg_match('#'.preg_quote($start).$mask.preg_quote($end).'#is', $string, $match);
        return $match[1];
    }
}
if (! function_exists('str_limit')) {
    /**
     * Limit the number of characters in a string.
     *
     * @param  string $string
     * @param  int $limit
     * @param  string $end
     * @return string
     */
    function str_limit($string, $limit = 100, $end = '...')
    {
        $lfunc = function_exists('mb_strwidth') ? 'mb_strwidth' : 'strlen';
        if ($lfunc($string, 'UTF-8') <= $limit) {
            return $string;
        }

        return function_exists('mb_strimwidth')
                            ? rtrim(mb_strimwidth($string, 0, $limit, $end, 'UTF-8'))
                            : rtrim(substr($string, 0, $limit)) . $end;
    }
}
if (! function_exists('str_random')) {
    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     */
    function str_random($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }
}
if (! function_exists('default_if')) {
    /**
     * Returns default value if a given value equals null or the specified value.
     *
     * @param  mixed $value
     * @param  mixed $default
     * @param null $compared
     * @return mixed
     */
    function default_if($value, $default = '', $compared = null)
    {
        if (isset($compared)) {
            $value = $value === $compared ? $default : $value;
        } elseif (is_null($value)) {
            $value = $default;
        }
        return $value;
    }
}
if (! function_exists('null_if')) {
    /**
     * Returns NULL if the given values are equal.
     *
     * @param mixed $value
     * @param mixed $compared
     * @return mixed
     */
    function null_if($value, $compared = '')
    {
        return $value === $compared ? null : $value;
    }
}
if (! function_exists('filter_data')) {
    /**
     * Filters the array according to the specified rules.
     *
     * @param  array $data
     * @param  array $rules
     * @param bool $intersect
     * @return mixed
     */
    function filter_data(array $data, array $rules, $intersect = false)
    {
        global $modx;
        $filtered = array();
        foreach ($rules as $key => $types) {
            $types = is_callable($types) ? array('func'=>$types) : explode('|', $types);
            $_var = $data[$key];
            foreach ($types as $type) {
                $options = null;
                if (!is_callable($type) && strpos($type, ':') !== false) {
                    list($type, $options) = explode(':', $type);
                }
                if (isset($_var) || in_array($type, array('bool', 'boolean', 'default')) || is_callable($type)) {
                    if (is_callable($type) && !in_array($type, array('implode','explode','trim','email','url'))) {
                        if ($options == 'true') $options = true;
                        if ($options == 'false') $options = false;
                        if (isset($_var)) $_var = isset($options) ? $type($_var, $options) : $type($_var);
                    } else {
                        switch ($type) {
                            case 'int':
                            case 'integer':
                                $_var = (int) $_var;
                                break;
                            case 'string':
                                $_var = filter_var(trim($_var), FILTER_SANITIZE_STRING);
                                break;
                            case 'float':
                                $_var = (float) $_var;
                                break;
                            case 'array':
                                $_var = is_array($_var) ? $_var : (!empty($_var) ? array($_var) : array());
                                break;
                            case 'bool':
                            case 'boolean':
                                $_var = filter_var($_var, FILTER_VALIDATE_BOOLEAN);
                                break;
                            case 'alpha':
                                $_var = preg_replace('/[^[:alpha:]]/', '', $_var);
                                break;
                            case 'alpha_num':
                                $_var = preg_replace('/[^[:alnum:]]/', '', $_var);
                                break;
                            case 'num':
                                $_var = str_replace(array('-', '+'), '', filter_var($_var, FILTER_SANITIZE_NUMBER_INT));
                                break;
                            case 'email':
                                $_var = filter_var($_var, FILTER_SANITIZE_EMAIL);
                                break;
                            case 'url':
                                $_var = filter_var($_var, FILTER_SANITIZE_URL);
                                break;
                            case 'limit':
                                $options = !empty($options) ? explode(',', $options) : array(100, '...');
                                if (!isset($options[1])) $options[1] = '...';
                                $_var = str_limit($_var, $options[0], $options[1]);
                                break;
                            case 'implode':
                                if (is_array($_var)) {
                                    $delimeter = $options ?: ',';
                                    $_var = implode($delimeter, $_var);
                                }
                                break;
                            case 'explode':
                                $delimeter = $options ?: ',';
                                $_var = explode($delimeter, $_var);
                                break;
                            case 'trim':
                                $chars = $options ?: " \t\n\r\0\x0B";
                                if (is_array($_var)) {
                                    $_var = array_trim($_var, $chars);
                                } elseif (is_string($_var)) {
                                    trim($_var, $chars);
                                }
                                break;
                            case 'fromJSON':
                                $_var = json_decode($_var, true);
                                break;
                            case 'toJSON':
                                $_var = json_encode($_var, true);
                                break;
                            case 'default':
                                //$option = isset($option) ? $option : null;
                                $_var = default_if($_var, $options);
                                break;
                            default:
                                if (class_exists($type)) {
                                    if (!$_var = $modx->getObject($type, $_var)) {
                                        $_var = $modx->newObject($type);
                                    }
                                }
                        }
                    }
                }
            }
            if (isset($_var)) $filtered[$key] = $_var;
        }
        return $intersect ? $filtered : array_merge($data, $filtered);
    }
}
if (!function_exists('request')) {
    /**
     * Returns a modHelpers\Request object or an input item from the request.
     * @param string $key
     * @param mixed $default
     * @return mixed|\modHelpers\Request
     */
    function request($key = null, $default = null)
    {
        $request = app('request');
        if (func_num_args() == 0) return $request;
        return $request->input($key, $default);
    }
}
if (!function_exists('switch_context')) {
    /**
     * Switches the primary Context for the modX instance.
     * @param string|array|callable $key
     * @param array $excluded
     * @return bool
     */
    function switch_context($key, $excluded = array())
    {
        global $modx;
        if (is_string($key)) {
            return $modx->switchContext($key);
        } elseif (is_array($key)) {
            reset($key);
            $attribute = key($key);
            $value = current($key);
            if (empty($attribute) || empty($value)) return false;
            $query = query('SELECT `context_key` FROM ' . table_name('modContextSetting') . ' WHERE `key` = ? AND TRIM(BOTH \'/\' FROM `value`) = ?')->bind($attribute, $value)->first();
            $ctx = $query['context_key'];
            if (!empty($ctx) && !in_array($ctx, $excluded)) {
                if ($attribute == 'base_url' && $modx->getOption('friendly_urls')) {
                    $alias = $modx->getOption('request_param_alias', null, 'alias', true);
                    $_REQUEST[$alias] = preg_replace('/^' . $value . '\//', '', $_REQUEST[$alias]);
                }
                return $modx->switchContext($ctx);
            }
        } elseif (is_callable($key) && $ctx = $key($modx)) {
            return $modx->switchContext($ctx);
        }
        return false;
    }
}
if (! function_exists('csrf_meta')) {
    /**
     * Generate a HTML meta tag with the CSRF token.
     *
     * @return string
     */
    function csrf_meta()
    {
        return '<meta name="csrf-token" content="' . csrf_token() . '">'."\n";
    }
}
if (! function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="csrf_token" value="'.csrf_token().'">' . "\n";
    }
}
if (! function_exists('csrf_token')) {
    /**
     * Set and get the CSRF token from the session.
     *
     * @param bool $regenerate
     * @return string
     */
    function csrf_token($regenerate = false)
    {
        $timeout = (int) config('modhelpers_token_ttl', 0);
        if (!$regenerate && abs($timeout) > 0 && defined('MODX_API_MODE') && !MODX_API_MODE) {
            if ( default_if(session('csrf_token.timestamp'), time()) < time() ) {
                $regenerate = true;
            }
        }
        if ($regenerate || empty(session('csrf_token.value'))) {
            session(['csrf_token' => [
                        'value' => str_random(40),
                        'timestamp' => time() + abs($timeout) * 60,
                    ]
            ]);
        }
        return session('csrf_token.value');
    }
}
if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param  mixed  $content
     * @param  int    $status
     * @param  array  $headers
     * @return \modHelpers\ResponseManager
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        /** @var modHelpers\ResponseManager $manager */
        $manager = app('response');

        if (func_num_args() === 0) {
            return $manager;
        }

        return $manager->make($content, $status, $headers);
    }
}

/*if (!function_exists('queue')) {
    function queue()
    {
        return new modHelpers\Queue;
    }
}*/