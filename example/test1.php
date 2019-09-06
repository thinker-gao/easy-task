<?php
require '../vendor/autoload.php';

//实例化Task
$task = new \EasyTask\Task();

//提取命令行传参的命令
$argv = $_SERVER['argv'];
if (!empty($argv['1']))
{
    if ($argv['1'] == 'start')
    {
        //设置常驻内存
        $task->setDaemon(false);

        //添加闭包函数任务
        $task->addCommand('php D:\wwwroot\1.php', 'sms', 5, 1);
        $task->addCommand('php D:\wwwroot\2.php', 'email', 5, 1);

        //启动任务
        $task->start();
    }
    if ($argv['1'] == 'status')
    {
        //状态命令
        $task->status();
    }
    if ($argv['1'] == 'stop')
    {
        //停止命令
        $force = false;
        if (!empty($argv['2']) && $argv['2'] == '-f')
        {
            $force = true;
        }
        $task->stop($force);
    }
}
