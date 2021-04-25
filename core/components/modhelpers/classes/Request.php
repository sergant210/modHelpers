<?php
namespace modHelpers;

use SplFileInfo;
use ArrayAccess;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

class Request extends SymfonyRequest implements ArrayAccess
{
    /** @var  string The decoded JSON content for the request. */
    public $json;
    /** @var array All of the converted files for the request. */
    protected $convertedFiles;
    /** @var  \modResource */
    protected $resource;
    /** @var bool $custom TRUE if the url is custom (No resources for this URL). */
    protected $custom = false;

    /**
     * Set a type of request.
     * @param bool $value
     */
    public function setCustom($value = true)
    {
        $this->custom = (bool) $value;
    }

    public function isCustom()
    {
        return $this->custom;
    }

    /**
     * Create a new HTTP request from server variables.
     *
     * @return static
     */
    public static function capture()
    {
        static::enableHttpMethodParameterOverride();

        return static::createFromBase(SymfonyRequest::createFromGlobals());
    }

    /**
     * Create a request from a Symfony instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return Request
     */
    public static function createFromBase(SymfonyRequest $request)
    {
        if ($request instanceof static) {
            return $request;
        }

        $content = $request->content;

        $request = (new static)->duplicate(
            $request->query->all(), $request->request->all(), $request->attributes->all(),
            $request->cookies->all(), $request->files->all(), $request->server->all()
        );

        $request->content = $content;

        $request->request = $request->getInputSource();

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        return parent::duplicate($query, $request, $attributes, $cookies, $this->filterFiles($files), $server);
    }

    /**
     * Filter the given array of files, removing any empty values.
     *
     * @param  mixed  $files
     * @return mixed
     */
    protected function filterFiles($files)
    {
        if (! $files) {
            return null;
        }

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $files[$key] = $this->filterFiles($files[$key]);
            }

            if (empty($files[$key])) {
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * Retrieve a query string item from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function query($key = null, $default = null)
    {
        return $this->getItem('query', $key, $default);
    }

    /**
     * Determine if a cookie is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCookie($key)
    {
        return !is_null($this->cookie($key));
    }

    /**
     * Retrieve a cookie from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function cookie($key = null, $default = null)
    {
        return $this->getItem('cookies', $key, $default);
    }

    /**
     * Retrieve a server variable from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function server($key = null, $default = null)
    {
        return $this->getItem('server', $key, $default);
    }

    /**
     * Determine if a header is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasHeader($key)
    {
        return ! is_null($this->header($key));
    }

    /**
     * Retrieve a header from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function header($key = null, $default = null)
    {
        return $this->getItem('headers', $key, $default);
    }
    /**
     * Get the request method.
     *
     * @return string
     */
    public function method()
    {
        return $this->getMethod();
    }
    /**
     * Returns the client IP address.
     *
     * @return string
     */
    public function ip()
    {
        return $this->getClientIp();
    }

    /**
     * Determine if the request is over HTTPS.
     *
     * @return bool
     */
    public function secure()
    {
        return $this->isSecure();
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        return str_contains($this->header('CONTENT_TYPE'), ['/json', '+json']);
    }

    /**
     * Determine if the request contains a given input item key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        $input = $this->all();

        foreach ($keys as $value) {
            if (! isset($input[$value])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        $input = $this->input();
        foreach ($keys as $value) {
            if (empty($input[$value])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all()
    {
        $input = $this->input() + $this->query();
        return array_replace_recursive($input, $this->allFiles());
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function input($key = null, $default = null)
    {
        return $this->getItem('request', $key, $default);
    }

    /**
     * Get a subset containing the provided keys with values from the input data.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = array();

        $input = $this->all();

        foreach ($keys as $key) {
            $results[$key] = $input[$key];
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        foreach ($keys as $key) {
            unset($results[$key]);
        }

        return $results;
    }

    /**
     * Intersect an array of items with the input data.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function intersect($keys)
    {
        return array_filter($this->only(is_array($keys) ? $keys : func_get_args()));
    }

    /**
     * Get a resource.
     *
     * @return \modResource|null
     */
    public function resource()
    {
        /** @var \modX $modx */
        $modx = app('modx');
        if (is_null($modx->resource)) {
            $method = $modx->request->getResourceMethod();
            if ($method === 'alias') {
                $resourceIdentifier = $modx->request->getResourceIdentifier($method);
                $resourceIdentifier = $modx->request->_cleanResourceIdentifier($resourceIdentifier);
                $this->resource = $modx->request->getResource($method, $resourceIdentifier);
            }
        } else {
            $this->resource = $modx->resource;
        }

        return $this->resource;
    }

    /**
     * Get the user making the request.
     *
     * @return \modUser|null
     */
    public function user()
    {
        return app('modx')->user;
    }

    /**
     * Merge new input into the current request's input array.
     *
     * @param  array  $input
     * @return Request
     */
    public function merge(array $input)
    {
        $this->getInputSource()->add($input);
        return $this;
    }

    /**
     * Replace the input for the current request.
     *
     * @param  array  $input
     * @return Request
     */
    public function replace(array $input)
    {
        $this->getInputSource()->replace($input);
        return $this;
    }

    /**
     * Filters input data.
     * @param array $rules
     * @param bool $intersect
     * @return Request
     */
    public function filter(array $rules, $intersect = false)
    {
        $filtered = filter_data($this->getInputSource()->all(), $rules, $intersect);
        if ($intersect) {
            $this->replace($filtered);
        } else {
            $this->merge($filtered);
        }
        return $this;
    }
    /**
     * Get the JSON payload for the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (! isset($this->json)) {
            $this->json = new ParameterBag((array) json_decode($this->getContent(), true));
        }

        if (is_null($key)) {
            return $this->json;
        }
        $data = $this->json->all();

        return default_if($data[$key], $default);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->all());
    }

    /**
     * Get the value at the given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $data = $this->all();
        return $data[$offset];
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $data = $this->all();
        $data[$offset] = $value;
    }

    /**
     * Remove the value at the given offset.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $data = $this->getInputSource()->all();
        unset($data[$offset]);
    }

    /**
     * Get the input source for the request.
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }
        return $this->getMethod() === 'GET' ? $this->query : $this->request;
    }

    /**
     * Get an array of all of the files on the request.
     *
     * @return array
     */
    public function allFiles()
    {
        $files = $this->files->all();
        return $this->convertedFiles ?: $this->convertedFiles = $this->convertUploadedFiles($files);
    }

    /**
     * Convert the given array of Symfony UploadedFiles to custom UploadedFiles.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile[]  $files
     * @return array
     */
    protected function convertUploadedFiles(array $files)
    {
        return array_map(function ($file) {
            if (is_null($file) || (is_array($file) && empty(array_filter($file)))) {
                return $file;
            }
            return is_array($file) ? $this->convertUploadedFiles($file) : UploadedFile::createFromBase($file);
        }, $files);
    }

    /**
     * Determine if the uploaded data contains a file.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasFile($key)
    {
        if (! is_array($files = $this->file($key))) {
            $files = [$files];
        }
        foreach ($files as $file) {
            if ($this->isValidFile($file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check that the given file is a valid file instance.
     *
     * @param  mixed  $file
     * @return bool
     */
    protected function isValidFile($file)
    {
        return $file instanceof SplFileInfo && $file->getRealPath() !== '';
    }

    /**
     * Retrieve a file from the request.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return UploadedFile|array|null
     */
    public function file($key, $default = null)
    {
        $data = $this->allFiles();
        return default_if(@$data[$key], $default);
    }

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root()
    {
        return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function fullUrl()
    {
        $query = $this->getQueryString();

        $question = $this->getBaseUrl() . $this->getPathInfo() === '/' ? '/?' : '?';

        return $query ? $this->url() . $question . $query : $this->url();
    }

    /**
     * Get the full URL for the request with the added query string parameters.
     *
     * @param  array  $query
     * @return string
     */
    public function fullUrlWithQuery(array $query)
    {
        $question = $this->getBaseUrl() . $this->getPathInfo() === '/' ? '/?' : '?';

        return count($this->query()) > 0
            ? $this->url() . $question . http_build_query(array_merge($this->query(), $query))
            : $this->fullUrl() . $question . http_build_query($query);
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }

    /**
     * Get the current encoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    /**
     * Get a segment from the URI (1 based index).
     *
     * @param  int  $index
     * @param  string|null  $default
     * @return string|null
     */
    public function segment($index, $default = null)
    {
        $segments = $this->segments();
        return default_if(@$segments[(int)$index - 1], $default);
    }

    /**
     * Get all of the segments for the request path.
     *
     * @return array
     */
    public function segments()
    {
        $segments = explode('/', $this->decodedPath());

        return array_values(array_filter($segments, function ($v) {
            return $v !== '';
        }));
    }

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @return bool
     */
    public function match()
    {
        foreach (func_get_args() as $pattern) {
            if (str_match($this->decodedPath(), $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the host name.
     *
     * @param bool $withSchema
     * @return string
     */
    public function getHost($withSchema = false)
    {
        return ($withSchema ? $this->getScheme().'://' : '') . parent::getHost();
    }

    /**
     * Returns the specified sub domain (1 based index).
     * @param  int $index
     * @param  string $default
     * @return string
     */
    public function subDomain($index, $default = null)
    {
        $subDomains = $this->subDomains();
        return default_if(@$subDomains[(int)$index-1], $default);
    }

    /**
     * Returns the sub domains.
     * @return array
     */
    public function subDomains()
    {
        return array_slice(array_reverse(explode('.', $this->getHost())), 2);
    }

    /**
     * @param string $path
     * @param int|string|\modMediaSource $source
     * @param bool $originalNames
     * @return array
     */
    public function uploadFiles($path = '/', $source = null, $originalNames = false)
    {
        $errors = array();
        /** @var UploadedFile $file */
        foreach ($this->allFiles() as $files) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                if (!is_object($source)) {
                    $source = $file->getSource($source);
                }
                if ($originalNames) {
                    if (!$file->storeAs($path, $file->originalName(), $source)) {
                        $errors[] = $file->originalName();
                    }
                } else {
                    if (!$file->store($path, $source)) {
                        $errors[] = $file->originalName();
                        app('modx')->error->addError($file->originalName());
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * @param string $path
     * @param int|string $source
     * @return array
     */
    public function uploadFilesWithOriginalName($path = '/', $source = null)
    {
        return $this->uploadFiles($path, $source, true);
    }

    /**
     * @return bool
     */
    public function isBot()
    {
        $bots = config('modhelpers_bot_user_agents');
        if (!empty($bots)) {
            $bots = explode_trim(',', $bots);
            $bots = implode('|', $bots);
            $userAgent = empty($this->server('HTTP_USER_AGENT')) ? 'empty' : $this->server('HTTP_USER_AGENT');
            $pattern = "/($bots)/i";
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the CSRF token from the request.
     * @return string|null
     */
    public function getCsrfToken()
    {
        return $this->input('csrf_token') ?: $this->header('X-CSRF-TOKEN');
    }

    /**
     * Checks the CSRF token from the request with the token from the session.
     * @param mixed $methods Methods for which this check will work.
     * @return bool
     */
    public function checkCsrfToken($methods = null)
    {
        if (!empty($methods)) {
            if (!is_array($methods)) {
                $methods = func_get_args();
            }
            $methods = array_map('strtoupper', $methods);
            if (!in_array($this->method(), $methods)) {
                return 0;
            }
        }
        $requestToken = $this->getCsrfToken();

        return  is_string($requestToken) &&
                is_string(csrf_token())  &&
                hash_equals($this->getCsrfToken(), csrf_token());
    }
    /**
     * Gets the session data.
     *
     * @return Session|null The session data
     */
    public function getSession()
    {
        return session();
    }
    /**
     * Get a parameter item from a given source.
     *
     * @param  string  $source
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    protected function getItem($source, $key, $default)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default);
    }
    /**
     * Check if an input element is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return !is_null($this->__get($key));
    }

    /**
     * Get an input element from the request.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return null;
    }

    /**
     * Set an input element.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }
}