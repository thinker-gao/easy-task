<?php
namespace EasyTask\Process;

use EasyTask\Command;
use \ArrayObject as ArrayObject;
use EasyTask\Env;
use EasyTask\Error;
use EasyTask\Exception\ErrorException;
use EasyTask\Helper;
use EasyTask\Thread;
use EasyTask\Win32;

/**
 * Class Win
 * @package EasyTask\Process
 */
class Win
{
    /**
     * @var Win32
     */
    private $win32;

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
     * 进程worker
     * @var array
     */
    private $workerList;

    /**
     * 构造函数
     * @throws
     * @var array  taskList
     */
    public function __construct($taskList)
    {
        $this->win32 = new Win32();
        $this->taskList = $taskList;
        $this->startTime = time();
        $this->commander = new Command();
    }

    /**
     * 开始运行
     */
    public function start()
    {
        //注册进程
        $this->regProcessName();

        //检查是否可运行
        if (!$this->chkCanStart())
        {
            Helper::showError('Please close the running process first');
        }

        //根据进程执行
        $this->win32->getProcessIdExecute(
            function ($name) {
                $this->executeByProcessName($name);
            },
            function () {
                Helper::showError('Unexpected error, process has been allocated');
            });
    }

    /**
     * 是否可启动
     * @return bool
     */
    private function chkCanStart()
    {
        $lineCount = 0;
        $workerList = $this->workerList;
        foreach ($workerList as $name => $item)
        {
            $status = $this->win32->getProcessStatus($name);
            if ($status)
            {
                $lineCount++;
            }
        }
        return $lineCount == count($workerList) ? false : true;
    }

    /**
     * 跟进进程名称执行任务
     * @param string $name
     */
    private function executeByProcessName($name)
    {
        switch ($name)
        {
            //主进程
            case 'master':
                $this->allocate();
                break;

            //守护进程
            case 'manager';
                $this->daemonWait();
                break;

            //worker进程
            default:
                $this->invoker($name);
        }
    }

    /**
     * 注册进程名称
     */
    private function regProcessName()
    {
        $list = ['master', 'manager'];
        foreach ($list as $name)
        {
            $this->win32->regProcessName($name);
        }
        foreach ($this->taskList as $key => $item)
        {
            //提取参数
            $alas = $item['alas'];
            $used = $item['used'];

            //根据Worker数分配进程
            for ($i = 0; $i < $used; $i++)
            {
                $name = $item['name'] = $alas . '___' . $i;
                $this->workerList[$name] = $item;
                $this->win32->regProcessName($name);
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
     */
    private function allocate()
    {
        //计算要分配的进程数
        $count = $this->getWorkerCount() + 1;

        //根据count数分配进程
        $argv = Helper::getFullArgv();
        for ($i = 0; $i < $count; $i++)
        {
            if (Env::get('daemon'))
            {
                //异步执行
                $cmd = 'start /b ' . $argv;
            }
            else
            {
                //同步执行
                $cmd = 'wmic process call create "' . $argv . '"';
            }

            //运行Cmd
            @pclose(@popen($cmd, 'r'));
        }

        //汇报执行情况
        Helper::showTable($this->workerStatus($count - 1));
    }

    /**
     * 获取worker数量
     * @return int|mixed
     */
    private function getWorkerCount()
    {
        $count = 0;
        foreach ($this->taskList as $key => $item)
        {
            $count += (int)$item['used'];
        }
        return $count;
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

        //提取任务
        $item = $taskDict[$name];

        //执行任务
        if (Env::get('canEvent') && $item['time'] != 0)
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
        $event = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, $this->execute($item));

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
        //进程标题
        @cli_set_process_title($item['alas']);

        //保存进程信息
        $pid = getmypid();
        $this->win32->saveProcessInfo([
            'pid' => $pid,
            'name' => $item['name'],
            'alas' => $item['alas'],
            'started' => date('Y-m-d H:i:s', $this->startTime),
            'timer' => $item['time']
        ]);

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

        //监听manager的命令
        $this->commander->waitCommandForExecute($pid, function ($command) {
            $commandType = $command['type'];
            if ($commandType == 'stop')
            {
                Helper::showError('Listen to exit command, the current process is safely exiting...');
            }
        }, 3600 * 24);
    }

    /**
     * 守护进程常驻
     */
    private function daemonWait()
    {
        //守护进程设置进程名
        @cli_set_process_title(Env::get('prefix'));

        //挂起进程
        while (true)
        {
            //CPU休息1秒
            sleep(1);

            //接收命令
            $this->commander->waitCommandForExecute(2, function ($command) {
                $commandType = $command['type'];
                switch ($commandType)
                {
                    //监听查询命令
                    case 'status':
                        $this->commander->send([
                            'type' => 'status',
                            'msgType' => 1,
                            'status' => $this->workerStatus($this->getWorkerCount()),
                        ]);
                        break;

                    //监听关闭命令(当前主进程关闭)
                    case 'stop':
                        $this->sendStopToWorker();
                        Helper::showError('Listen to exit command, the current process is safely exiting...');
                        break;
                }
            });
        }
    }

    /**
     * 向所有worker进程发送退出命令
     */
    private function sendStopToWorker()
    {
        $workers = $this->workerStatus($this->getWorkerCount());
        foreach ($workers as $work)
        {
            $this->commander->send([
                'type' => 'stop',
                'msgType' => $work['pid']
            ]);
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
                if ($report['type'] == 'status')
                {
                    Helper::showTable($report['status']);
                }
            });
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
        $report = $infoDict = [];
        while (true)
        {
            //Cpu休息
            sleep(1);
            $infoDict = $this->win32->getProcessInfo();
            if ($count == count($infoDict))
            {
                break;
            }
        }

        foreach ($infoDict as $name => $item)
        {
            //获取进程状态
            $status = $this->win32->getProcessStatus($name);
            $item['status'] = $status ? 'active' : 'stop';
            $item['name'] = $item['alas'];
            unset($item['alas']);

            //组装报告
            $report[] = $item;
        }

        return $report;
    }
}

