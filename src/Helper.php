<?php
namespace EasyTask;

use EasyTask\Exception\ErrorException;

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
     * 获取日志文件名格式
     * @param string $prefix 前缀名称
     * @return string
     */
    public static function getFormatLogFilePath($prefix)
    {
        $file = Helper::isWin() ? 'C:/Windows/Temp/%s.txt' : '/tmp/%s.txt';
        $format = $prefix . '_log_' . date('Ymd');
        return sprintf($file, $format);
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
     * 是否支持event事件
     * @return bool
     */
    public static function canEvent()
    {
        return (extension_loaded('event'));
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
        return $date . ' [' . $type . '] : errStr:' . $exception->getMessage() . ',errFile:' . $exception->getFile() . ',errLine:' . $exception->getLine() . PHP_EOL . PHP_EOL;
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

    /**
     * 控制台输出表格
     * @param array $data 输出数据
     * @param boolean $exit 输出后是否退出
     */
    public static function showTable($data, $exit = true)
    {
        //提取表头
        $header = array_keys($data['0']);

        //组装数据
        foreach ($data as $key => $row)
        {
            $data[$key] = array_values($row);
        }

        //输出表格
        $table = new Table();
        $table->setHeader($header);
        $table->setStyle('box');
        $table->setRows($data);
        if ($exit)
        {
            exit($table->render());
        }
        echo($table->render());
    }
}