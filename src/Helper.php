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
     * 抛出异常(统一格式todo())
     * @param string $errStr 错误信息
     * @param int $code 错误码
     * @throws
     */
    public static function exception($errStr, $code = 0)
    {
        throw new ErrorException($errStr, $code);
    }
}