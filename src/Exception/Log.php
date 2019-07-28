<?php
namespace EasyTask\Exception;

/**
 * 日志类
 */
class Log
{
    /**
     * 格式化异常
     * @param  $exception
     * @return string
     */
    private static function format($exception)
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
     * 上报
     */
    public static function report($exception)
    {
        $os = (DIRECTORY_SEPARATOR == '\\') ? 1 : 2;
        $path = $os == 1 ? 'C:/Windows/Temp' : '/tmp';

        $file = $path . DIRECTORY_SEPARATOR . 'easytask.log';

        $str = static::format($exception);

        file_put_contents($file, $str);
    }
}