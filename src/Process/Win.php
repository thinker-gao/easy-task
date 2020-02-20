<?php
namespace EasyTask\Process;

use EasyTask\Command;
use \ArrayObject as ArrayObject;
use EasyTask\Env;
use EasyTask\Error;
use EasyTask\Exception\ErrorException;
use EasyTask\Helper;
use EasyTask\Thread;

/**
 * Class Win
 * @package EasyTask\Process
 */
class Win
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
     * 线程执行记录
     * @var array
     */
    private $threadList = [];

    /**
     * 构造函数
     * @throws
     * @var array  taskList
     */
    public function __construct($taskList)
    {
        $this->taskList = $taskList;
        $this->startTime = time();
        $this->commander = new Command();
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

        //分配进程
        $this->allocate();

        //主进程守护
        if (Env::get('daemon')) $this->daemonWait();
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
        $this->initWaitExit();
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
     * 分配进程处理指令任务
     */
    private function allocate()
    {
        //分配线程池
        $pools = [];
        foreach ($this->taskList as $key => $item)
        {
            //提取参数
            $used = $item['used'];

            //根据used数分配线程
            for ($i = 0; $i < $used; $i++)
            {
                $pools[] = new Thread($item);
            }
        }

        //启动线程池
        foreach ($pools as $pool)
        {
            //启动并记录线程信息
            $pool->start();
            $name = $pool->item['alas'];
            $time = $pool->item['time'];
            $date = date('Y-m-d H:i:s');
            $prefix = Env::get('prefix');
            $pName = "{$prefix}_{$name}";
            $this->threadList[] = [
                'tid' => $pool->getThreadId(),
                'task_name' => $pName,
                'started' => $date,
                'timer' => $time . 's',
                'ttid' => $pool->getThreadPid(),
                'object' => $pool,
            ];
        }

        //汇报执行情况
        Helper::showTable($this->threadStatus(), false);
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
                    //监听启动命令(当前主进程关闭,避免多开)
                    case 'start':
                        if ($command['time'] > $this->startTime)
                        {
                            Helper::showError('Duplicate process detected, the current process is safely exiting...');
                        }
                        break;

                    //监听查询命令(汇报线程状态)
                    case 'status':
                        $this->commander->send([
                            'type' => 'status',
                            'msgType' => 1,
                            'status' => $this->threadStatus(),
                        ]);
                        break;

                    //监听关闭命令(当前主进程关闭)
                    case 'stop':
                        Helper::showError('Listen to exit command, the current process is safely exiting...');
                        break;
                }
            });
        }
        var_dump(111);
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
     * @return array
     */
    private function threadStatus()
    {
        $report = [];
        foreach ($this->threadList as $key => $item)
        {
            //提取参数
            $object = $item['object'];

            //检查线程状态
            $status = $object->isRunning();
            $item['status'] = $status ? 'active' : 'stop';

            //组装返回
            unset($item['object']);
            $report[] = $item;
        }
        return $report;
    }
}

