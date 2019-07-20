<?php
namespace EasyTask\Exception;

/**
 * 日志类
 */
class Log
{
    /**
     * 简单记录日志
     */
    public static function report($exception)
    {

        $os = (DIRECTORY_SEPARATOR == '\\') ? 1 : 2;
        $path = $os == 1 ? 'C:/Windows/Temp' : '/tmp';

        $file = $path . DIRECTORY_SEPARATOR . 'easytask.log';

        //组装基本数据
        $errInfo = [
            'errstr' => $exception->getMessage(),
            'errfile' => $exception->getFile(),
            'errline' => $exception->getLine(),
            'errtrace' => $exception->getTrace(),
        ];

        error_log(json_encode($errInfo), 3, $file);
    }
}