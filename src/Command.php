<?php
namespace EasyTask;

class Command
{
    /**
     * 通信依赖
     */
    private $sysMsg;

    /**
     * 通信队列
     */
    private $sysQueue;

    /**
     * 构造函数
     * @param int $ipcKey
     */
    public function __construct($ipcKey)
    {
        $this->sysMsg = new SysMsg();
        $this->sysQueue = $this->sysMsg->setQueue($ipcKey);
    }

    /**
     * 发送命令
     * @param $msgType
     * @param $command
     */
    public function send($msgType, $command)
    {
        var_dump('send');
        var_dump($msgType, $command);
        $command['time'] = time();
        $this->sysMsg->sendMessage($this->sysQueue, $msgType, $command);
    }

    /**
     * 接收命令
     * @param $msgType
     * @param $command
     * @return bool
     */
    public function receive($msgType, &$command)
    {
        var_dump('rec');
        var_dump($msgType, $command);
        return $this->sysMsg->receMesage($this->sysQueue, $msgType, $command);
    }

    /**
     * 移除命令通信
     */
    public function remove()
    {
        $this->sysMsg->removeQueue($this->sysQueue);
    }
}