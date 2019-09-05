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
     * 进程命令管理
     * @var ArrayObject
     */
    private $commander;

    /**
     * 构造函数
     * @throws
     * @var  $task
     */
    public function __construct($task)
    {
        $this->task = $task;
        $this->commander = new Command();
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
     * 停止运行
     * @param bool $force 是否强制
     */
    public function stop($force = false)
    {
        $this->commander->send([
            'type' => 'stop',
            'force' => $force,
            'msgType' => 2
        ]);
    }

    /**
     * 执行任务
     * @param string $uniKey 任务Key
     */
    public function invoke($uniKey)
    {
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

                //接收命令
                $this->waitCommandForExecute(2, function ($command) {
                    if ($command['type'] == 'stop')
                    {
                        exit();
                    }
                });
            }
        }
    }

    /**
     * 分配进程处理指令任务
     */
    public function allocate()
    {
        $initCommand = (Helper::getCommandByArgv());
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

    /**
     * 根据命令执行对应操作
     * @param int $msgType 消息类型
     * @param Closure $func 执行函数
     */
    public function waitCommandForExecute($msgType, $func)
    {
        $command = '';
        $this->commander->receive($msgType, $command);
        if (!$command)
        {
            return;
        }
        $func($command);
    }

}

