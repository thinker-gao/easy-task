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
            'curl',
            'com_dotnet',
            'mbstring',
        ],
        //Linux
        '2' => [
            'json',
            'curl',
            'pcntl',
            'posix',
            'mbstring',
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
            'sleep',
            'usleep',
            'ob_start',
            'ob_end_clean',
            'ob_get_contents',
        ],
        //Linux
        '2' => [
            'umask',
            'chdir',
            'sleep',
            'usleep',
            'ob_start',
            'ob_end_clean',
            'ob_get_contents',
            'pcntl_fork',
            'posix_setsid',
            'posix_getpid',
            'posix_getppid',
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
                Helper::showSysError("php_{$extend}.(dll/so) is not load,please check php.ini file");
            }
        }
        //检查函数
        $waitFunctions = static::$waitFunctions[$currentOs];
        foreach ($waitFunctions as $func)
        {
            if (!function_exists($func))
            {
                Helper::showSysError("function $func may be disabled,please check disable_functions in php.ini");
            }
        }
    }
}

