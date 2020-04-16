<?php
namespace EasyTask;

/**
 * Class Check
 * @package EasyTask
 */
class Check
{
    /**
     * 待检查扩展列表
     * @var array
     */
    private static $waitExtends = [
        //Win
        '1' => [
            'json',
            'com_dotnet',
            'mbstring',
        ],
        //Linux
        '2' => [
            'json',
            'pcntl',
            'posix',
        ]
    ];

    /**
     * 待检查函数列表
     * @var array
     */
    private static $waitFunctions = [
        //Win
        '1' => [
            'umask',
            'putenv',
            'getenv',
        ],
        //Linux
        '2' => [
            'umask',
            'chdir',
            'putenv',
            'getenv',
            'pcntl_fork',
            'posix_setsid',
            'posix_getpid',
            'pcntl_wait',
            'posix_kill',
            'pcntl_signal',
            'pcntl_alarm',
            'pcntl_waitpid',
            'pcntl_signal_dispatch',
        ]
    ];

    /**
     *  解析运行环境
     * @param int $currentOs
     */
    public static function analysis($currentOs)
    {
        //检查扩展
        $waitExtends = static::$waitExtends[$currentOs];
        foreach ($waitExtends as $extend)
        {
            if (!extension_loaded($extend))
            {
                Helper::showError("$extend extend is not load,please install this extend by dll file or so file");
            }
        }
        //检查函数
        $waitFunctions = static::$waitFunctions[$currentOs];
        foreach ($waitFunctions as $func)
        {
            if (!function_exists($func))
            {
                Helper::showError("function $func may be disabled,please check disable_functions in php.ini");
            }
        }
    }
}

