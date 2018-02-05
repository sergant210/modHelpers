<?php

namespace modHelpers;

trait ResponseTrait
{
    /**
     * The original content of the response.
     *
     * @var mixed
     */
    public $original;


    /**
     * @param  mixed  $content
     * @return bool
     */
    protected function isJsonable($content)
    {
        return method_exists($content, 'toJson');
    }
    /**
     * @param  mixed  $content
     * @return bool
     */
    protected function isArrayable($content)
    {
        return method_exists($content, 'toArray');
    }

    /**
     * Get the status code for the response.
     *
     * @return int
     */
    public function status()
    {
        return $this->getStatusCode();
    }

    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function content()
    {
        return $this->getContent();
    }

    /**
     * Get the original response content.
     *
     * @return mixed
     */
    public function originalContent()
    {
        return $this->original;
    }

    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  array|string  $values
     * @param  bool    $replace
     * @return $this
     */
    public function header($key, $values, $replace = true)
    {
        $this->headers->set($key, $values, $replace);

        return $this;
    }

    /**
     * Add an array of headers to the response.
     *
     * @param  array  $headers
     * @return $this
     */
    public function headers(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @param  \Symfony\Component\HttpFoundation\Cookie|mixed  $cookie
     * @return $this
     */
    public function cookie($cookie)
    {
        if (is_string($cookie)) {
            list($name, $value, $expire, $path, $domain, $secure, $httpOnly) = func_get_args();
            $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        }
        if ($cookie instanceof Cookie) $this->headers->setCookie($cookie);

        return $this;
    }
}