<?php
namespace EasyTask;

/**
 * Class Lock
 * @package EasyTask
 */
class Lock
{
    /**
     * 锁文件
     * @var string
     */
    private $file;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $runPath = Helper::getRunTimePath();
        if (!is_dir($runPath))
        {
            @mkdir($runPath, 0777, true);
        }
        $this->file = $runPath . 'lock';
        if (!file_exists($this->file))
        {
            @file_put_contents($this->file, '');
        }
    }

    /**
     * 加锁执行
     * @param $func
     * @param $block
     * @return mixed
     */
    public function execute($func, $block = true)
    {
        $fp = fopen($this->file, 'r');
        $is_flock = $block ? flock($fp, LOCK_EX) : flock($fp, LOCK_EX | LOCK_NB);
        $call_back = null;
        if ($is_flock)
        {
            $call_back = $func();
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        return $call_back;
    }
}