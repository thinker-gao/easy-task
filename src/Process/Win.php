<?php
namespace EasyTask\Process;

use EasyTask\Command;
use \ArrayObject as ArrayObject;
use EasyTask\Helper;

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
        if (!$extend)
        {
            $this->allocate();
        }
        else
        {
            $this->invokeTask($extend);
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
     * 执行非指令任务
     * @param string $alas 任务别名
     */
    public function invokeTask($alas)
    {
        if (isset($this->task->taskList[$alas]))
        {
            //提取任务
            $task = $this->task->taskList[$alas];

            //循环执行
            while (true)
            {
                if ($task['type'] == 0)
                {
                    $func = $task['func'];
                    $func();
                }
                elseif ($task['type'] == 1)
                {
                    call_user_func([$task['class'], $task['func']]);
                }
                else
                {
                    $object = new $task['class']();
                    call_user_func([$object, $task['func']]);
                }
                sleep($task['time']);
                $this->waitCommand();
            }
        }


    }

    /**
     * 分配进程处理指令任务
     */
    public function allocate()
    {
        $entryCommand = (Helper::getCommandByArgv());
        foreach ($this->task->taskList as $item)
        {
            //提取参数
            $type = $item['type'];
            $alas = $item['alas'];
            $time = $item['time'];
            $used = $item['used'];

            //根据Worker数分配进程
            for ($i = 0; $i < $used; $i++)
            {
                //组装cmd
                if ($type == 3)
                {
                    //组装数据
                    $command = base64_encode($item['command']);
                    $params = " -a{$alas} -t{$time} -c{$command}";
                    if ($this->task->daemon)
                    {
                        //异步执行
                        $cmd = 'start /b php ' . __FILE__ . $params;
                    }
                    else
                    {
                        //同步执行
                        $cmd = 'wmic process call create "' . 'php ' . __FILE__ . $params . '"';
                    }
                }
                else
                {
                    if ($this->task->daemon)
                    {
                        //异步执行
                        $cmd = 'start /b ' . $entryCommand . "-m-e:{$alas}";
                    }
                    else
                    {
                        //同步执行
                        $cmd = 'wmic process call create "' . $entryCommand . " -m-e:{$alas}" . '"';
                    }
                }

                //运行Cmd
                @pclose(@popen($cmd, 'r'));
            }
        }
    }

    /**
     * 监听命令
     */
    private function waitCommand()
    {
        //接收命令
        $this->waitCommandForExecute(2, function ($command) {
            if ($command['type'] == 'stop')
            {
                exit();
            }
        });
    }

    /**
     * 根据命令执行对应操作
     * @param int $msgType 消息类型
     * @param \Closure $func 执行函数
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

    /**
     * 进程定时器
     * @param array $commandData 执行指令
     */
    public static function timerByCommand($commandData)
    {
        //提取参数
        $alas = $commandData['a'];
        $time = $commandData['t'];
        $command = base64_decode($commandData['c']);

        //设置进程标题
        @cli_set_process_title($alas);

        //启动定时器
        while (true)
        {
            @pclose(@popen($command, 'r'));
            sleep($time);
            //$this->waitCommand();
        }
    }
}

$commandData = getopt('a:t:c:');
if ($commandData)
{
    Win::timerByCommand($commandData);
}

