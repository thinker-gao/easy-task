<?php

require './src/Task.php';
require './src/Process.php';


//初始化
$task = new \EasyTask\task();

$task->setDaemon(true);

//传入Redis
/*
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$task->setRedis($redis);
*/

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

//输出任务列表
//$task->getList();

$task->start();









