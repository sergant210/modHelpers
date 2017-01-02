<?php
require_once __DIR__ . '/classes.php';

/***********************************************/
/*              Functions                      */
/***********************************************/
if (!function_exists('url')) {
    /**
     * Формирует Url
     * @param int $id Page id
     * @param string $context Context
     * @param array $arg Ling arguments
     * @param int $scheme Scheme
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
     * @param array|boolean $options Options
     * @param string $type
     * @param string $responseCode
     */
    function redirect($url, $options = false, $type = '', $responseCode = '')
    {
        global $modx;
        if (is_numeric($url)) {
            if (is_string($options)) {
                $ctx = $options;
            } else {
                $ctx = isset($options['context']) ? $options['context'] : '';
            }
            $url = url($url, $ctx);
        }
        if (!empty($url)) $modx->sendRedirect($url, $options, $type, $responseCode);
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
     * @param string $key
     * @param null|mixed $value
     * @return array|null|string
     */
    function config($key = '', $value = NULL)
    {
        global $modx;
        if (!empty($key)) {
            if (isset($value)) {
                $modx->config[$key] = $value;
                return $value;
            }
            return isset($modx->config[$key]) ? $modx->config[$key] : '';
        } else {
            return $modx->config;
        }
    }
}
if (!function_exists('session')) {
    /**
     * Manages the session
     * @param string $key Use the dot notation.
     * @param string|null $value Value or NULL.
     * @return mixed
     */
    function session($key = '', $value = '')
    {
        if (empty($key)) {
            return $_SESSION;
        }
        $delete = is_null($value);
        if (!empty($value) || $delete) {
            $keys = explode('.', $key);
            if (count($keys) == 1) {
                $rootKey = array_shift($keys);
                $_SESSION[$rootKey] = $value;
            } else {
                $_key = array_shift($keys);
                if (!isset($rootKey)) $rootKey = $_key;
                if (! isset($array[$key]) || ! is_array($array[$key])) {
                    $_SESSION[$_key] = array();
                }
                $array =& $_SESSION[$_key];
                while (count($keys) > 1) {
                    $_key = array_shift($keys);
                    $array[$_key] = array();
                    $array = &$array[$_key];
                }
                $array[array_shift($keys)] = $delete ? null : $value;
            }
            return $_SESSION[$rootKey];
        }
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        $array = $_SESSION;
        foreach (explode('.', $key) as $segment) {
            if (isset($array[$segment])) {
                $array = $array[$segment];
            } else {
                return '';
            }
        }

        return $array;
    }
}
if (!function_exists('cache')) {
    /**
     * Manages the cache
     * @see https://docs.modx.com/revolution/2.x/developing-in-modx/advanced-development/caching
     * @param string|array $key
     * @param null|string|array $options
     * @return mixed|modCacheManager
     */
    function cache($key = '', $options = NULL)
    {
        global $modx;
        if (empty($key) && empty($options)) {
            return new extCacheManager($modx->getCacheManager());
        }
        if (is_string($options)) {
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        }
        if (is_numeric($options)) {
            $options = array(xPDO::OPT_CACHE_EXPIRES => (int) $options);
        }
        if (is_array($key)) {
            foreach ($key as $itemKey => $itemValue) {
                $lifetime = isset($options[xPDO::OPT_CACHE_EXPIRES]) ? $options[xPDO::OPT_CACHE_EXPIRES] : 0;
                $response = $modx->getCacheManager()->set($itemKey, $itemValue, $lifetime, $options);
            }
            return $response;
        } else {
            return $modx->getCacheManager()->get($key, $options);
        }
    }
}
if (!function_exists('pls')) {
    /**
     * Gets/sets placeholders
     * @param string|array $key String to get a placeholder, array ('key'=>'value') - to set one/ones.
     * @param string $default
     * @return array|bool|string
     */
    function pls($key = '', $default = '')
    {
        global $modx;

        if (empty($key)) {
            return $modx->placeholders;
        }
        if (is_array($key)) {
            foreach ($key as $itemKey => $itemValue) {
                $modx->placeholders[$itemKey] = $itemValue;
            }
            return true;
        } else {
            return isset($modx->placeholders[$key]) ? $modx->placeholders[$key] : $default;
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
     * Отправляет Email.
     * @param string $email Email.
     * @param string|array $subject Subject or an array of options. Required option keys - subject, content. Optional - sender, from, fromName.
     * @param string $content
     * @return bool
     */
    function email($email, $subject, $content = '')
    {
        global $modx;
        if (is_array($subject)) {
            $options = $subject;
            $options['email'] = $email;
        } else {
            $options = compact('email','subject','content');
        }
        $options['sender'] = isset($options['sender']) ? $options['sender'] : $modx->getOption('emailsender');
        $options['from'] = isset($options['from']) ? $options['from'] : $modx->getOption('emailsender');
        $options['fromName'] = isset($options['fromName']) ? $options['emailFromName'] : $modx->getOption('site_name');
        /* @var modPHPMailer $mail */
        $mail = $modx->getService('mail', 'mail.modPHPMailer');
        $mail->setHTML(true);
        $mail->set(modMail::MAIL_SUBJECT, $options['subject']);
        $mail->set(modMail::MAIL_BODY, $options['content']);
        $mail->set(modMail::MAIL_SENDER, $options['sender']);
        $mail->set(modMail::MAIL_FROM, $options['from']);
        $mail->set(modMail::MAIL_FROM_NAME, $options['fromName']);

        $mail->address('to', $options['email']);
        if (!$mail->send()) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . $mail->mailer->ErrorInfo);
            $mail->reset();
            return false;
        }
        $mail->reset();
        return true;
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
        if (is_numeric($user)) {
            $user = $modx->getObject('modUser', array('id' => (int) $user));
        } elseif (is_string($user)) {
            $user = $modx->getObject('modUser', array('id' => (int) $user));
        }
        if ($user instanceof modUser) $email = $modx->user->Profile->get('email');
        return !empty($email) ? email($email, $subject, $content) : false;
    }
}
if (!function_exists('pdotools')) {
    /**
     * Gets instance of the pdoTools class
     * @param array $options
     * @return pdoTools|boolean
     */
    function pdotools($options = array())
    {
        global $modx;
        $fqn = $modx->getOption('pdoTools.class', null, 'pdotools.pdotools', true);
        if ($pdoClass = $modx->loadClass($fqn, '', false, true)) {
            return new $pdoClass($modx, $options);
        }
        elseif ($pdoClass = $modx->loadClass($fqn, MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
            return new $pdoClass($modx, $options);
        }
        return false;
    }
}
if (!function_exists('pdofetch')) {
    /**
     * * Gets instance of the pdoFetch class
     * @param array $options
     * @return pdoFetch|boolean
     */
    function pdofetch($options = array())
    {
        global $modx;
        $fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
        if ($pdoClass = $modx->loadClass($fqn, '', false, true)) {
            return new $pdoClass($modx, $options);
        }
        elseif ($pdoClass = $modx->loadClass($fqn, MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
            return new $pdoClass($modx, $options);
        }
        return false;
    }
}
if (!function_exists('css')) {
    /**
     * Register CSS to be injected inside the HEAD tag of a resource.
     * @param string $src
     * @param null $media
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
     * @param bool|string $attr async defer
     */
    function script($src, $start = false, $plaintext = false, $attr = false)
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
        if ($pdo = pdotools()) {
            return $pdo->getChunk($chunkName, $properties);
        }
        return $modx->getChunk($chunkName, $properties);
    }
}
if (!function_exists('snippet')) {
    /**
     * Runs the specified snippet.
     * @param $snippetName
     * @param array $params
     * @return string
     */
    function snippet($snippetName, array $params= array ())
    {
        global $modx;
        if ($pdo = pdotools()) {
            return $pdo->runSnippet($snippetName, $params);
        }
        return $modx->runSnippet($snippetName, $params);
    }
}
if (!function_exists('processor')) {
    /**
     * Runs the specified processor.
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
if (!function_exists('object')) {
    /**
     * Gets an object of the specified class.
     * @param string $class
     * @param integer|array $criteria
     * @return ObjectManager
     */
    function object($class, $criteria = null)
    {
        global $modx;
        $object = new ObjectManager($modx, $class);
        if (isset($criteria)) {
            if (is_numeric($criteria)) {
                $pk = $modx->getPK($class);
                $where = array($pk => $criteria);
            } elseif (is_array($criteria)) {
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
     * Gets a collection of the specified class.
     * @param string $class
     * @param array $criteria
     * @return CollectionManager
     */
    function collection($class, $criteria = null)
    {
        global $modx;
        $collection = new CollectionManager($modx, $class);
        if (isset($criteria) && is_array($criteria)) {
            $collection->where($criteria);
        }
        return $collection;
    }
}
if (!function_exists('resource')) {
    /**
     * Gets a resource object/array.
     * @param int|array $criteria Resource id or array with criteria.
     * @param bool $asObject True to return an object. Otherwise - an array.
     * @return array|modResource|bool|ObjectManager
     */
    function resource($criteria = null, $asObject = true)
    {
        /** @var ObjectManager $resourceManager */
        $resourceManager = object('modResource', $criteria);
        if (!isset($criteria)) return $resourceManager;

        return $asObject ? $resourceManager->get() : $resourceManager->toArray();
    }
}
if (!function_exists('resources')) {
    /**
     * Gets a collection of the resources.
     * @param array $criteria Criteria
     * @param bool $asObject True to return an array of the objects. Otherwise - an array of resources data arrays.
     * @return array|bool|CollectionManager
     */
    function resources($criteria = null, $asObject = false)
    {
        global $modx;
        /** @var CollectionManager $collection */
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
     * Gets a user object.
     * @param int|array $criteria User id or array with criteria.
     * @param bool $asObject True to return an object. Otherwise - an array.
     * @return array|modUser
     */
    function user($criteria = null, $asObject = true)
    {
        /** @var ObjectManager $userManager */
        $userManager = object('modUser', $criteria);

        return (isset($criteria) && $asObject) ? $userManager->get() : $userManager->toArray();
    }
}
if (!function_exists('users')) {
    /**
     * @param array $criteria
     * @param bool $asObject True to return an array of the user objects. Otherwise - an array of users data arrays.
     * @return array|CollectionManager
     */
    function users($criteria = null, $asObject = false)
    {
        global $modx;
        /** @var CollectionManager $collection */
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

        if (empty($ctx)) $ctx = $modx->context->get('key');
        return ($modx->user->id > 0) ? $modx->user->isAuthenticated($ctx) : false;
    }
}
if (!function_exists('is_guest')) {
    /**
     * Checks the user is guest.
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
     * Returns true if user has the specified policy permission.
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
if (!function_exists('esc')) {
    /**
     * Escapes the provided string using the platform-specific escape character.
     * @param string $string
     * @return string
     */
    function esc($string)
    {
        global $modx;

        return $modx->escape($string);
    }
}
if (!function_exists('object_exists')) {
    /**
     * Checks the object existence
     * @param $className
     * @param array $criteria
     * @return bool
     */
    function object_exists($className, $criteria = null)
    {
        global $modx;
        if (is_numeric($criteria)) {
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
        return isset($rowCount) ? $rowCount > 0 : $rowCount;
    }
}
if (!function_exists('user_id')) {
    /**
     * Gets id of the current user.
     * @return int
     */
    function user_id()
    {
        global $modx;
        return isset($modx->user) ? $modx->user->id : false;
    }
}
if (!function_exists('resource_id')) {
    /**
     * Gets id of the current resource.
     * @return int
     */
    function resource_id()
    {
        global $modx;
        return isset($modx->resource) ? $modx->resource->id : false;
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
        return isset($modx->resource) ? $modx->resource->template : false;
    }
}

if (!function_exists('tv')) {
    /**
     * Gets TV of the current resource.
     * @param mixed $id
     * @return null|mixed
     */
    function tv($id)
    {
        global $modx;

        return isset($modx->resource) ? $modx->resource->getTVValue($id) : false;
    }
}

if (!function_exists('str_clean')) {
    /**
     * Sanitize the specified string
     *
     * @param string $str
     * @param string|array $chars Magic. Chars or allowed tags.
     * @param array $allowedTags Allowed tags.
     * @return string .
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
     * @return string Название таблицы.
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
     * @param string $message
     * @param bool $changeLevel Change log level
     */
    function log_error($message, $changeLevel = false)
    {
        LogManager::error($message, $changeLevel);
    }
}
if (!function_exists('log_warn')) {
    /**
     * $modx->log(modX::LOG_LEVEL_WARN, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     */
    function log_warn($message, $changeLevel = false)
    {
        LogManager::warn($message, $changeLevel);
    }
}
if (!function_exists('log_info')) {
    /**
     * $modx->log(modX::LOG_LEVEL_INFO, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     */
    function log_info($message, $changeLevel = false)
    {
        LogManager::info($message, $changeLevel);
    }
}
if (!function_exists('log_debug')) {
    /**
     * $modx->log(modX::LOG_LEVEL_DEBUG, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     */
    function log_debug($message, $changeLevel = false)
    {
        LogManager::debug($message, $changeLevel);
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
     */
    function query($query)
    {
        global $modx;
        return new QueryManager($modx, $query);
    }
}