<?php
namespace EasyTask;

use EasyTask\Exception\ErrorException;

/**
 * Class Helper
 * @package EasyTask
 */
class Helper
{
    /**
     * 是否win平台
     * @return bool
     */
    public static function isWin()
    {
        return (DIRECTORY_SEPARATOR == '\\') ? true : false;
    }

    /**
     * 是否支持异步信号
     * @return bool
     */
    public static function canAsyncSignal()
    {
        return (function_exists('pcntl_async_signals'));
    }

    /**
     * 抛出异常
     * @param string $errStr 错误信息
     * @param int $code 错误码
     * @throws
     */
    public static function exception($errStr, $code = 0)
    {
        throw new ErrorException($errStr, $code);
    }

    /**
     * 获取入口指令
     * @return string
     */
    public static function getEntryCommand()
    {
        //指令集
        $argv = $_SERVER['argv'];

        //入口文件
        $argv['0'] = static::getEntryFile();

        //填充指令
        $command = 'php ' . join(' ', $argv);

        //返回
        return $command;
    }

    /**
     * 获取指令扩展信息
     * @return string
     */
    public static function getCommandExtend()
    {
        //提取输入指令集合
        $argv = $_SERVER['argv'];
        $extend = '';
        foreach ($argv as $item)
        {
            if (strpos($item, 'extend:') !== false)
            {
                $data = explode(':', $item);
                if ($data)
                {
                    $extend = $data['1'];
                }
            }
        }
        return $extend;
    }

    /**
     * 获取入口文件
     * @return string
     */
    public static function getEntryFile()
    {
        //已加载文件
        $files = get_included_files();

        //返回
        return array_shift($files);
    }
}