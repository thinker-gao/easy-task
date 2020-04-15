<?php
namespace EasyTask\Process;

use EasyTask\Command;
use EasyTask\Cron\CronExpression;
use EasyTask\Env;
use EasyTask\Error;
use EasyTask\Log;
use EasyTask\Wpc;
use \Event as Event;
use \EventConfig as EventConfig;
use \EventBase as EventBase;
use EasyTask\Helper;
use EasyTask\Wts;

/**
 * Class Win
 * @package EasyTask\Process
 */
class Win
{
    /**
     * Wts服务
     * @var Wts
     */
    private $wts;

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
     * 任务总数
     * @var int
     */
    private $taskCount;

    /**
     * 进程worker
     * @var array
     */
    private $workerList;

    /**
     * AutoRec事件
     * @var bool
     */
    private $autoRecEvent;

    /**
     * 构造函数
     * @param array $taskList
     */
    public function __construct($taskList)
    {
        $this->wts = new Wts();
        $this->startTime = time();
        $this->taskList = $taskList;
        $this->setTaskCount();
        $this->commander = new Command();
    }

    /**
     * 开始运行
     */
    public function start()
    {
        //构建基础
        $this->make();

        //启动检查
        $this->checkForRun();

        //进程分配
        $func = function ($name) {
            $this->executeByProcessName($name);
        };
        if (!$this->wts->allocateProcess($func))
        {
            Helper::showError('Unexpected error, process has been allocated');
        }
    }

    /**
     * 启动检查
     */
    private function checkForRun()
    {
        if (!Env::get('phpPath'))
        {
            Helper::showError('If you use windows system, then you must set the value of phpPath through the setPhpPath method');
        }
        if (!$this->chkCanStart())
        {
            Helper::showError('Please close the running process first');
        }
    }

    /**
     * 检查进程
     * @return bool
     */
    private function chkCanStart()
    {
        $workerList = $this->workerList;
        foreach ($workerList as $name => $item)
        {
            $status = $this->wts->getProcessStatus($name);
            if (!$status)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 跟进进程名称执行任务
     * @param string $name
     * @throws \Exception
     */
    private function executeByProcessName($name)
    {
        if ($name == 'master')
        {
            $this->allocate();
        }
        else
        {
            if ($name == 'manager')
            {
                $this->daemonWait();
            }
            else
            {
                $this->invoker($name);
            }
        }
    }

    /**
     * 构建任务
     */
    private function make()
    {
        $list = [];
        if (!$this->wts->getProcessStatus('manager'))
        {
            $list = ['master', 'manager'];
        }
        foreach ($list as $name)
        {
            $this->wts->joinProcess($name);
        }
        foreach ($this->taskList as $key => $item)
        {
            //提取参数
            $alas = $item['alas'];
            $used = $item['used'];

            //根据Worker数构建
            for ($i = 0; $i < $used; $i++)
            {
                $name = $item['name'] = $alas . '___' . $i;
                $this->workerList[$name] = $item;
                $this->wts->joinProcess($name);
            }
        }
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

        //等待返回结果
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
     * 分配子进程
     * @throws \Exception
     */
    private function allocate()
    {
        //清理进程信息
        $this->wts->cleanProcessInfo();

        //计算要分配的进程数
        $count = $this->taskCount;

        //根据count数分配进程
        for ($i = 0; $i <= $count; $i++)
        {
            $this->forkItemExec();
        }

        //查询状态
        $i = 30;
        while ($i--)
        {
            $status = $this->wts->getProcessStatus('manager');
            if ($status)
            {
                $this->status();
                break;
            }
            sleep(1);
        }
    }

    /**
     * 创建任务执行的子进程
     * @throws \Exception
     */
    private function forkItemExec()
    {
        try
        {
            //提取参数
            $argv = Helper::getCliInput(2);
            $file = array_shift($argv);;
            $char = join(' ', $argv);
            $work = dirname(array_shift($argv));
            $style = Env::get('daemon') ? 1 : 0;

            //创建进程
            $wpc = new Wpc();
            $wpc->setFile($file);
            $wpc->setArgument($char);
            $wpc->setStyle($style);
            $wpc->setWorkDir($work);
            $pid = $wpc->start();
            if (!$pid)
            {
                Helper::showError('Create process failed!', true);
            }
        }
        catch (\Exception $exception)
        {
            throw  new \Exception(Helper::convert_char($exception->getMessage()));
        }
    }

    /**
     * 初始化任务数量
     */
    private function setTaskCount()
    {
        $count = 0;
        foreach ($this->taskList as $key => $item)
        {
            $count += (int)$item['used'];
        }
        $this->taskCount = $count;
    }

    /**
     * 执行器
     * @param string $name 任务名称
     */
    private function invoker($name)
    {
        //提取字典
        $taskDict = $this->workerList;
        if (!isset($taskDict[$name]))
        {
            Helper::showError("the task name $name is not exist" . json_encode($taskDict));
        }

        //提取Task字典
        $item = $taskDict[$name];

        //输出信息
        $pid = getmypid();
        if (!Env::get('daemon')) Helper::showInfo("this worker {$item['alas']}(pid:{$pid}) is start");

        //设置进程标题
        $title = Env::get('prefix') . '.' . $item['alas'];
        Helper::cli_set_process_title($title);

        //保存进程信息
        $item['pid'] = $pid;
        $this->wts->saveProcessInfo([
            'pid' => $pid,
            'name' => $item['name'],
            'alas' => $item['alas'],
            'started' => date('Y-m-d H:i:s', $this->startTime),
            'time' => $item['time']
        ]);

        //执行任务
        if (CronExpression::isValidExpression($item['time']))
        {
            $this->invokeByCron($item);
        }
        elseif (Env::get('canEvent') && $item['time'] != 0)
        {
            $this->invokeByEvent($item);
        }
        else
        {
            $this->invokeByDefault($item);
        }
    }

    /**
     * 通过默认定时执行
     * @param array $item 执行项目
     */
    private function invokeByDefault($item)
    {
        while (true)
        {
            //执行任务
            $this->execute($item);

            //执行一次
            if ($item['time'] == 0) break;

            //CPU休息
            sleep($item['time']);
        }
        exit;
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
            catch (\Throwable $exception)
            {
                $type = 'appException';
                Error::report($type, $exception);
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
        $nextExecTime = 0;
        while (true)
        {
            if (!$nextExecTime) $nextExecTime = Helper::getCronNextDate($item['time']);
            $waitTime = (strtotime($nextExecTime) - time());
            if (!$waitTime)
            {
                $this->execute($item);
                echo (date('Y-m-d H:i:s')) . PHP_EOL;
                $nextExecTime = 0;
            }
            else
            {
                sleep($waitTime);
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
        //跟进任务类型执行
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

        //记录执行
        $text = "this worker {$item['alas']}(pid:{$item['pid']})";
        Log::writeInfo("$text is executed");

        //检查进程存活
        $status = $this->wts->getProcessStatus('manager');
        if (!$status)
        {
            Helper::showInfo("listened exit command, $text is safely exited", true);
        }
    }

    /**
     * 常驻进程
     */
    private function daemonWait()
    {
        //进程标题
        Helper::cli_set_process_title(Env::get('prefix'));

        //输出信息
        $pid = getmypid();
        $text = "this manager(pid:{$pid})";
        if (!Env::get('daemon')) Helper::showInfo("$text is start");;

        //挂起进程
        while (true)
        {
            //CPU休息
            sleep(1);

            //接收命令status/stop
            $this->commander->waitCommandForExecute(2, function ($command) use ($text) {
                $commandType = $command['type'];
                switch ($commandType)
                {
                    case 'status':
                        $this->commander->send([
                            'type' => 'status',
                            'msgType' => 1,
                            'status' => $this->getReport(),
                        ]);
                        break;
                    case 'stop':
                        Helper::showInfo("listened exit command, $text is safely exited", true);
                        break;
                }
            }, $this->startTime);

            //检查进程
            if (Env::get('canAutoRec'))
            {
                $this->getReport(true);
                if ($this->autoRecEvent)
                {
                    $this->autoRecEvent = false;
                }
            }
        }
    }

    /**
     * 获取报告
     * @param bool $output
     * @return array
     * @throws
     */
    private function getReport($output = false)
    {
        $report = $this->workerStatus($this->taskCount);
        foreach ($report as $key => $item)
        {
            if ($item['status'] == 'stop' && Env::get('canAutoRec'))
            {
                $argv = Helper::getCliInput();
                $this->forkItemExec($argv);
                if ($output)
                {
                    $this->autoRecEvent = true;
                    Log::writeInfo('the worker ' . $item['pid'] . ' is stop,try to fork new one');
                }
            }
        }

        return $report;
    }

    /**
     * master进程等待结束退出
     */
    private function masterWaitExit()
    {
        $i = 10;
        while ($i--)
        {
            //CPU休息
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
     * 查看进程状态
     * @param int $count
     * @return array
     */
    private function workerStatus($count)
    {
        //构建报告
        $report = $infoData = [];
        $tryTotal = 10;
        while ($tryTotal--)
        {
            sleep(1);
            $infoData = $this->wts->getProcessInfo();
            if ($count == count($infoData)) break;
        }

        //组装数据
        $pid = getmypid();
        $prefix = Env::get('prefix');
        foreach ($infoData as $name => $item)
        {
            $report[] = [
                'pid' => $item['pid'],
                'name' => "{$prefix}_{$item['alas']}",
                'started' => $item['started'],
                'time' => $item['time'],
                'status' => $this->wts->getProcessStatus($name) ? 'active' : 'stop',
                'ppid' => $pid,
            ];
        }

        return $report;
    }
}