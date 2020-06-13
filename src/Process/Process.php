<?php
namespace EasyTask\Process;

use EasyTask\Env;
use EasyTask\Helper;
use \Exception as Exception;
use \Throwable as Throwable;

/**
 * Class Process
 * @package EasyTask\Process
 */
abstract class Process
{
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
        //发送关闭命令
        $this->commander->send([
            'type' => 'stop',
            'force' => $force,
            'msgType' => 2
        ]);
    }

    /**
     * 检查是否可写标准输出日志
     * @return bool
     */
    protected function canWriteStd()
    {
        return Env::get('daemon') && !Env::get('closeStdOutPutLog');
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

        //Win_Std_Start
        if (Helper::isWin() && $this->canWriteStd()) ob_start();
        try
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

        }
        catch (Exception $exception)
        {
            if (Helper::isWin())
            {
                Helper::showException($exception, 'exception', !$daemon);
            }
            else
            {
                if (!$daemon) throw $exception;
                Helper::writeLog(Helper::formatException($exception));
            }
        }
        catch (Throwable $exception)
        {
            if (Helper::isWin())
            {
                Helper::showException($exception, 'exception', !$daemon);
            }
            else
            {
                if (!$daemon) throw $exception;
                Helper::writeLog(Helper::formatException($exception));
            }
        }

        //Win_Std_End
        if (Helper::isWin() && $this->canWriteStd())
        {
            $stdChar = ob_get_contents();
            if ($stdChar) Helper::saveStdChar($stdChar);
            ob_end_clean();
        }

        //检查常驻进程存活
        $this->checkDaemonForExit($item);
    }

    /**
     * 主进程等待结束退出
     */
    protected function masterWaitExit()
    {
        $i = 15;
        while ($i--)
        {
            //CPU休息
            Helper::sleep(1);

            //接收汇报
            $this->commander->waitCommandForExecute(1, function ($report) {
                if ($report['type'] == 'status' && $report['status'])
                {
                    Helper::showTable($report['status']);
                }
            }, $this->startTime);
        }
        Helper::showInfo('the process is too busy,please use status command try again');
        exit;
    }
}