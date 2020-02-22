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
        $runPath = Helper::getWin32Path();
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
     * 通过进程锁执行
     * @param \Closure $func
     */
    private function lockToExecute($func)
    {
        $fp = fopen($this->lockFile, "r");
        if (flock($fp, LOCK_EX))
        {
            $func();
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    /**
     * 注册进程名称
     * @param string $name
     */
    public function joinProcess($name)
    {
        $this->processNames[] = $name;
        $runPath = Helper::getWin32Path();
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
        $runPath = Helper::getWin32Path();
        return $runPath . md5($name);
    }

    /**
     * 获取进程保存信息的文件名
     * @return string
     */
    public function getProcessInfoFile()
    {
        $runPath = Helper::getWin32Path();
        $infoFile = md5(date('Y-m-d') . __FILE__);
        return $runPath . $infoFile;
    }

    /**
     * 获取进程状态
     * @param string $name 进行名称
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
            $fileSize = filesize($file);
            $oldInfo = $fileSize ? fread($fp, $fileSize) : [];
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
     * @param array $info
     */
    public function saveProcessInfo($info)
    {
        //加锁执行
        $this->lockToExecute(function () use ($info) {

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
     * @param \Closure $func
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