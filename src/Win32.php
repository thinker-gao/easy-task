<?php
namespace EasyTask;

/**
 * Class Win32
 * @package EasyTask
 */
class Win32
{
    /**
     * 进程锁
     * @var string
     */
    private $lockFile;

    /**
     * 进程名称列表
     * @var array
     */
    private $processNames = [];

    /**
     * 构造函数
     * Win32 constructor.
     */
    public function __construct()
    {
        //创建运行时目录
        $runPath = Helper::getWin32LockPath();
        if (!is_dir($runPath))
        {
            mkdir($runPath, 0777, true);
        }

        //创建锁文件
        $lockFile = $this->lockFile = $runPath . 'lock';
        if (!file_exists($lockFile))
        {
            file_put_contents($lockFile, '');
        }

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
    public function regProcessName($name)
    {
        $this->processNames[] = $name;
        $runPath = Helper::getWin32LockPath();
        $file = $runPath . md5($name);
        if (!file_exists($file))
        {
            file_put_contents($file, '');
        }
    }

    /**
     * 获取进程文件名
     * @param string $name 进程名称
     * @return string
     */
    public function getProcessFile($name)
    {
        $runPath = Helper::getWin32LockPath();
        return $runPath . md5($name);
    }

    /**
     * 获取进程保存信息的文件名
     * @return string
     */
    public function getProcessInfoFile()
    {
        $runPath = Helper::getWin32LockPath();
        return $runPath . '888.txt';
    }

    public function formatProcessName($name)
    {
        $split = '___';
        if (strpos($name, $split) !== false)
        {
            $name_Split = explode($split, $name);
            $name = array_shift($name_Split);
        }
        return $name;
    }

    /**
     * 获取进程状态
     * @param $name
     * @return bool
     */
    public function getProcessStatus($name)
    {
        $file = $this->getProcessFile($name);
        $fp = fopen($file, "r");
        if (flock($fp, LOCK_EX | LOCK_NB))
        {
            return false;
        }
        return true;
    }

    /**
     * 获取进程信息
     * @return array
     */
    public function getProcessInfo()
    {
        $info = [];
        $file = $this->getProcessInfoFile();
        $fp = fopen($file, 'r');
        if (flock($fp, LOCK_EX))
        {
            $oldInfo = fread($fp, filesize($file));
            if ($oldInfo)
            {
                $info = json_decode($oldInfo, true);
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        return $info;
    }

    /**
     * 保存进程信息
     * @param $info
     */
    public function saveProcessInfo($info)
    {
        //提取信息参数
        $name = $info['name'];

        //打开文件
        $file = $this->getProcessInfoFile();
        $fp = fopen($file, 'w+');
        if (flock($fp, LOCK_EX))
        {
            //包装数据
            $fileSize = filesize($file);
            $oldInfo = $fileSize ? fread($fp, $fileSize) : [];
            $oldInfo = $oldInfo ? json_decode($oldInfo, true) : [];
            $oldInfo[$name] = $info;

            //写入数据
            fwrite($fp, json_encode($oldInfo));
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    /**
     * 为程序获取一个进程
     * @param \Closure $success 获取成功回调
     * @param \Closure $error 获取失败回调
     */
    public function getProcessIdExecute($success, $error)
    {
        $processNames = $this->processNames;
        foreach ($processNames as $name)
        {
            $file = $this->getProcessFile($name);
            $fp = fopen($file, 'w');
            if (flock($fp, LOCK_EX | LOCK_NB))
            {
                $success($name);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
        $error();
    }
}