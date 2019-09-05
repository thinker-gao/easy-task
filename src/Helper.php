<?php

namespace EasyTask;

use EasyTask\Exception\ErrorException;
use ReflectionClass as ReflectionClass;
use ReflectionException as ReflectionException;
use ReflectionMethod as ReflectionMethod;

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
     * 获取唯一Key
     */
    public static function getTaskUniKey($alas)
    {
        return md5($alas);
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
     * @throws
     */
    public static function exception($errStr, $code = 0)
    {
        throw new ErrorException($errStr, $code);
    }

    /**
     * 检查类和方法
     * @param $class
     * @param $func
     * @throws
     */
    public static function chkClassFunc($class, $func)
    {
        if (!class_exists($class))
        {
            throw new \Exception("{$class}类不存在");
        }
        try
        {
            $reflect = new ReflectionClass($class);
            if (!$reflect->hasMethod($func))
            {
                throw new \Exception("{$class}类的方法{$func}不存在");
            }

            $method = new ReflectionMethod($class, $func);
            if (!$method->isPublic())
            {
                throw new \Exception("{$class}类的方法{$func}必须是可访问的");
            }
        }
        catch (ReflectionException $exception)
        {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 通过argv获取输入指令
     */
    public static function getCommandByArgv()
    {
        //提取输入指令集合
        $argv = $_SERVER['argv'];
        if (!$argv)
        {
            return '';
        }

        //将指令中脚本的真实地址
        $argv['0'] = static::getEntryFile();
        $command = 'php';
        foreach ($argv as $value)
        {
            $command .= " $value";
        }
        return $command;
    }

    /**
     * 获取指令扩展信息
     */
    public static function getCommandExtend()
    {
        //提取输入指令集合
        $argv = $_SERVER['argv'];
        if (!$argv)
        {
            return '';
        }

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
     */
    public static function getEntryFile()
    {
        $includes = get_included_files();
        if (!$includes)
        {
            return '';
        }

        return array_shift($includes);
    }
}