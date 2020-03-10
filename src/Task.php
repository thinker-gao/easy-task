<?php
namespace EasyTask;

use \Closure as Closure;
use EasyTask\Process\Linux;
use EasyTask\Process\Win;
use \ReflectionClass as ReflectionClass;
use \ReflectionMethod as ReflectionMethod;
use \ReflectionException as ReflectionException;

/**
 * Class Task
 * @package EasyTask
 */
class Task
{
    /**
     * 任务列表
     * @var array
     */
    private $taskList = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        //检查运行环境
        $currentOs = Helper::isWin() ? 1 : 2;
        Check::analysis($currentOs);
        $this->initialise($currentOs);
    }

    /**
     * 进程初始化
     * @param int $currentOs
     */
    private function initialise($currentOs)
    {
        //初始化基础配置
        Env::set('prefix', 'Task');
        Env::set('canEvent', Helper::canEvent());
        Env::set('currentOs', $currentOs);
        Env::set('canAsync', Helper::canAsyncSignal());
        Env::set('canAutoRec', true);
        if ($currentOs == 1)
        {
            Env::set('phpPath', Helper::getPhpPath());
        }
    }

    /**
     * 设置是否守护进程
     * @param bool $daemon
     * @return $this
     */
    public function setDaemon($daemon = false)
    {
        Env::set('daemon', $daemon);
        return $this;
    }

    /**
     * 设置任务前缀
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix = '')
    {
        Env::set('prefix', $prefix);
        return $this;
    }

    /**
     * 设置PHP执行路径(windows)
     * @param string $path
     * @return $this
     */
    public function setPhpPath($path)
    {
        $file = realpath($path);
        if (!file_exists($file))
        {
            Helper::showError("the path {$path} is not exists");
        }
        Env::set('phpPath', $path);
        return $this;
    }

    /**
     * 设置子进程自动恢复
     * @param bool $isRec
     */
    public function setAutoRecover($isRec = true)
    {
        Env::set('canAutoRec', $isRec);
    }

    /**
     * 设置时区
     * @param string $timeIdent
     * @return $this
     */
    public function setTimeZone($timeIdent)
    {
        date_default_timezone_set($timeIdent);
        return $this;
    }

    /**
     * 设置运行时目录
     * @param string $path
     * @return $this
     */
    public function setRunTimePath($path)
    {
        if (!is_dir($path))
        {
            Helper::showError("the path {$path} is not exist");
        }
        if (!is_writable($path))
        {
            Helper::showError("the path {$path} is not writeable");
        }
        Env::set('runTimePath', $path);
        return $this;
    }

    /**
     * 新增匿名函数作为任务
     * @param Closure $func 匿名函数
     * @param string $alas 任务别名
     * @param int $time 定时器间隔
     * @param int $used 定时器占用进程数
     * @return $this
     * @throws
     */
    public function addFunc($func, $alas = '', $time = 1, $used = 1)
    {
        if (!($func instanceof Closure))
        {
            Helper::showError('func must instanceof Closure');
        }
        Helper::checkTaskTime($time);
        $alas = $alas ? $alas : uniqid();
        $uniKey = md5($alas);
        $this->taskList[$uniKey] = [
            'type' => 1,
            'func' => $func,
            'alas' => $alas,
            'time' => $time,
            'used' => $used,
        ];

        return $this;
    }

    /**
     * 新增类作为任务
     * @param string $class 类名称
     * @param string $func 方法名称
     * @param string $alas 任务别名
     * @param int $time 定时器间隔
     * @param int $used 定时器占用进程数
     * @return $this
     * @throws
     */
    public function addClass($class, $func, $alas = '', $time = 1, $used = 1)
    {
        if (!class_exists($class))
        {
            Helper::showError("class {$class} is not exist");
        }
        try
        {
            $reflect = new ReflectionClass($class);
            if (!$reflect->hasMethod($func))
            {
                Helper::showError("class {$class}'s func {$func} is not exist");
            }
            $method = new ReflectionMethod($class, $func);
            if (!$method->isPublic())
            {
                Helper::showError("class {$class}'s func {$func} must public");
            }
            Helper::checkTaskTime($time);
            $alas = $alas ? $alas : uniqid();
            $uniKey = md5($alas);
            $this->taskList[$uniKey] = [
                'type' => $method->isStatic() ? 2 : 3,
                'func' => $func,
                'alas' => $alas,
                'time' => $time,
                'used' => $used,
                'class' => $class,
            ];
        }
        catch (ReflectionException $exception)
        {
            Helper::showException($exception);
        }

        return $this;
    }

    /**
     * 新增指令作为任务
     * @param string $command 指令
     * @param string $alas 任务别名
     * @param int $time 定时器间隔
     * @param int $used 定时器占用进程数
     * @return $this
     */
    public function addCommand($command, $alas = '', $time = 1, $used = 1)
    {
        if (!Helper::canExecuteCommand())
        {
            Helper::showError('please open the disabled function of popen and pclose');
        }
        Helper::checkTaskTime($time);
        $alas = $alas ? $alas : uniqid();
        $uniKey = md5($alas);
        $this->taskList[$uniKey] = [
            'type' => 4,
            'alas' => $alas,
            'time' => $time,
            'used' => $used,
            'command' => $command,
        ];

        return $this;
    }

    /**
     * 获取进程管理实例
     * @return  Win | Linux
     */
    private function getProcess()
    {
        $taskList = $this->taskList;
        $currentOs = Env::get('currentOs');
        if ($currentOs == 1)
        {
            return (new Win($taskList));
        }
        else
        {
            return (new Linux($taskList));
        }
    }

    /**
     * 开始运行
     * @throws
     */
    public function start()
    {
        if (!$this->taskList)
        {
            return;
        }

        //异常注册
        Error::register();

        //进程启动
        ($this->getProcess())->start();
    }

    /**
     * 运行状态
     * @throws
     */
    public function status()
    {
        ($this->getProcess())->status();
    }

    /**
     * 停止运行
     * @param bool $force 是否强制
     * @throws
     */
    public function stop($force = false)
    {
        ($this->getProcess())->stop($force);
    }
}