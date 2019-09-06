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
        $this->allocate();
    }

    /**
     * 分配进程处理指令任务
     */
    public function allocate()
    {
        foreach ($this->task->taskList as $key => $item)
        {
            //提取参数
            $type = $item['type'];
            $used = $item['used'];
            $alas = $item['alas'];
            $time = $item['time'];

            //根据Worker数分配进程
            for ($i = 0; $i < $used; $i++)
            {
                //组装cmd
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

                //运行Cmd
                @pclose(@popen($cmd, 'r'));
            }
        }
    }

    /**
     * 进程执行
     * @param array $commandData 执行指令
     */
    public static function invoke($commandData)
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
            //执行指令
            @pclose(@popen($command, 'r'));

            //CPU休息
            sleep($time);
        }
    }

}

$commandData = getopt('a:t:c:');
if ($commandData)
{
    Win::invoke($commandData);
}
