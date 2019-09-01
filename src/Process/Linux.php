<?php
namespace EasyTask\Process;

use EasyTask\Command;
use EasyTask\Console;
use \ArrayObject as ArrayObject;

/**
 * Class Linux
 * @package EasyTask\Process
 */
class Linux
{
    /**
     * Task实例
     * @var $task
     */
    private $task;

    /**
     * 进程休息时间
     * @var int
     */
    private $sleepTime;

    /**
     * 进程命令管理
     * @var ArrayObject
     */
    private $commander;

    /**
     * 进程执行记录
     * @var array
     */
    private $processList = [];

    /**
     * 构造函数
     * @throws
     * @var  $task
     */
    public function __construct($task)
    {
        $this->task = $task;
        if (!$task->canAsync)
        {
            $this->sleepTime = 1;
        }
        else
        {
            $this->sleepTime = 100;
            pcntl_async_signals(true);
        }

        $this->commander = new Command();
    }

    /**
     * 开始运行
     */
    public function start()
    {
        if ($this->task->daemon)
        {
            $this->daemon();
        }
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
        $this->allocate();
    }

    /**
     * 运行状态
     */
    public function status()
    {
        $this->commander->send([
            'type' => 'status',
            'msgType' => 2
        ]);
        $this->initWaitExit();
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
     * 守护进程
     * @throws
     */
    private function daemon()
    {
        $pid = pcntl_fork();
        if ($pid < 0)
        {
            Console::error('创建进程失败');
        }
        elseif ($pid)
        {
            $this->initWaitExit();
        }
        else
        {
            //子进程转守护进程
            $sid = posix_setsid();
            if ($sid < 0)
            {
                Console::error('设置守护进程失败');
            }
        }
    }

    /**
     * 分配进程处理任务
     */
    public function allocate()
    {
        foreach ($this->task->taskList as $item)
        {
            //提取参数
            $name = $item['alas'];
            $time = $item['time'];
            $date = date('Y-m-d H:i:s');
            $used = $item['used'];
            $pName = "{$this->task->prefix}_{$name}";

            //根据Worker数分配进程
            for ($i = 0; $i < $used; $i++)
            {
                $pid = pcntl_fork();
                if ($pid == -1)
                {
                    exit();
                }
                elseif ($pid)
                {
                    //记录进程
                    $ppid = posix_getpid();
                    $this->processList[] = ['pid' => $pid, 'task_name' => $pName, 'started' => $date, 'timer' => $time . 's', 'status' => 'active', 'ppid' => $ppid,];

                    //主进程设置非阻塞
                    pcntl_wait($status, WNOHANG);
                }
                else
                {
                    //执行定时任务
                    $this->timer($time, $pName, $item);
                }
            }
        }
        $this->daemonWait();
    }

    /**
     * 进程定时器
     * @param int $time 执行间隔
     * @param string $pname 进程名称
     * @param array $item 执行项目
     */
    public function timer($time, $pname, $item)
    {
        //设置任务进程标题
        @cli_set_process_title($pname);

        //安装信号管理
        pcntl_signal(SIGALRM, function () use ($time, $item) {
            pcntl_alarm($time);
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

        }, false);

        //发送闹钟信号
        pcntl_alarm($time);

        //挂起进程(同步调用信号,异步CPU休息)
        while (true)
        {
            //CPU休息
            sleep($this->sleepTime);

            //同步模式(调用信号处理)
            if (!$this->task->canAsync) pcntl_signal_dispatch();
        }
    }

    /**
     * init进程等待结束退出
     */
    private function initWaitExit()
    {
        $i = 10;
        while ($i--)
        {
            //CPU休息1秒
            sleep(1);

            //接收汇报
            $this->WaitCommandForExecute(1, function ($report) {
                if ($report['type'] == 'status')
                {
                    Console::showTable($report['status']);
                }
                if ($report['type'] == 'allocate')
                {
                    Console::showTable($report['allocate']);
                }
            });
        }
        exit();
    }

    /**
     * 守护进程常驻
     */
    private function daemonWait()
    {
        //守护进程设置进程名
        @cli_set_process_title($this->task->prefix);

        //任务汇报Init进程
        $this->commander->send([
            'type' => 'allocate',
            'msgType' => 1,
            'allocate' => $this->processList,
        ]);

        //监听Kill命令
        pcntl_signal(SIGTERM, function () {
            posix_kill(0, SIGTERM);
            exit();
        });

        //挂起进程
        while (true)
        {
            //CPU休息1秒
            sleep(1);

            //接收命令
            $this->waitCommandForExecute(2, function ($command) {
                if ($command['type'] == 'status')
                {
                    $this->processStatus();
                    $this->commander->send([
                        'type' => 'status',
                        'msgType' => 1,
                        'status' => $this->processList,
                    ]);
                }
                if ($command['type'] == 'stop')
                {
                    $command['force'] ? posix_kill(0, SIGKILL) : posix_kill(0, SIGTERM) && exit();
                }

            });

            //调用信号处理
            if (!$this->task->canAsync) pcntl_signal_dispatch();
        }
    }

    /**
     * 查看进程状态
     */
    public function processStatus()
    {
        foreach ($this->processList as $key => $item)
        {
            //提取参数
            $pid = $item['pid'];

            //检查进程状态
            $rel = pcntl_waitpid($pid, $status, WNOHANG);
            if ($rel == -1 || $rel > 0)
            {
                $this->processList[$key]['status'] = 'stoped';
            }
        }
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
        if (!$command || (!empty($command['time']) && (time() - $command['time']) > 5))
        {
            return;
        }
        $func($command);
    }
}