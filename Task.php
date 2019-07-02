<?php
namespace EasyTask;

class Task
{
    /**
     * @var mixed 进程通信redis
     */
    private $redis = null;

    /**
     * @var bool 是否守护进程
     */
    private $daemon = false;

    /**
     * @var int 子进程休息时间
     */
    private $sleepTime = 1;

    /**
     * @var bool 关闭标准输入输出
     */
    private $closeInOut = false;

    /**
     * @var bool 支持异步信号
     */
    private $canAsync = false;

    /**
     * 构造函数
     */
    public function __construct()
    {
        //异步支持
        $this->canAsync = function_exists('pcntl_async_signals');
        if ($this->canAsync)
        {
            $this->sleepTime = 100;
            pcntl_async_signals(true);
        }
    }

    /**
     * 设置守护
     * @param bool $daemon 是否守护
     * @return $this
     */
    public function setDaemon($daemon = false)
    {
        $this->daemon = $daemon;
        return $this;
    }

    /**
     * 设置标准输入输出
     * @param bool $closeInOut
     * @return $this
     */
    public function setInOut($closeInOut = false)
    {
        $this->closeInOut = $closeInOut;
        return $this;
    }

    /**
     * 设置Redis
     * @param \Redis $redis
     */
    public function setRedis($redis)
    {
        $this->redis = $redis;
    }

    /**
     * 开始运行
     */
    public function start()
    {

    }

    /**
     * 运行状态
     */
    public function status()
    {

    }

    /**
     * 停止运行
     */
    public function stop()
    {

    }
}