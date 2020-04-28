<?php
namespace EasyTask\Process;

use EasyTask\Command;
use EasyTask\Cron\CronExpression;
use EasyTask\Env;
use EasyTask\Error;
use EasyTask\Log;
use \Event as Event;
use \EventConfig as EventConfig;
use \EventBase as EventBase;
use \Throwable as Throwable;
use EasyTask\Helper;

/**
 * Class Linux
 * @package EasyTask\Process
 */
class Linux
{
    /**
     * 进程启动时间
     * @var int
     */
    private $startTime;

    /**
     * 进程命令管理
     * @var array
     */
    private $commander;

    /**
     * 任务列表
     * @var array
     */
    private $taskList;

    /**
     * 进程执行记录
     * @var array
     */
    private $processList = [];

    /**
     * 构造函数
     * @var array $taskList
     */
    public function __construct($taskList)
    {
        $this->taskList = $taskList;
        $this->startTime = time();
        $this->commander = new Command();
        if (Env::get('canAsync'))
        {
            Helper::openAsyncSignal();
        }
    }

    /**
     * 开始运行
     */
    public function start()
    {
        //发送命令
        $this->commander->send([
            'type' => 'start',
            'msgType' => 2
        ]);

        //常驻处理
        if (Env::get('daemon'))
        {
            $this->setMask();
            $this->fork(
                function () {
                    $sid = posix_setsid();
                    if ($sid < 0)
                    {
                        Helper::showError('set child processForManager failed,please try again');
                    }
                    $this->allocate();
                },
                function () {
                    pcntl_wait($status, WNOHANG);
                    $this->status();
                }
            );
        }

        //同步处理
        $this->allocate();
    }

    /**
     * 运行状态
     */
    public function status()
    {
        //发送命令
        $this->commander->send([
            'type' => 'status',
            'msgType' => 2
        ]);
        $this->masterWaitExit();
    }

    /**
     * 停止运行
     * @param bool $force 是否强制
     */
    public function stop($force = false)
    {
        //发送命令
        $this->commander->send([
            'type' => 'stop',
            'force' => $force,
            'msgType' => 2
        ]);
    }

    /**
     * 设置掩码
     */
    private function setMask()
    {
        umask(0);
    }

    /**
     * 分配进程处理任务
     */
    private function allocate()
    {
        if (Env::get('daemon'))
        {
            Helper::setStdClose();
        }
        foreach ($this->taskList as $item)
        {
            //提取参数
            $prefix = Env::get('prefix');
            $item['data'] = date('Y-m-d H:i:s');
            $item['alas'] = "{$prefix}_{$item['alas']}";
            $used = $item['used'];

            //根据Worker数分配进程
            for ($i = 0; $i < $used; $i++)
            {
                $this->forkItemExec($item);
            }
        }

        //常驻守护
        $this->daemonWait();
    }

    /**
     * 创建子进程
     * @param $childInvoke
     * @param $mainInvoke
     */
    private function fork($childInvoke, $mainInvoke)
    {
        $pid = pcntl_fork();
        if ($pid == -1)
        {
            Helper::showError('fork child process failed,please try again');
        }
        elseif ($pid)
        {
            $mainInvoke($pid);
        }
        else
        {
            $childInvoke();
        }
    }

    /**
     * 创建任务执行的子进程
     * @param array $item 执行项目
     */
    private function forkItemExec($item)
    {
        $this->fork(
            function () use ($item) {
                $this->invoker($item);
            },
            function ($pid) use ($item) {
                //write_log
                $ppid = posix_getpid();
                $this->processList[] = ['pid' => $pid, 'name' => $item['alas'], 'item' => $item, 'started' => $item['data'], 'time' => $item['time'], 'status' => 'active', 'ppid' => $ppid];
                //set not block
                pcntl_wait($status, WNOHANG);
            }
        );
    }

    /**
     * 执行器
     * @param array $item 执行项目
     */
    private function invoker($item)
    {
        //输出信息
        $item['pid'] = getmypid();
        $item['ppid'] = posix_getppid();
        $text = "this worker {$item['alas']}(pid:{$item['pid']})";
        Log::writeInfo("$text is start");

        //进程标题
        Helper::cli_set_process_title($item['alas']);

        //Kill信号
        pcntl_signal(SIGTERM, function () use ($text) {
            Log::writeInfo("listened kill command, $text not to exit the program for safety");
        });

        //执行任务
        if (is_int($item['time']) || is_float($item['time']))
        {
            if ($item['time'] === 0) $this->invokerByDirect($item);
            Env::get('canEvent') ? $this->invokeByEvent($item) : $this->invokeByDefault($item);
        }
        elseif (is_string($item['time']))
        {
            $this->invokeByCron($item);
        }
    }

    /**
     * 普通执行(执行完成,直接退出)
     * @param array $item 执行项目
     */
    private function invokerByDirect($item)
    {
        //执行程序
        $this->execute($item);

        //进程退出
        exit;
    }

    /**
     * 通过闹钟信号执行
     * @param array $item 执行项目
     */
    private function invokeByDefault($item)
    {
        //安装信号管理
        pcntl_signal(SIGALRM, function () use ($item) {
            pcntl_alarm($item['time']);
            $this->execute($item);
        }, false);

        //发送闹钟信号
        pcntl_alarm($item['time']);

        //挂起进程(同步调用信号,异步CPU休息)
        while (true)
        {
            //CPU休息
            sleep(1);

            //信号处理(同步/异步)
            if (!Env::get('canAsync')) pcntl_signal_dispatch();
        }
    }

    /**
     * 通过Event事件执行
     * @param array $item 执行项目
     */
    private function invokeByEvent($item)
    {
        //创建Event事件
        $eventConfig = new EventConfig();
        $eventBase = new EventBase($eventConfig);
        $event = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, function () use ($item) {
            try
            {
                $this->execute($item);
            }
            catch (Throwable $exception)
            {
                $type = 'appException';
                Error::report($type, $exception);
                $this->checkDaemonForExit($item);
            }
        });

        //添加事件
        $event->add($item['time']);

        //事件循环
        $eventBase->loop();
    }

    /**
     * 通过CronTab命令执行
     * @param array $item 执行项目
     */
    private function invokeByCron($item)
    {
        $nextExecuteTime = 0;
        while (true)
        {
            if (!$nextExecuteTime) $nextExecuteTime = Helper::getCronNextDate($item['time']);
            $waitTime = (strtotime($nextExecuteTime) - time());
            if (!$waitTime)
            {
                $this->execute($item);
                $nextExecuteTime = 0;
            }
            else
            {
                //Cpu休息
                sleep(1);

                //常驻进程存活检查
                $this->checkDaemonForExit($item);
            }
        }
        exit;
    }

    /**
     * 执行任务代码
     * @param array $item 执行项目
     */
    private function execute($item)
    {
        $type = $item['type'];
        switch ($type)
        {
            case 1:
                $func = $item['func'];
                $func();
                break;
            case 2:
                call_user_func([$item['class'], $item['func']]);
                break;
            case 3:
                $object = new $item['class']();
                call_user_func([$object, $item['func']]);
                break;
            default:
                @pclose(@popen($item['command'], 'r'));
        }

        //常驻进程存活检查
        $this->checkDaemonForExit($item);
    }

    /**
     * 检查常驻进程是否存活
     * @param array $item
     */
    private function checkDaemonForExit($item)
    {
        if (!posix_kill($item['ppid'], 0))
        {
            Log::writeInfo("listened exit command, this worker {$item['alas']}(pid:{$item['pid']}) is safely exited", 'info', true);
        }
    }

    /**
     * master进程等待结束退出
     */
    private function masterWaitExit()
    {
        $i = 15;
        while ($i--)
        {
            //CPU休息1秒
            sleep(1);

            //接收汇报
            $this->commander->waitCommandForExecute(1, function ($report) {
                if ($report['type'] == 'status' && $report['status'])
                {
                    Helper::showTable($report['status']);
                }
            }, $this->startTime);
        }
        exit;
    }

    /**
     * 守护进程常驻
     */
    private function daemonWait()
    {
        //设置进程标题
        Helper::cli_set_process_title(Env::get('prefix'));

        //输出信息
        $pid = getmypid();
        $text = "this manager(pid:{$pid})";
        Log::writeInfo("$text is start");

        //Kill信号
        pcntl_signal(SIGTERM, function () use ($text) {
            Log::writeInfo("listened kill command $text is safely exited", 'info', true);
        });

        //挂起进程
        while (true)
        {
            //CPU休息
            sleep(1);

            //接收命令start/status/stop
            $this->commander->waitCommandForExecute(2, function ($command) use ($text) {
                $exitText = "listened exit command, $text is safely exited";
                $statusText = "listened status command, $text is reported";
                $forceExitText = "listened exit command, $text is safely exited";
                if ($command['type'] == 'start')
                {
                    if ($command['time'] > $this->startTime)
                    {
                        Log::writeInfo($forceExitText);
                        posix_kill(0, SIGKILL);
                    }
                }
                if ($command['type'] == 'status')
                {
                    $report = $this->processStatus();
                    $this->commander->send([
                        'type' => 'status',
                        'msgType' => 1,
                        'status' => $report,
                    ]);
                    Log::writeInfo($statusText);
                }
                if ($command['type'] == 'stop')
                {
                    if ($command['force'])
                    {
                        Log::writeInfo($forceExitText);
                        posix_kill(0, SIGKILL);
                    }
                    else
                    {
                        Log::writeInfo($exitText);
                        exit();
                    }
                }

            }, $this->startTime);

            //信号调度
            if (!Env::get('canAsync')) pcntl_signal_dispatch();

            //检查进程
            if (Env::get('canAutoRec')) $this->processStatus();
        }
    }

    /**
     * 查看进程状态
     * @return array
     */
    private function processStatus()
    {
        $report = [];
        foreach ($this->processList as $key => $item)
        {
            //提取参数
            $pid = $item['pid'];

            //进程状态
            $rel = pcntl_waitpid($pid, $status, WNOHANG);
            if ($rel == -1 || $rel > 0)
            {
                //标记状态
                $item['status'] = 'stop';

                //进程退出,重新fork
                if (Env::get('canAutoRec'))
                {
                    $this->forkItemExec($item['item']);
                    Log::writeInfo("the worker {$item['name']}(pid:{$pid}) is stop,try to fork new one");
                    unset($this->processList[$key]);
                }
            }

            //记录状态
            unset($item['item']);
            $report[] = $item;
        }

        return $report;
    }
}