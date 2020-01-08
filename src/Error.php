<?php

namespace EasyTask;

use EasyTask\Exception\ErrorException;

class Error
{
    /**
     * Task实例
     * @var Task
     */
    private static $taskInstance;

    /**
     * 注册异常处理
     * @param Task $taskInstance
     */
    public static function register($taskInstance)
    {
        static::$taskInstance = $taskInstance;
        error_reporting(E_ALL);
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    /**
     * appError
     * (E_ERROR|E_PARSE|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING|E_STRICT)
     * @param $errno
     * @param $errStr
     * @param $errFile
     * @param $errLine
     * @throws
     */
    public static function appError($errno, $errStr, $errFile, $errLine)
    {
        //组装异常
        $type = 'appError';
        $exception = new ErrorException($errno, $errStr, $errFile, $errLine);

        //上报异常
        static::writeRecord($type, $exception);

        //控制台输出
        if ((static::$taskInstance)->throwException)
        {
            static::showError($type, $exception);
        }
    }

    /**
     * appException
     * @param mixed $exception (Exception|Throwable)
     * @throws
     */
    public static function appException($exception)
    {
        //上报异常
        $type = 'appException';
        static::writeRecord($type, $exception);

        //控制台抛出(根据需要,异常开发者必须处理)
        if ((static::$taskInstance)->throwException)
        {
            throw $exception;
        }
    }

    /**
     * appShutdown
     * (Fatal Error|Parse Error)
     */
    public static function appShutdown()
    {
        //存在错误
        $type = 'appShutdown';
        if (($error = error_get_last()) != null)
        {
            $exception = new ErrorException($error['type'], $error['message'], $error['file'], $error['line']);
            static::writeRecord($type, $exception);
        }
    }

    /**
     * 异常信息格式化
     * @param string $type
     * @param ErrorException $exception
     * @return string
     */
    public static function format($type, $exception)
    {
        //时间
        $date = date('Y/m/d H:i:s', time());

        //组装文本
        return $date . ' [' . $type . '] : errStr:' . $exception->getMessage() . ',errFile:' . $exception->getFile() . ',errLine:' . $exception->getLine() . PHP_EOL;
    }

    /**
     * 记录异常
     * @param string $type
     * @param ErrorException $exception
     */
    private static function writeRecord($type, $exception)
    {
        //格式化信息
        $log = static::format($type, $exception);

        //记录信息
        file_put_contents('/tmp/log.txt', $log);
    }

    /***
     *  输出异常
     * @param string $type
     * @param ErrorException $exception
     */
    public static function showError($type, $exception)
    {
        //格式化信息
        $text = static::format($type, $exception);

        //输出信息
        echo $text;
    }
}