<?php
namespace EasyTask;

use \Com as Com;
use \Exception as Exception;

/**
 * Class Wpc
 * @package EasyTask
 */
class Wpc
{
    /**
     * Wpc实例
     * @var null
     */
    private $instance = null;

    /**
     * Wpc constructor.
     * @return $this
     */
    public function __construct()
    {
        $this->instance = new Com('Wpc.Core');
        return $this;
    }

    /**
     * 获取Com_Variant
     * @return Com
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * 设置进程文件
     * @param string $filename
     * @return $this
     * @throws Exception
     */
    public function setFile($filename)
    {
        $filename = realpath($filename);
        if (!file_exists($filename))
        {
            throw new Exception("the file:{$filename} is not exist");
        }
        $this->instance->SetFile($filename);
        return $this;
    }

    /**
     * 设置进程域
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $domain = (string)$domain;
        $this->instance->SetDomain($domain);
        return $this;
    }

    /**
     * 设置进程参数
     * @param string $argument
     * @return $this
     */
    public function setArgument($argument)
    {
        $argument = (string)$argument;
        $this->instance->SetArgument($argument);
        return $this;
    }

    /**
     * 设置进程是否带窗口
     * @param bool $set
     * @return $this
     */
    public function setNoWindow($set)
    {
        $set = (bool)$set;
        $this->instance->SetNoWindow($set);
        return $this;
    }

    /**
     * 设置启动进程的用户
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $username = (string)$username;
        $this->instance->SetUsername($username);
        return $this;
    }

    /**
     * 设置启动进程的密码
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $password = (string)$password;
        $this->instance->SetPassword($password);
        return $this;
    }

    /**
     * 设置进程风格
     * @param int $style (0.正常 1.隐藏 2.最小化 3.最大化)
     * @return $this
     */
    public function setStyle($style)
    {
        $style = (int)$style;
        $this->instance->SetStyle($style);
        return $this;
    }

    /**
     * 设置进程工作目录
     * @param string $path
     * @return $this
     * @throws Exception
     */
    public function setWorkDir($path)
    {
        $path = realpath($path);
        if (!is_dir($path))
        {
            throw new Exception("the path:{$path} is not exist");
        }
        $this->instance->SetWorkDir($path);
        return $this;
    }

    /**
     * 设置等待关联进程退出
     * @param int $timeOut 超时时间
     * @return $this
     * @throws Exception
     */
    public function setWaitForExit($timeOut = 1024)
    {
        $timeOut = (int)$timeOut;
        $this->instance->SetWaitForExit($timeOut);
        return $this;
    }

    /**
     * 获取进程ID
     * @return int
     */
    public function getPid()
    {
        return $this->instance->GetPid();
    }

    /**
     * 获取进程sessionId
     * @return int
     */
    public function getSessionId()
    {
        return $this->instance->GetSessionId();
    }

    /**
     * 获取程是否已经退出
     * @return bool
     */
    public function hasExited()
    {
        return $this->instance->HasExited();
    }

    /**
     * 获取进程名称
     * @return string
     */
    public function getProcessName()
    {
        return $this->instance->GetProcessName();
    }

    /**
     * 获取进程打开的资源句柄数
     * @return int
     */
    public function getHandleCount()
    {
        return $this->instance->GetHandleCount();
    }

    /**
     * 获取进程主窗口标题
     * @return string
     */
    public function getMainWindowTitle()
    {
        return $this->instance->GetMainWindowTitle();
    }

    /**
     * 获取进程启动时间
     * @return string
     */
    public function getStartTime()
    {
        return $this->instance->GetStartTime();
    }

    /**
     * 获取进程停止时间
     * @return string
     */
    public function getStopTime()
    {
        return $this->instance->GetStopTime();
    }

    /**
     * 启动进程
     * @return int 进程id
     */
    public function start()
    {
        return $this->instance->Start();
    }

    /**
     * 停止进程
     * @param int $force (1.正常停止 2.强制停止)
     */
    public function stop($force = 1)
    {
        $this->instance->Stop($force);
    }
}
