<?php
namespace EasyTask\Process;

use EasyTask\Command;
use \ArrayObject as ArrayObject;
use EasyTask\Helper;
use \Closure as Closure;

/**
 * Class Win
 * @package EasyTask\Process
 */
class Win
{
    /**
     * Task实例
     * @var $task
     */
    private $task;

    /**
     * 构造函数
     * @throws
     * @var  $task
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
        $extend = Helper::getCommandExtend();
        if ($extend)
        {
            //执行任务
            $this->invoke($extend);
        }
        else
        {
            //分配任务
            $this->allocate();
        }
    }

    /**
     * 设置进程
     */
    private function setProcess()
    {
        if ($this->task->umask)
        {
            umask(0);
        }
        if ($this->task->isChdir)
        {
            chdir('/');
        }
        if ($this->task->closeInOut)
        {
            fclose(STDIN);
            fclose(STDOUT);
        }
    }

    /**
     * 执行任务
     * @param string $uniKey 任务Key
     */
    public function invoke($uniKey)
    {
        $this->setProcess();
        $tasks = $this->task->taskList;
        if (isset($tasks[$uniKey]))
        {
            //获取任务
            $task = $tasks[$uniKey];

            //提取参数
            $type = $task['type'];
            $time = $task['time'];
            $alas = $task['alas'];

            //设置进程标题
            $alas = "{$this->task->prefix}_{$alas}";
            @cli_set_process_title($alas);

            //循环执行
            while (true)
            {
                if ($type == 1)
                {
                    $func = $task['func'];
                    $func();
                }
                elseif ($type == 2)
                {
                    call_user_func([$task['class'], $task['func']]);
                }
                elseif ($type == 3)
                {
                    $object = new $task['class']();
                    call_user_func([$object, $task['func']]);
                }

                //CPU休息
                sleep($time);
            }
        }
    }

    /**
     * 分配进程处理指令任务
     */
    public function allocate()
    {
        $initCommand = (Helper::getEntryCommand());
        foreach ($this->task->taskList as $key => $item)
        {
            //提取参数
            $used = $item['used'];

            //根据Worker数分配进程
            for ($i = 0; $i < $used; $i++)
            {
                //组装Cmd
                if ($this->task->daemon)
                {
                    //异步执行
                    $cmd = 'start /b ' . $initCommand . "-extend:{$key}";
                }
                else
                {
                    //同步执行
                    $cmd = 'wmic process call create "' . $initCommand . " -extend:{$key}" . '"';
                }

                //运行Cmd
                @pclose(@popen($cmd, 'r'));
            }
        }
    }
}

