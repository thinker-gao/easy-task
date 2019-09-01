<?php
require 'D:\wwwroot\EasyTask/vendor/autoload.php';

class Tools
{
    public function do1()
    {
        echo '1122' . PHP_EOL;
    }
}

//实例化Task
$task = new \EasyTask\Task();

//提取命令行传参的命令
$argv = $_SERVER['argv'];
if (!empty($argv['1']))
{
    if ($argv['1'] == 'start')
    {
        $task->setDaemon(false);
        $task->addClass('Tools', 'do1', 'test', 5, 2);
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
