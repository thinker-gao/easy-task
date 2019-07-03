<?php
namespace EasyTask;

class Task
{
    /**
     * @var bool 是否守护进程
     */
    private $daemon = false;

    /**
     * @var bool 是否卸载工作区
     */
    private $isChdir = false;

    /**
     * @var bool 关闭标准输入输出
     */
    private $closeInOut = false;

    /**
     * @var bool 支持异步信号
     */
    private $canAsync = false;

    /**
     * @var string 任务前缀
     */
    private $prefix = 'EasyTask';

    /**
     * @var int $ipcKey 进程通信Key
     */
    private $ipcKey = '';

    /**
     * @var array 任务列表
     */
    private $taskList = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        //异步支持
        $this->canAsync = function_exists('pcntl_async_signals');

        //初始化ipcKey
        $this->ipcKey = ftok(dirname(__FILE__), 'T');
    }

    /**
     * 拦截器
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 设置守护
     * @param bool $daemon 是否守护
     * @return $this
     */
    public function setDaemon($daemon = false)
    {
        $this->daemon = $daemon;
        return $this;
    }

    /**
     * 卸载工作区
     * @param bool $isChdir 是否卸载所在工作区
     * @return $this
     */
    public function setChdir($isChdir = false)
    {
        $this->isChdir = $isChdir;
        return $this;
    }

    /**
     * 设置关闭标准输入输出
     * @param bool $closeInOut
     * @return $this
     */
    public function setInOut($closeInOut = false)
    {
        $this->closeInOut = $closeInOut;
        return $this;
    }

    /**
     * 设置任务前缀
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix = '')
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 设置进程通信Key
     * @param int $ipcKey
     * @return $this
     */
    public function setIpcKey($ipcKey)
    {
        $this->ipcKey = $ipcKey;
        return $this;
    }

    /**
     * 新增匿名函数作为任务
     * @param \Closure $func 匿名函数
     * @param string $alas 任务别名
     * @param int $time 定时器间隔
     * @param int $used 定时器占用进程数
     * @return $this
     * @throws
     */
    public function addFunction($func, $alas = '', $time = 1, $used = 1)
    {
        //必须是匿名函数
        if (!($func instanceof \Closure))
        {
            Console::error('参数必须是匿名函数');
        }

        $this->taskList[] = [
            'type' => 0,
            'func' => $func,
            'alas' => $alas ? $alas : uniqid(),
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
        //检查类是否存在
        if (!class_exists($class))
        {
            Console::error("{$class}类不存在");
        }

        try
        {
            $reflect = new \ReflectionClass($class);
            if (!$reflect->hasMethod($func))
            {
                Console::error("{$class}类的方法{$func}不存在");
            }

            $method = new \ReflectionMethod($class, $func);
            if (!$method->isPublic())
            {
                Console::error("{$class}类的方法{$func}必须是可访问的");
            }

            $this->taskList[] = [
                'type' => $method->isStatic() ? 1 : 2,
                'func' => $func,
                'alas' => $alas ? $alas : uniqid(),
                'time' => $time,
                'used' => $used,
                'class' => $class,
            ];
        }
        catch (\ReflectionException $exception)
        {
            Console::error($exception->getMessage());
        }

        return $this;
    }

    /**
     * 开始运行
     */
    public function start()
    {
        if (!$this->taskList)
        {
            return false;
        }
        (new Process($this))->start();
    }

    /**
     * 运行状态
     */
    public function status()
    {
        (new Process($this))->status();
    }

    /**
     * 停止运行
     * @param bool $force 是否强制
     */
    public function stop($force)
    {
        (new Process($this))->stop($force);
    }
}