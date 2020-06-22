<?php
namespace EasyTask;

/**
 * Class Queue
 * @package EasyTask
 */
class Queue
{
    /**
     * 进程锁
     * @var Lock
     */
    private $lock;

    /**
     * 队列文件
     */
    private $queFile;

    /**
     * 构造函数
     * @throws
     */
    public function __construct()
    {
        $this->lock = new Lock();
        $this->initQueFile();
    }

    /**
     * 初始化文件
     */
    private function initQueFile()
    {
        //创建文件
        $path = Helper::getQuePath();
        $file = $path . '%s.dat';
        $this->queFile = sprintf($file, md5(__FILE__));
        if (!file_exists($this->queFile))
        {
            if (!file_put_contents($this->queFile, '[]', LOCK_EX))
            {
                Helper::showError('crate queFile failed,please try again');
            }
        }
    }
}
