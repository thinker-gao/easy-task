<?php
namespace EasyTask\Process;

use EasyTask\Wts;
use EasyTask\Wpc;
use EasyTask\Env;
use EasyTask\Helper;
use \Exception as Exception;
use \Throwable as Throwable;

/**
 * Class Win
 * @package EasyTask\Process
 */
class Win extends Process
{
    /**
     * Wts服务
     * @var Wts
     */
    protected $wts;

    /**
     * 虚拟进程列表
     * @var array
     */
    protected $workerList;

    /**
     * 实体进程容器
     * @var array
     */
    protected $wpcContainer;

    /**
     * AutoRec事件
     * @var bool
     */
    protected $autoRecEvent;

    /**
     * 构造函数
     * @param array $taskList
     */
    public function __construct($taskList)
    {
        $this->wts = new Wts();
        parent::__construct($taskList);
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
            Helper::showError('unexpected error, process has been allocated');
        }
    }

    /**
     * 启动检查
     */
    protected function checkForRun()
    {
        if (!Env::get('phpPath'))
        {
            Helper::showError('please use setPhpPath api to set phpPath');
        }
        if (!$this->chkCanStart())
        {
            Helper::showError('please close the running process first');
        }
    }

    /**
     * 检查进程
     * @return bool
     */
    protected function chkCanStart()
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
     * @throws Exception|Throwable
     */
    protected function executeByProcessName($name)
    {
        switch ($name)
        {
            case 'master':
                $this->master();
                break;
            case 'manager':
                $this->manager();
                break;
            default:
                $this->invoker($name);
        }
    }

    /**
     * 构建任务
     */
    protected function make()
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
     * 主进程
     * @throws Exception
     */
    protected function master()
    {
        //创建常驻进程
        $this->forkItemExec();

        //查询状态
        $i = $this->taskCount + 15;
        while ($i--)
        {
            $status = $this->wts->getProcessStatus('manager');
            if ($status)
            {
                $this->status();
                break;
            }
            Helper::sleep(1);
        }
    }

    /**
     * 常驻进程
     */
    protected function manager()
    {
        //分配子进程
        $this->allocate();

        //后台常驻运行
        $this->daemonWait();
    }

    /**
     * 分配子进程
     */
    protected function allocate()
    {
        //清理进程信息
        $this->wts->cleanProcessInfo();

        foreach ($this->taskList as $key => $item)
        {
            //提取参数
            $used = $item['used'];

            //根据Worker数创建子进程
            for ($i = 0; $i < $used; $i++)
            {
                $this->joinWpcContainer($this->forkItemExec());
            }
        }
    }

    /**
     * 注册实体进程
     * @param Wpc $wpc
     */
    protected function joinWpcContainer($wpc)
    {
        $this->wpcContainer[] = $wpc;
        foreach ($this->wpcContainer as $key => $wpc)
        {
            if ($wpc->hasExited())
            {
                unset($this->wpcContainer[$key]);
            }
        }
    }

    /**
     * 创建任务执行子进程
     * @return Wpc
     */
    protected function forkItemExec()
    {
        $wpc = null;
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
            if (!$pid) Helper::showError('create process failed,please try again', true);
        }
        catch (Exception $exception)
        {
            Helper::showError(Helper::convert_char($exception->getMessage()), true);
        }

        return $wpc;
    }

    /**
     * 执行器
     * @param string $name 任务名称
     * @throws Throwable
     */
    protected function invoker($name)
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
        $title = Env::get('prefix') . '_' . $item['alas'];
        Helper::showInfo("this worker $title is start");

        //设置进程标题
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
        $this->executeInvoker($item);
    }

    /**
     * 通过默认定时执行
     * @param array $item 执行项目
     * @throws Throwable
     */
    protected function invokeByDefault($item)
    {
        while (true)
        {
            //CPU休息
            Helper::sleep($item['time']);

            //执行任务
            $this->execute($item);
        }
        exit;
    }

    /**
     * 检查常驻进程是否存活
     * @param array $item
     */
    protected function checkDaemonForExit($item)
    {
        //检查进程存活
        $status = $this->wts->getProcessStatus('manager');
        if (!$status)
        {
            $text = Env::get('prefix') . '_' . $item['alas'];
            Helper::showInfo("listened exit command, this worker $text is exiting safely", true);
        }
    }

    /**
     * 后台常驻运行
     */
    protected function daemonWait()
    {
        //进程标题
        Helper::cli_set_process_title(Env::get('prefix'));

        //输出信息
        $text = "this manager";
        Helper::showInfo("$text is start");;

        //挂起进程
        while (true)
        {
            //CPU休息
            Helper::sleep(1);

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
                        Helper::showInfo("listened status command, $text is reported");
                        break;
                    case 'stop':
                        if ($command['force']) $this->stopWorkerByForce();
                        Helper::showInfo("listened exit command, $text is exiting safely", true);
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
    protected function getReport($output = false)
    {
        $report = $this->workerStatus($this->taskCount);
        foreach ($report as $key => $item)
        {
            if ($item['status'] == 'stop' && Env::get('canAutoRec'))
            {
                $this->joinWpcContainer($this->forkItemExec());
                if ($output)
                {
                    $this->autoRecEvent = true;
                    Helper::showInfo("the worker {$item['name']}(pid:{$item['pid']}) is stop,try to fork a new one");
                }
            }
        }

        return $report;
    }

    /**
     * 查看进程状态
     * @param int $count
     * @return array
     */
    protected function workerStatus($count)
    {
        //构建报告
        $report = $infoData = [];
        $tryTotal = 10;
        while ($tryTotal--)
        {
            Helper::sleep(1);
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

    /**
     * 强制关闭所有进程
     */
    protected function stopWorkerByForce()
    {
        foreach ($this->wpcContainer as $wpc)
        {
            try
            {
                $wpc->stop(2);
            }
            catch (Exception $exception)
            {
                Helper::showError(Helper::convert_char($exception->getMessage()), false);
            }
        }
    }
}