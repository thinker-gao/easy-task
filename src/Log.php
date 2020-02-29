<?php
namespace EasyTask;

/**
 * Class Log
 * @package EasyTask
 */
class Log
{
    /**
     * Write
     * @param string $message
     */
    public static function write($message)
    {
        //根目录
        $path = Helper::getLogPath();
        if (!is_dir($path))
        {
            mkdir($path, 0777, true);
        }

        //保存文件
        $file = $path . date('Y_m_d_H') . '.txt';

        //加锁保存
        file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
    }
}