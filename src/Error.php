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

        //控制台抛出异常
        if ((static::$taskInstance)->isThrowExcept) throw $exception;
    }

    /**
     * appShutdown
     * (Fatal Error|Parse Error)
     * @throws
     */
    public static function appShutdown()
    {
        //存在错误
        $type = 'appShutdown';
        if (($error = error_get_last()) != null)
        {
            $exception = new ErrorException($error['type'], $error['message'], $error['file'], $error['line']);
            static::writeRecord($type, $exception);

            //控制台抛出异常
            if ((static::$taskInstance)->isThrowExcept) throw $exception;
        }
    }

    /**
     * 记录异常
     * @param string $type
     * @param ErrorException $exception
     * @throws
     */
    private static function writeRecord($type, $exception)
    {
        //格式化信息
        $log = Helper::formatException($exception, $type);

        //设置日志文件
        $file = Helper::getFormatLogFilePath(static::$taskInstance->prefix);

        //记录信息
        file_put_contents($file, $log, FILE_APPEND);
    }

    /***
     *  输出异常
     * @param string $type
     * @param ErrorException $exception
     */
    public static function showError($type, $exception)
    {
        //格式化信息
        $text = Helper::formatException($exception, $type);

        //输出信息
        echo $text;
    }
}