<?php

if (!function_exists('isHttps'))
{
    /**
     * 是否Https
     * @return bool
     */
    function isHttps()
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return true;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        {
            return true;
        }
        elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return true;
        }
        return false;
    }


}

if (!function_exists('getServername'))
{
    /**
     * 获取网站域名(包含协议头)
     */
    function getServername()
    {
        $serverName = $_SERVER['SERVER_NAME'];
        $serverName = isHttps() ? 'https://' . $serverName : 'http://' . $serverName;
        return $serverName;
    }
}
