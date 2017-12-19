<?php

namespace modHelpers;

use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class Cookie extends SymfonyCookie
{

    public function __construct($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = null, $httpOnly = null, $raw = false, $sameSite = null)
    {
        $path = $path ?: config('session_cookie_path', MODX_BASE_URL);
        $domain = $domain ?: config('session_cookie_domain', null);
        $secure = $secure ?: config('session_cookie_secure', false);
        $httpOnly = $httpOnly ?: config('session_cookie_httponly', true);
        $expire = ($expire == 0) ? 0 : time() + ($expire * 60);

    	parent::__construct($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

}