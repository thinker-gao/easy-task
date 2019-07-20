<?php

namespace EasyTask;

use EasyTask\Exception\Log;
use EasyTask\Exception\ErrorException;

class Error
{
    /**
     * 注册异常处理
     */
    public static function register()
    {
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
        $exception = new ErrorException($errno, $errStr, $errFile, $errLine);

        //上报
        Log::report($exception);
    }

    /**
     * appException
     * @param mixed $exception (Exception|Throwable)
     */
    public static function appException($exception)
    {
        //上报
        Log::report($exception);
    }

    /**
     * appShutdown
     * (Fatal Error|Parse Error)
     */
    public static function appShutdown()
    {
        //存在错误
        if (($error = error_get_last()) != null)
        {
            $exception = new ErrorException($error['type'], $error['message'], $error['file'], $error['line']);
            self::appException($exception);
        }
    }
}