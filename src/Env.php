<?php
namespace EasyTask;

/**
 * Class Env
 * @package EasyTask
 */
class Env
{
    /**
     * Set
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        $value = is_bool($value) ? (int)$value : $value;
        putenv("$key=$value");
    }

    /**
     * Get
     * @param string $key
     * @return array|false|string
     */
    public static function get($key)
    {
        return getenv($key);
    }
}