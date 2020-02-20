<?php
namespace EasyTask;

/**
 * Class Env
 * @package EasyTask
 */
class Env
{
    /**
     * 设置
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        $value = is_bool($value) ? (int)$value : $value;
        putenv("$key=$value");
    }

    /**
     * 获取
     * @param $key
     * @return array|false|string
     */
    public static function get($key)
    {
        return getenv($key);
    }
}