<?php

class My extends Thread
{
    /**
     * 线程执行的任务
     * @var $item
     */
    private $item;

    /**
     * 构造函数
     * @var $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * 单线程执行的任务
     */
    function run()
    {
        $item = $this->item;
        if ($item['type'] == 0)
        {
            $func = $item['func'];
            $func();
        }
        elseif ($item['type'] == 1)
        {
            call_user_func([$item['class'], $item['func']]);
        }
        else
        {
            $object = new $item['class']();
            call_user_func([$object, $item['func']]);
        }
    }
}

class Process
{
    /**
     * Task实例
     * @var $task
     */
    private $task;

    /**
     * 构造函数
     * @var $task
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * 开始运行
     */
    public function start()
    {
        $this->allocate();
    }

    /**
     * 分配进程处理任务
     */
    public function allocate()
    {
        foreach ($this->task->taskList as $item)
        {
            //提取参数
            $used = $item['used'];

            //根据Worker数分配进程
            for ($i = 0; $i < $used; $i++)
            {
                $pool = new My($item);
                $pool->start();
            }
        }
    }
}

