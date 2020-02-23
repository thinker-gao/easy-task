<?php

namespace EasyTask;

/**
 * Class Log
 * @package EasyTask
 */
class Log
{
    /**
     * 追加日志
     * @param string $message
     */
    public static function write($message)
    {
        $file = static::getWriteFile();
        @file_put_contents($file, $file, LOCK_EX);
    }

    /**
     * 获取日志文件
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
        if (!is_dir($path))
        {
            mkdir($path);
        }

        //设置日志文件
        return $path . date('Y_m_d_log') . '.txt';
    }
}