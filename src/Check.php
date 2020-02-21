<?php
namespace EasyTask;

class Check
{
    /**
     * 待检查扩展列表
     */
    private static $waitExtends = [
        //Win
        '1' => [
            'json',
            //'pthreads',
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
            'popen',
            'pclose',
            'umask',
            'putenv',
            'getenv'
        ],
        //Linux
        '2' => [
            'umask',
            'chdir',
            'popen',
            'pclose',
            'putenv',
            'getenv'
        ]
    ];

    /**
     *  分析环境是否支持
     * @param int $currentOs 输出数据
     */
    public static function analysis($currentOs)
    {
        //检查扩展
        $waitExtends = static::$waitExtends[$currentOs];
        foreach ($waitExtends as $extend)
        {
            if (!extension_loaded($extend))
            {
                Helper::showError("$extend extend is not load");
            }
        }
        //检查函数
        $waitFunctions = static::$waitFunctions[$currentOs];
        foreach ($waitFunctions as $func)
        {
            if (!function_exists($func))
            {
                Helper::showError("$func function is disabled");
            }
        }

        //Windows特殊检查
        if ($currentOs == 1 && !Env::get('phpPath'))
        {
            static::checkOtherForWin();
        }
    }

    /**
     * Windows特殊检查
     */
    private static function checkOtherForWin()
    {
        //提取环境变量
        $paths = $_SERVER['Path'];
        if (!$paths)
        {
            Helper::showError("get php env path failed");
        }

        //循环检查
        $isSet = false;
        $paths = explode(';', $paths);
        foreach ($paths as $path)
        {
            $file = $path . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'php.exe';
            if (file_exists($file))
            {
                $isSet = true;
                Env::set('phpPath', realpath($file));
                break;
            }
        }

        //提示检查或者手动设置变量
        if (!$isSet) Helper::showError('get your php environment variable failed or you can set it by setPhpPath api');
    }
}

