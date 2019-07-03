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
        echo 'Success1' . PHP_EOL;
    }

    public static function recv()
    {
        echo 'Success2' . PHP_EOL;
    }
}

//初始化Task对象
$task = new Task();
try
{
    //设置常驻内存
    $task->setDaemon(true);

    //添加执行普通类
    $task->addClass(Sms::class, 'send', 'sendsms1', 20, 1);

    //添加执行静态类
    $task->addClass(Sms::class, 'recv', 'sendsms2', 20, 1);

    //添加执行闭包函数
    $task->addFunction(function () {
        echo 'Success3' . PHP_EOL;
    }, 'fucn', 20, 1);

    //启动任务
    $task->start();
}
catch (\Exception $exception)
{
    //错误输出
    var_dump($exception->getMessage());
}











