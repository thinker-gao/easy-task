<?php
namespace EasyTask;
require './src/Task.php';
require './src/Process.php';
require './src/Console.php';
require './src/Command.php';
require './src/SysMsg.php';


//初始化
$task = new Task();

//设置常驻
$task->setDaemon(false);

//设置匿名函数任务
$task->addFunction(function () {
    //echo '1' . PHP_EOL;
}, 'math', 10, 1);

//设置类的方法任务
class order
{
    public static function do()
    {
        // echo '2' . PHP_EOL;
    }
}

$task->addClass(order::class, 'do', 'english', 10, 1);

//启动任务
$task->start();









