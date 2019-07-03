<?php
namespace EasyTask;

require './vendor/autoload.php';

require './src/Task.php';
require './src/Process.php';
require './src/Console.php';
require './src/Command.php';
require './src/SysMsg.php';


//初始化Task对象
$task = new Task();
try
{
    //设置常驻内存
    $task->setDaemon(true);

    //设置闭包函数任务
    $task->addFunction(function () {
        $url = 'https://www.gaojiufeng.cn/?id=243';
        file_get_contents($url);
    }, 'request', 10, 2);

    //启动任务
    $task->start();
}
catch (\Exception $exception)
{
    //错误输出
    var_dump($exception->getMessage());
}











