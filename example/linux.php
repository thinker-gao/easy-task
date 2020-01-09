<?php
require '../vendor/autoload.php';

use \EasyTask\Task;


//实例化Task
$task = new Task();

//设置记录日志,当日志存在异常影响代码执行时抛出到外部
$task->setWriteLog(true, true);

//设置运行时常驻内存
$task->setDaemon(true);

//关闭标准输入输出(关闭后程序运行时任何输出不会显示到终端)
$task->setCloseInOut(false);

//设置文件掩码
$task->setUmask(0);

//设置文件工作目录
$task->setChdir(true);

//添加定时任务
$task->addFunc(function () {
    file_put_contents('./1.txt', time());
}, 'task1', 10, 1);

//启动全部定时任务
$task->start();



