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
     * @var string
     */
    private $queFile;

    /**
     * 构造函数
     * @param string $name
     * @throws
     */
    public function __construct($name = 'queue')
    {
        //创建进程锁
        $this->lock = new Lock($name);

        //创建队列文件
        $path = Helper::getQuePath();
        $file = $path . '%s.dat';
        $this->queFile = sprintf($file, md5($name));
        if (!file_exists($this->queFile))
        {
            if (!file_put_contents($this->queFile, '[]', LOCK_EX))
            {
                Helper::showError('crate queFile failed,please try again');
            }
        }
    }

    /**
     * 向队列投递数据
     * @param string $item
     */
    public function push($item)
    {
        $this->lock->execute(function () use ($item) {
            //read
            $content = file_get_contents($this->queFile);
            $queue_data = $content ? json_decode($content, true) : [];
            $queue_data = is_array($queue_data) ? $queue_data : [];

            //write
            array_push($queue_data, $item);
            if (!file_put_contents($this->queFile, json_encode($queue_data)))
            {
                Helper::showError('failed to save data to queue file');
            }
        });
    }

    /**
     * 从队列弹出数据
     * @return string|null
     */
    public function shift()
    {
        return $this->lock->execute(function () {
            //read
            $content = file_get_contents($this->queFile);
            $queue_data = $content ? json_decode($content, true) : [];
            $queue_data = is_array($queue_data) ? $queue_data : [];

            //shift+write
            $value = array_shift($queue_data);
            if (!file_put_contents($this->queFile, json_encode($queue_data)))
            {
                Helper::showError('failed to save data to queue file');
            }
            return $value;
        });
    }
}
