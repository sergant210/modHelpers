<?php
//require_once __DIR__ . '/classes.php';

/***********************************************/
/*              Functions                      */
/***********************************************/
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
     * @return array|null|string
     */
    function config($key = '', $default = '')
    {
        global $modx;
        if (!empty($key)) {
            if (is_array($key)) {
                if (!can('settings')) return false;
                foreach ($key as $itemKey => $itemValue) {
                    $modx->config[$itemKey] = $itemValue;
                }
                return true;
            }
            return isset($modx->config[$key]) ? $modx->config[$key] : $default;
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
     * @param null|int|string|array $options
     * @return mixed|modCacheManager
     */
    function cache($key = '', $options = NULL)
    {
        global $modx;
        if (func_num_args() == 0) {
            return new modHelpersCacheManager($modx->getCacheManager());
        }
        if (is_string($options)) {
            $options = array(xPDO::OPT_CACHE_KEY => $options);
        } elseif (is_numeric($options)) {
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
     * Send Email.
     * @param string|array $email Email.
     * @param string|array $subject Subject or an array of options. Required option keys - subject, content. Optional - sender, from, fromName.
     * @param string $content
     * @return bool|modHelpersMailer
     */
    function email($email='', $subject='', $content = '')
    {
        global $modx;
        if (func_num_args() == 0) return new modHelpersMailer($modx);
        if (is_array($subject)) {
            $options = $subject;
        } else {
            $options = compact('subject','content');
        }
        if (empty($email)) return false;
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
        if (!empty($options['cc'])) $mail->address('cc', $options['cc']);
        if (!empty($options['bcc'])) $mail->address('bcc', $options['bcc']);
        if (!empty($options['replyTo'])) $mail->address('reply-to', $options['replyTo']);
        if (!empty($options['attach'])) {
            if (is_array($options['attach'])) {
                foreach ($options['attach'] as $name => $file) {
                    if (!is_string($name)) $name = '';
                    $mail->attach($file, $name);
                }
            } else {
                $mail->attach($options['attach']);
            }
        }

        if (is_array($email)) {
            foreach ($email as $e) {
                $mail->address('to', $e);
            }
        } else {
            $mail->address('to', $email);
        }
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
        $output = '';
        //$store = isset($modx->getCacheManager()->store) ? $modx->getCacheManager()->store : array('modChunk'=>array());
        if (strpos($chunkName, '/') !== false && file_exists($chunkName)) {
            $content = file_get_contents($chunkName);
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
     * Runs the specified MODX or file snippet.
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
        if (strpos($snippetName, '/') !== false && file_exists($snippetName)) {
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
if (!function_exists('object')) {
    /**
     * Gets an object of the specified class.
     * @param string $class
     * @param integer|array $criteria
     * @return modHelpersObjectManager
     */
    function object($class, $criteria = null)
    {
        global $modx;
        $object = new modHelpersObjectManager($modx, $class);
        if (isset($criteria)) {
            if (is_numeric($criteria)) {
                $pk = $modx->getPK($class);
                $where = array($pk => (int) $criteria);
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
     * @return modHelpersCollectionManager
     */
    function collection($class = '', $criteria = null)
    {
        global $modx;
        $collection = new modHelpersCollectionManager($modx, $class);
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
     * @return array|modResource|bool|modHelpersObjectManager
     */
    function resource($criteria = null, $asObject = true)
    {
        /** @var modHelpersObjectManager $resourceManager */
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
     * Gets a collection of the resources.
     * @param array $criteria Criteria
     * @param bool $asObject True to return an array of the objects. Otherwise - an array of resources data arrays.
     * @return array|bool|modHelpersCollectionManager
     */
    function resources($criteria = null, $asObject = false)
    {
        global $modx;
        /** @var modHelpersCollectionManager $collection */
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
     * @param int|string|array $criteria User id, username or array.
     * @param bool $asObject True to return an object. Otherwise - an array.
     * @return array|modUser
     */
    function user($criteria = null, $asObject = true)
    {
        /** @var modHelpersObjectManager $userManager */
        if (is_numeric($criteria)) {
            $criteria = array('id' => (int) $criteria);
        } elseif (is_string($criteria)) {
            $criteria = array('username' => $criteria);
        }
        $userManager = object('modUser', $criteria);

        return (isset($criteria) && $asObject) ? $userManager->get() : $userManager->toArray();
    }
}
if (!function_exists('users')) {
    /**
     * @param array $criteria
     * @param bool $asObject True to return an array of the user objects. Otherwise - an array of users data arrays.
     * @return array|modHelpersCollectionManager
     */
    function users($criteria = null, $asObject = false)
    {
        global $modx;
        /** @var modHelpersCollectionManager $collection */
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
if (!function_exists('res_id')) {
    /**
     * Gets id of the current resource.
     * @return int
     */
    function res_id()
    {
        return resource_id();
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
     * @param string $target
     */
    function log_error($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        global $modx;
        modHelpersLogger::setModx($modx);
        modHelpersLogger::error($message, $changeLevel, $target, $def, $file, $line);
    }
}
if (!function_exists('log_warn')) {
    /**
     * $modx->log(modX::LOG_LEVEL_WARN, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     * @param string $target
     */
    function log_warn($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        global $modx;
        modHelpersLogger::setModx($modx);
        modHelpersLogger::warn($message, $changeLevel, $target, $def, $file, $line);
    }
}
if (!function_exists('log_info')) {
    /**
     * $modx->log(modX::LOG_LEVEL_INFO, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     * @param string $target
     */
    function log_info($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        global $modx;
        modHelpersLogger::setModx($modx);
        modHelpersLogger::info($message, $changeLevel, $target, $def, $file, $line);
    }
}
if (!function_exists('log_debug')) {
    /**
     * $modx->log(modX::LOG_LEVEL_DEBUG, $message)
     *
     * @param string $message
     * @param bool $changeLevel Change log level
     * @param string $target
     */
    function log_debug($message, $changeLevel = false, $target = '', $def = '', $file = '', $line = '')
    {
        global $modx;
        modHelpersLogger::setModx($modx);
        modHelpersLogger::debug($message, $changeLevel, $target, $def, $file, $line);
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
     * @return modHelpersQueryManager
     */
    function query($query)
    {
        global $modx;
        return new modHelpersQueryManager($modx, $query);
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
    $Faker = false;
    /**
     * Makes fake data
     * @see https://github.com/fzaninotto/Faker
     * @param string|array $property
     * @param string $locale
     * @return mixed
     */
    function faker($property = '', $locale = '')
    {
        global $Faker;
        if (!$Faker) {
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

            $Faker = \Faker\Factory::create($locale);
        }
        if (func_num_args() == 0) return $Faker;

        try {
            if (is_array($property)) {
                $func = key($property);
                $params = current($property);
                $output = call_user_func_array(array($Faker, $func), $params);
            } else {
                $output = $Faker->$property;
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
        $model = new modHelpersModelBuilder($table);
        if (is_callable($callback)) {
            $callback($model);
            $map = $model->output();
            if (!empty($map)) {
                if (!empty($table)) {
                    $modx->map[$class] = $map;
                } else {
                    $modx->map[$class] = array_merge_recursive($modx->map[$class], $map);
                }
                if (config('modHelpers_cache_model', true)) cache()->set($key,$map);
                return true;
            }
        }
        return false;
    }
}
if (!function_exists('login')) {
    /**
     * Logs in the specified user.
     * @param int|modUser $user
     * @return bool
     */
    function login($user)
    {
        global $modx;
        if (is_scalar($user) || is_array($user)) $user = user($user);
        if ($user instanceof modUser) {
            $modx->user = $user;
            $modx->user->addSessionContext($modx->context->key);
            return true;
        }
        return false;
    }
}
if (!function_exists('logout')) {
    /**
     * Logs out the current user.
     * @param bool $redirect True to redirect to the unauthorized page.
     * @param int $code Response code
     * @return bool
     */
    function logout($redirect = false, $code = 401)
    {
        global $modx;
        $response = $modx->runProcessor('security/logout');
        if ($response->isError()) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Logout error of the user: '.$modx->user->get('username').' ('.$modx->user->get('id').').');
            return false;
        }
        $modx->user = $modx->getAuthenticatedUser('mgr');
        if (!is_object($modx->user) || !$modx->user instanceof modUser) {
            if ($redirect) abort($code);
            $modx->user = $modx->newObject('modUser');
            $modx->user->fromArray(array(
                'id' => 0,
                'username' => $modx->getOption('default_username', '', '(anonymous)', true)
            ), '', true);
            $modx->toPlaceholders($modx->user->get(array('id','username')),'modx.user');
        }
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
if (!function_exists('is_mobile')) {
    /**
     * Mobile detector.
     * @return bool
     * @see http://detectmobilebrowsers.com/
     */
    function is_mobile()
    {
        $is_mobile = false;
        $useragent=$_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
            $is_mobile = true;
        }
        return $is_mobile;
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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return string
     */
    function explode_ltrim($delimiter, $string, $chars = '')
    {
        $array = explode($delimiter, $string);
        return array_trim($array, $chars, 'ltrim');
    }
}
if (!function_exists('echo_nl')) {
    /**
     * Convert the specified variable to the string type and print or return it.
     *
     * @param mixed $string
     * @param string $nl.
     * @return void
     */
    function echo_nl($string, $nl = PHP_EOL)
    {
        echo $string . $nl;
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
            $template = config('modhelpers.print_template');
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
     * @param string $string Text to parse.
     * @param array $data An array of placeholders to replace.
     * @param string $prefix The placeholder prefix, defaults to [[+.
     * @param string $suffix The placeholder suffix, defaults to ]].
     * @return string The processed string with the placeholders replaced.
     */
    function parse($string, $data, $prefix = '[[+', $suffix = ']]')
    {
        if (!empty($string) || $string === '0') {
            if (is_array($data)) {
                reset($data);
                while (list($key, $value) = each($data)) {
                    $string = str_replace($prefix . $key . $suffix, $value, $string);
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
     * (Taken from Laravel helpers - str_is() )
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
/*if (!function_exists('queue')) {
    function queue()
    {
        return new modHelpersQueue;
    }
}*/