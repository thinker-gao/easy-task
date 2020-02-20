<?php

namespace EasyTask;


/**
 * Class Log
 * @package EasyTask
 */
class Log
{

    public  function wi()
    {

    }

    /**
     * 记录日志
     * @param string $type
     * @param  $exception
     * @throws
     */
    public static function write($type, $exception)
    {
        return '';

        //格式化信息
        $log = Helper::formatException($exception, $type);
        die();

        //获取日志文件
        $file = static::getWriteFile();

        //写入日志文件
        $fp = fopen($file, 'a+');

        //加锁
        if (flock($fp, LOCK_EX))
        {
            fwrite($fp, $log);
            flock($fp, LOCK_UN);
        }

        //关闭文件
        fclose($fp);
    }

    /**
     * 获取日志写入目录
     * @return string
     */
    private static function getWriteFile()
    {
        //设置根目录
        $setPath = Env::get('writeLogPath');
        if (!$setPath)
        {
            $setPath = Helper::getOsTempPath();
        }
        if (!is_writable($setPath))
        {
            Helper::showError("the log path {$setPath} is not writeable");
        }

        //设置子目录
        $prefix = Env::get('prefix');
        $path = $setPath . DIRECTORY_SEPARATOR . $prefix . DIRECTORY_SEPARATOR;
        if (is_dir($path))
        {
            mkdir($path);
        }

        //设置日志文件
        return $path . date('Y_m_d_H_log') . '.txt';
    }
}