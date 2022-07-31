<?php

namespace EasyTask\Process;

use EasyTask\Command;
use EasyTask\Env;
use EasyTask\Error;
use EasyTask\Helper;
use \Event as Event;
use \EventBase as EventBase;
use \EventConfig as EventConfig;
use \Exception as Exception;
use \Throwable as Throwable;

/**
 * Class Process
 * @package EasyTask\Process
 */
abstract class Process
{
    /**
     * 进程启动时间
     * @var int
     */
    protected $startTime;

    /**
     * 任务总数
     * @var int
     */
    protected $taskCount;

    /**
     * 任务列表
     * @var array
     */
    protected $taskList;

    /**
     * 进程命令管理
     * @var Command
     */
    protected $commander;

    /**
     * 构造函数
     * @param array $taskList
     */
    public function __construct($taskList)
    {
        $this->startTime = time();
        $this->taskList = $taskList;
        $this->setTaskCount();
        $this->commander = new Command();
    }

    /**
     * 开始运行
     */
    abstract public function start();

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
        $force = $force ?: true;
        $this->commander->send([
            'type' => 'stop',
            'force' => $force,
            'msgType' => 2
        ]);
    }

    /**
     * 初始化任务数量
     */
    protected function setTaskCount()
    {
        $count = 0;
        foreach ($this->taskList as $key => $item) {
            $count += (int)$item['used'];
        }
        $this->taskCount = $count;
    }

    /**
     * 检查是否可写标准输出日志
     * @return bool
     */
    protected function canWriteStd()
    {
        return Env::get('daemon') && !Env::get('closeStdOutLog');
    }

    /**
     * 执行任务代码
     * @param array $item
     * @throws
     */
    protected function execute($item)
    {
        //根据任务类型执行
        $daemon = Env::get('daemon');

        //Std_Start
        if ($this->canWriteStd()) ob_start();
        try {
            $type = $item['type'];
            switch ($type) {
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
                    $result = shell_exec($item['command']);
                    if ($result) {
                        echo $result . PHP_EOL;
                        Helper::output($result);
                    }
                    if ($result === false) {
                        $errorResult = 'failed to execute ' . $item['alas'] . ' task' . PHP_EOL;
                        Helper::output($errorResult);
                    }
            }

        } catch (Exception $exception) {
            if (Helper::isWin()) {
                Helper::showException($exception, 'exception', !$daemon);
            } else {
                if (!$daemon) throw $exception;
                Helper::writeLog(Helper::formatException($exception));
            }
        } catch (Throwable $exception) {
            if (Helper::isWin()) {
                Helper::showException($exception, 'exception', !$daemon);
            } else {
                if (!$daemon) throw $exception;
                Helper::writeLog(Helper::formatException($exception));
            }
        }

        //Std_End
        if ($this->canWriteStd()) {
            $stdChar = ob_get_contents();
            if ($stdChar) Helper::saveStdChar($stdChar);
            ob_end_clean();
        }

        //检查常驻进程存活
        $this->checkDaemonForExit($item);
    }

    /**
     * 执行任务
     * @param array $item
     * @throws Throwable
     */
    protected function executeInvoker($item)
    {
        if ($item['time'] === 0) {
            $this->invokerByDirect($item);
        } else {
            Env::get('canEvent') ? $this->invokeByEvent($item) : $this->invokeByDefault($item);
        }
    }

    /**
     * 通过Event事件执行
     * @param array $item
     */
    protected function invokeByEvent($item)
    {
        //创建Event事件
        $eventConfig = new EventConfig();
        $eventBase = new EventBase($eventConfig);
        $event = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, function () use ($item) {
            try {
                $this->execute($item);
            } catch (Throwable $exception) {
                $type = 'exception';
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
     * 普通执行
     * @param array $item
     * @throws Throwable
     */
    protected function invokerByDirect($item)
    {
        $this->execute($item);
        exit;
    }

    /**
     * 主进程等待结束退出
     */
    protected function masterWaitExit()
    {
        $i = $this->taskCount + 30;
        while ($i--) {
            //接收汇报
            $this->commander->waitCommandForExecute(1, function ($report) {
                if ($report['type'] == 'status' && $report['status']) {
                    Helper::showTable($report['status']);
                }
            }, $this->startTime);

            //CPU休息
            Helper::sleep(1);
        }
        Helper::showInfo('this cpu is too busy,please use status command try again');
        exit;
    }
}
