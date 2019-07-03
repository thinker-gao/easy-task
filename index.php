<?php
namespace EasyTask;

require './vendor/autoload.php';

require './src/Task.php';
require './src/Process.php';
require './src/Console.php';
require './src/Command.php';
require './src/SysMsg.php';


class Sms
{
    public function send()
    {
        echo 'Success' . PHP_EOL;
    }
}

//初始化Task对象
$task = new Task();
try
{
    //设置常驻内存
    $task->setDaemon(true);

    //设置闭包函数任务
    $task->addClass(Sms::class, 'send', 'sendsms', 20, 1);

    //启动任务
    $task->start();
}
catch (\Exception $exception)
{
    //错误输出
    var_dump($exception->getMessage());
}











