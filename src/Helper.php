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
     * 格式化异常信息
     * @param ErrorException $exception
     * @param string $type
     * @return string
     */
    public static function formatException($exception, $type = 'system')
    {
        //时间
        $date = date('Y/m/d H:i:s', time());

        //组装文本
        return $date . ' [' . $type . '] : errStr:' . $exception->getMessage() . ',errFile:' . $exception->getFile() . ',errLine:' . $exception->getLine() . PHP_EOL;
    }

    /**
     * 格式化异常信息
     * @param string $message
     * @param string $type
     * @return string
     */
    public static function formatError($message, $type = 'system')
    {
        //时间
        $date = date('Y/m/d H:i:s', time());

        //组装文本
        return $date . ' [' . $type . '] : ' . $message . PHP_EOL;
    }

    /**
     * 输出错误
     * @param string $errStr 错误信息
     * @param string $type
     * @param bool $isExit
     * @throws
     */
    public static function showError($errStr, $type = 'warring', $isExit = true)
    {
        //格式化信息
        $text = static::formatError($errStr, $type);

        //输出信息
        if ($isExit)
        {
            exit($text);
        }
        echo $text;
    }

    /**
     * 输出错误
     * @param mixed $exception 异常信息
     * @param string $type
     * @param bool $isExit
     * @throws
     */
    public static function showException($exception, $type = 'warring', $isExit = true)
    {
        //格式化信息
        $text = static::formatException($exception, $type);

        //输出信息
        if ($isExit)
        {
            exit($text);
        }
        echo $text;
    }
}