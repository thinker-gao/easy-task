<?php
namespace EasyTask;

class SysMsg
{
    /**
     * 创建一个队列
     * @param string $key 队列编号
     * @param int $perms 队列权限
     * @return resource
     */
    public static function setQueue($key, $perms = 0777)
    {
        return msg_get_queue($key, $perms);
    }

    /**
     * 向队列发送信息
     * @param resource $queue 队列句柄
     * @param int $msgType 消息类型
     * @param mixed $message 消息内容
     * @return boolean
     */
    public static function sendMessage($queue, $msgType, $message)
    {
        return msg_send($queue, $msgType, $message, true);
    }

    /**
     * 从队列接收信息
     * @param resource $queue
     * @param int $msgType
     * @param mixed $message
     * @return boolean
     */
    public static function receMesage($queue, $msgType, &$message)
    {
        return msg_receive($queue, 0, $msgType, 8192, $message, true, MSG_NOERROR);
    }

    /**
     * 移除消息队列
     * @param resource $queue
     * @return bool
     */
    public static function removeQueue($queue)
    {
        return msg_remove_queue($queue);
    }
}