<?php
namespace EasyTask;

/**
 * Class Env
 * @package EasyTask
 */
class Env
{

    /**
     * collection
     * @var array
     */
    private static $collection;

    /**
     * Set
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        static::$collection[$key] = $value;
    }

    /**
     * Get
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return isset(static::$collection[$key]) ? static::$collection[$key] : false;
    }
}