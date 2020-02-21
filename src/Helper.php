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
     * 二维数组转字典
     * @param array $list
     * @param string $key
     * @return array
     */
    public static function array_dict($list, $key)
    {
        $dict = [];
        foreach ($list as $v)
        {
            if (!isset($v[$key]))
            {
                continue;
            }
            $dict[$v[$key]] = $v;
        }

        return $dict;
    }

    public static function getFullArgv()
    {
        //输入参数
        $argv = $_SERVER['argv'];

        //组装PHP路径
        array_unshift($argv, Env::get('phpPath'));

        //返回
        return join(' ', $argv);
    }

    /**
     * 是否win平台
     * @return bool
     */
    public static function isWin()
    {
        return (DIRECTORY_SEPARATOR == '\\') ? true : false;
    }

    /**
     * 获取平台临时目录
     * @return string
     */
    public static function getOsTempPath()
    {
        return Helper::isWin() ? 'C:/Windows/Temp/' : '/tmp/';
    }

    /**
     * 获取运行时目录
     * @return  string
     */
    public static function getRunTimePath()
    {
        $path = Env::get('writeLogPath');
        if (!$path)
        {
            $path = Helper::getOsTempPath();
        }
        return $path . DIRECTORY_SEPARATOR . Env::get('prefix') . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取win32进程锁目录
     * @return  string
     */
    public static function getWin32LockPath()
    {
        return Helper::getRunTimePath() . 'win32_lock' . DIRECTORY_SEPARATOR;
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
        $a = $date . ' [' . $type . '] : errStr:' . $exception->getMessage() . ',errFile:' . $exception->getFile() . ',errLine:' . $exception->getLine() . PHP_EOL . PHP_EOL;
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
     * 输出异常
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