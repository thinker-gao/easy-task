<?php
namespace EasyTask;

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
        //创建目录
        $path = Helper::getCsgPath();
        if (!is_dir($path))
        {
            mkdir($path, 0777);
        }

        //创建文件
        $file = $path . '%s.txt';
        $this->msgFile = sprintf($file, md5(date('Y-m-d') . __FILE__));
        if (!file_exists($this->msgFile))
        {
            if (!file_put_contents($this->msgFile, '[]', LOCK_EX))
            {
                Helper::showError('crate process queue msgFile failed');
            }
        }
    }

    /**
     * 获取数据
     * @return array|mixed
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
     * @param $data
     */
    public function set($data)
    {
        file_put_contents($this->msgFile, json_encode($data), LOCK_EX);
    }

    /**
     * 投递数据
     * @param $command
     */
    public function push($command)
    {
        $data = $this->get();
        array_push($data, $command);
        $this->set($data);
    }

    /**
     * 发送命令
     * @param $command
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
     * @param \Closure $func 执行函数
     * @param int $timeOut 执行函数
     */
    public function waitCommandForExecute($msgType, $func, $timeOut = 5)
    {
        $command = '';
        $this->receive($msgType, $command);
        if (!$command || (!empty($command['time']) && (time() - $command['time']) > $timeOut))
        {
            return;
        }
        $func($command);
    }
}