<?php
namespace EasyTask;

use \Closure as Closure;

/**
 * Class Wts
 * @package EasyTask
 */
class Wts
{
    /**
     * 进程锁
     * @var Lock
     */
    private $lock;

    /**
     * 进程名称列表
     * @var array
     */
    private $processNames = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        //创建进程锁
        $this->lock = new Lock();

        //创建进程信息文件
        $processFile = $this->getProcessInfoFile();
        if (!file_exists($processFile))
        {
            file_put_contents($processFile, '');
        }
    }

    /**
     * 注册进程名称
     * @param string $name
     */
    public function joinProcess($name)
    {
        $this->processNames[] = $name;
        $file = $this->getProcessFile($name);
        if (!file_exists($file))
        {
            file_put_contents($file, $name);
        }
    }

    /**
     * 获取进程文件名
     * @param string $name 进程名称
     * @return string
     */
    public function getProcessFile($name)
    {
        $runPath = Helper::getWinPath();
        return $runPath . md5($name) . '.win';
    }

    /**
     * 获取进程保存信息的文件名
     * @return string
     */
    public function getProcessInfoFile()
    {
        $runPath = Helper::getWinPath();
        $infoFile = md5(__FILE__) . '.win';
        return $runPath . $infoFile;
    }

    /**
     * 获取进程状态
     * @param string $name 进程名称
     * @return bool
     */
    public function getProcessStatus($name)
    {
        $file = $this->getProcessFile($name);
        if (!file_exists($file))
        {
            return false;
        }
        $fp = fopen($file, "r");
        if (flock($fp, LOCK_EX | LOCK_NB))
        {
            return false;
        }
        return true;
    }

    /**
     * 获取进程信息(非阻塞)
     * @return array
     */
    public function getProcessInfo()
    {
        $file = $this->getProcessInfoFile();
        $info = file_get_contents($file);
        $info = json_decode($info, true);
        return is_array($info) ? $info : [];
    }

    /**
     * 清理进程信息
     */
    public function cleanProcessInfo()
    {
        //加锁执行
        $this->lock->execute(function () {
            @file_put_contents($this->getProcessInfoFile(), '');
        });
    }

    /**
     * 保存进程信息
     * @param array $info
     */
    public function saveProcessInfo($info)
    {
        //加锁执行
        $this->lock->execute(function () use ($info) {

            //进程信息文件
            $name = $info['name'];
            $file = $this->getProcessInfoFile();

            //读取原数据
            $content = @file_get_contents($file);
            $oldInfo = $content ? json_decode($content, true) : [$name => $info];

            //追加数据
            $oldInfo ? $oldInfo[$name] = $info : $oldInfo = $info;
            file_put_contents($file, json_encode($oldInfo));
        });
    }

    /**
     * 分配进程
     * @param Closure $func
     * @return bool
     */
    public function allocateProcess($func)
    {
        $processNames = $this->processNames;
        foreach ($processNames as $name)
        {
            $file = $this->getProcessFile($name);
            $fp = fopen($file, 'w');
            if (flock($fp, LOCK_EX | LOCK_NB))
            {
                $func($name);
                flock($fp, LOCK_UN);
                return true;
            }
            fclose($fp);
        }
        return false;
    }
}