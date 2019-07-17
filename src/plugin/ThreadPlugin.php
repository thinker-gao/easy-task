<?php
namespace EasyTask\plugin;

use EasyTask\Console;

/**
 * 多线程插件
 */
class ThreadPlugin
{
    /**
     * Task实例
     * @var $task
     */
    private $task;

    /**
     * 线程执行记录
     * @var array
     */
    private $threadList = [];

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
        //分配线程
        $pools = [];
        foreach ($this->task->taskList as $item)
        {
            //提取参数
            $used = $item['used'];

            //根据used数分配线程
            for ($i = 0; $i < $used; $i++)
            {
                $pools[] = new TaskThread($item);
            }
        }

        //启动线程
        foreach ($pools as $pool)
        {
            $pool->start();

            //记录线程信息
            $tid = $pool->getThreadId();
            $ttid = $pool->getCurrentId();
            $name = $pool->item['alas'];
            $time = $pool->item['time'];
            $date = date('Y-m-d H:i:s');
            $pName = "{$this->task->prefix}_{$name}";
            $this->threadList[] = ['tid' => $tid, 'task_name' => $pName, 'started' => $date, 'timer' => $time . 's', 'status' => 'active', 'ttid' => $ttid];
        }

        //输出启动信息
        Console::showTable($this->threadList, false);
    }
}


