<?php
namespace EasyTask\Process;

use EasyTask\Command;
use EasyTask\Env;
use \Event as Event;
use \EventConfig as EventConfig;
use \EventBase as EventBase;
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
        if (!Env::get('canEvent') && Env::get('canAsync'))
        {
            Helper::openAsyncSignal();
        }
    }

    /**
     * 开始运行
     */
    public function start()
    {
        //发送命令(关闭重复进程)
        $this->commander->send([
            'type' => 'start',
            'msgType' => 2
        ]);

        //掩码
        if (Env::get('daemon')) $this->setMask();

        //Fork
        $this->fork(
            function () {
                //child
                $sid = posix_setsid();
                if ($sid < 0)
                {
                    Helper::showError('set child processForManager failed');
                }
                //child_allocate
                $this->allocate();
            },
            function () {
                //parent
                $this->status();
            }
        );
    }

    /**
     * 运行状态
     */
    public function status()
    {
        //发送查询命令
        $this->commander->send([
            'type' => 'status',
            'msgType' => 2
        ]);

        //master等待返回结果
        $this->masterWaitExit();
    }

    /**
     * 停止运行
     * @param bool $force 是否强制
     */
    public function stop($force = false)
    {
        //发送关闭命令
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
     * 关闭标准输入输出
     */
    private function closeInOut()
    {
        fclose(STDIN);
        fclose(STDOUT);
    }

    /**
     * 分配进程处理任务
     */
    private function allocate()
    {
        if (Env::get('daemon'))
        {
            $this->closeInOut();
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
            Helper::showError('fork child process failed');
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
                $this->processList[] = ['pid' => $pid, 'name' => $item['alas'], 'item' => $item, 'started' => $item['data'], 'timer' => $item['time'], 'status' => 'active', 'ppid' => $ppid];
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
        if ($item['time'] == 0)
        {
            $this->invokerByDirect($item);
        }
        if (!Env::get('canEvent'))
        {
            $this->invokeByAlarm($item);
        }
        else
        {
            $this->invokeByEvent($item);
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
    private function invokeByAlarm($item)
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

            //同步模式(调用信号处理)
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
            $this->execute($item);
        });

        //添加事件
        $event->add($item['time']);

        //事件循环
        $eventBase->loop();
    }

    /**
     * 执行任务代码
     * @param array $item 执行项目
     */
    private function execute($item)
    {
        @cli_set_process_title($item['alas']);
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
    }

    /**
     * master进程等待结束退出
     */
    private function masterWaitExit()
    {
        $i = 10;
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
        @cli_set_process_title(Env::get('prefix'));

        //注册kill信号
        pcntl_signal(SIGTERM, function () {
            posix_kill(0, SIGTERM) && exit();
        });

        //挂起进程
        while (true)
        {
            //CPU休息
            sleep(1);

            //接收命令start/status/stop
            $this->commander->waitCommandForExecute(2, function ($command) {
                if ($command['type'] == 'start')
                {
                    if ($command['time'] > $this->startTime)
                    {
                        posix_kill(0, SIGTERM) && exit();
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
                }
                if ($command['type'] == 'stop')
                {
                    $command['force'] ? posix_kill(0, SIGKILL) : posix_kill(0, SIGTERM) && exit();
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