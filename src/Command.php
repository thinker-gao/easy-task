<?php
namespace EasyTask;

use \Closure as Closure;

/**
 * Class Command
 * @package EasyTask
 */
class Command
{
    /**
     * 通讯文件
     */
    private $msgFile;

    /**
     * 构造函数
     * @throws
     */
    public function __construct()
    {
        $this->initMsgFile();
    }

    /**
     * 初始化文件
     */
    private function initMsgFile()
    {
        //创建文件
        $path = Helper::getCsgPath();
        $file = $path . '%s.csg';
        $this->msgFile = sprintf($file, md5(__FILE__));
        if (!file_exists($this->msgFile))
        {
            if (!file_put_contents($this->msgFile, '[]', LOCK_EX))
            {
                Helper::showError('failed to create msgFile');
            }
        }
    }

    /**
     * 获取数据
     * @return array
     * @throws
     */
    public function get()
    {
        $content = @file_get_contents($this->msgFile);
        if (!$content)
        {
            return [];
        }
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    /**
     * 重置数据
     * @param array $data
     */
    public function set($data)
    {
        file_put_contents($this->msgFile, json_encode($data), LOCK_EX);
    }

    /**
     * 投递数据
     * @param array $command
     */
    public function push($command)
    {
        $data = $this->get();
        array_push($data, $command);
        $this->set($data);
    }

    /**
     * 发送命令
     * @param array $command
     */
    public function send($command)
    {
        $command['time'] = time();
        $this->push($command);
    }

    /**
     * 接收命令
     * @param string $msgType 消息类型
     * @param mixed $command 收到的命令
     */
    public function receive($msgType, &$command)
    {
        $data = $this->get();
        if (empty($data)) {
            return;
        }
        foreach ($data as $key => $item)
        {
            if ($item['msgType'] == $msgType)
            {
                $command = $item;
                unset($data[$key]);
                break;
            }
        }
        $this->set($data);
    }

    /**
     * 根据命令执行对应操作
     * @param int $msgType 消息类型
     * @param Closure $func 执行函数
     * @param int $time 等待方时间戳
     */
    public function waitCommandForExecute($msgType, $func, $time)
    {
        $command = '';
        $this->receive($msgType, $command);
        if (!$command || (!empty($command['time']) && $command['time'] < $time))
        {
            return;
        }
        $func($command);
    }
}