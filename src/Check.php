<?php
namespace EasyTask;

/**
 * 环境检查
 * @package EasyTask
 */
class Check
{
    /**
     * 待检查扩展列表
     */
    private static $waitExtends = [
        //Win
        '1' => [],
        //Linux
        '2' => []
    ];

    /**
     * 待检查函数列表
     * @var array
     */
    private static $waitFunctions = [
        //Win
        '1' => [],
        //Linux
        '2' => []
    ];

    /**
     *  分析环境是否支持
     * @param int $currentOs 输出数据
     * @throws
     */
    public static function analysis($currentOs)
    {
        //检查扩展
        $waitExtends = static::$waitExtends[$currentOs];
        foreach ($waitExtends as $extend)
        {
            if (!extension_loaded($extend))
            {
                Helper::exception("$extend extend is not loaded");
            }
        }
        //检查函数
        $waitFunctions = static::$waitFunctions[$currentOs];
        foreach ($waitFunctions as $func)
        {
            if (!function_exists($func))
            {
                Helper::exception("$func function is not exists");
            }
        }
    }
}

