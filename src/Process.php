<?php
namespace EasyTask;

class Process
{
    /**
     * @var $task
     */
    private $task;

    /**
     * @var int 进程休息时间
     */
    private $sleepTime;

    /**
     * @var array 进程执行记录
     */
    private $processList = [];

    /**
     * 构造函数
     * @var Task $task
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

        $this->allocate();
    }

    /**
     * 守护进程
     */
    private function daemon()
    {
        if ($this->task->isChdir)
        {
            chdir('/');
        }
        if ($this->task->closeInOut)
        {
            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);
        }
        $pid = pcntl_fork();
        if ($pid < 0)
        {
            exit('Create process failed' . PHP_EOL);
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
                exit('Setsid child process failed' . PHP_EOL);
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
            $pname = "{$this->task->prefix}_{$name}";

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
                    $this->processList[] = ['pid' => $pid, 'task_name' => $pname, 'started' => $date, 'timer' => $time . 's', 'status' => 'active', 'ppid' => $ppid,];

                    //主进程设置非阻塞
                    pcntl_wait($status, WNOHANG);
                }
                else
                {
                    //执行定时任务
                    $this->timer($time, $pname, $item);
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
                $object->$item['func']();
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

            //订阅汇报任务
        }
        exit();
    }

    /**
     * 守护进程常驻
     */
    private function daemonWait()
    {
        //监听Kill命令
        pcntl_signal(SIGTERM, function () {
            posix_kill(0, SIGTERM);
            exit();
        });

        //挂起进程
        while (true)
        {
            sleep(1);

            //调用信号处理
            if (!$this->task->canAsync) pcntl_signal_dispatch();
        }
    }
}