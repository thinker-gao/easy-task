<?php
namespace EasyTask;

use EasyTask\Exception\ErrorException;
use \Closure as Closure;

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
        $type = 'error';
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
        $type = 'exception';
        static::report($type, $exception);
    }

    /**
     * appShutdown
     * (Fatal Error|Parse Error)
     * @throws
     */
    public static function appShutdown()
    {
        //存在错误
        $type = 'warring';
        if (($error = error_get_last()) != null)
        {
            //日志记录
            $exception = new ErrorException($error['type'], $error['message'], $error['file'], $error['line']);
            static::report($type, $exception);
        }
    }

    /**
     * Report
     * @param string $type
     * @param ErrorException $exception
     */
    public static function report($type, $exception)
    {
        //标准化日志
        $text = Helper::formatException($exception, $type);

        //本地日志储存
        Helper::writeLog($text);

        //同步模式输出
        if (!Env::get('daemon')) echo($text);

        //回调上报信息
        $notify = Env::get('notifyHand');
        if ($notify)
        {
            //闭包回调
            if ($notify instanceof Closure)
            {
                $notify($exception);
                return;
            }

            //Http回调
            $request = [
                'errStr' => $exception->getMessage(),
                'errFile' => $exception->getFile(),
                'errLine' => $exception->getLine(),
            ];
            $result = Helper::curl($notify, $request);
            if (!$result || $result != 'success')
            {
                Helper::showError("request http api $notify failed", false, 'warring', true);
            }
        }
    }
}