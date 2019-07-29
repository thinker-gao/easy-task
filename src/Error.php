<?php

namespace EasyTask;

use EasyTask\Exception\Log;
use EasyTask\Exception\ErrorException;

class Error
{
    /**
     * Task实例
     * @var $task
     */
    private static $task;

    /**
     * 注册异常处理
     */
    public static function register($task)
    {
        static::$task = $task;
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

        //记录
        static::record($exception);
    }

    /**
     * appException
     * @param mixed $exception (Exception|Throwable)
     */
    public static function appException($exception)
    {
        //上报
        static::record($exception);
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

    /**
     * 异常信息格式化
     * @param ErrorException $exception
     * @return string
     */
    public static function format($exception)
    {
        //时间
        $date = date('Y-m-d H:i:s', time());

        //组装数据
        $data = [
            'errStr' => $exception->getMessage(),
            'errFile' => $exception->getFile(),
            'errLine' => $exception->getLine()
        ];

        //组装字符串
        $errStr = "[{$date}]" . PHP_EOL . '%s' . PHP_EOL;
        $tempStr = '';
        foreach ($data as $key => $value)
        {
            $tempStr .= "--[{$key}]：{$value}" . PHP_EOL;
        }

        //返回
        return sprintf($errStr, $tempStr);
    }

    /**
     * 异常信息记录
     * @param ErrorException $exception
     */
    private static function record($exception)
    {
        //设置日志目录
        $path = static::$task->logPath;

        //按月设置日志文件
        $date = date('Y-m');
        $file = $path . DIRECTORY_SEPARATOR . 'EasyTask%s.log';
        $file = sprintf($file, $date);

        //获取格式化内容
        $formatLog = static::format($exception);

        //记录日志
        file_put_contents($file, $formatLog);
    }
}