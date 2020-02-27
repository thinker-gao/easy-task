<?php
namespace EasyTask;

use EasyTask\Exception\ErrorException;

/**
 * Class Error
 * @package EasyTask
 */
class Error
{
    /**
     * Register Error
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
     * @param string $errno
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     * @throws
     */
    public static function appError($errno, $errStr, $errFile, $errLine)
    {
        //组装异常
        $type = 'appError';
        $exception = new ErrorException($errno, $errStr, $errFile, $errLine);

        //日志记录
        static::report($type, $exception);
    }

    /**
     * appException
     * @param mixed $exception (Exception|Throwable)
     * @throws
     */
    public static function appException($exception)
    {
        //日志记录
        $type = 'appException';
        static::report($type, $exception);

        //控制台抛出异常
        $isThrowExcept = Env::get('isThrowExcept');
        if ($isThrowExcept) Helper::showException($exception);
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
            //日志记录
            $exception = new ErrorException($error['type'], $error['message'], $error['file'], $error['line']);
            static::report($type, $exception);

            //控制台抛出异常
            $isThrowExcept = Env::get('isThrowExcept');
            if ($isThrowExcept) Helper::showException($exception);
        }
    }

    /**
     * Report
     * @param $type
     * @param $exception
     */
    private static function report($type, $exception)
    {
        $text = Helper::formatException($exception, $type);
        Log::write($text);
        if (Env::get('daemon'))
        {
            Helper::showError($text, $type, false);
        }
    }
}