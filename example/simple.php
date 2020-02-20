<?php
include 'D:/wwwroot/EasyTask/vendor/autoload.php';

use \EasyTask\Task;

//获取命令行输入参数
$cliArgv = $_SERVER['argv'];
$command = empty($cliArgv['1']) ? '' : $cliArgv['1'];  //获取输入的是start,status,stop中的哪一个
$isForce = !empty($cliArgv['2']) && $cliArgv['2'] == '-f' ? true : false;  //获取是否要强制停止

//实例化Task
$task = new Task();

//设置记录日志,当日志存在异常影响代码执行时抛出到外部
$task->setIsWriteLog(true);

$task->setThrowExcept(true);

//设置运行时常驻内存
$task->setDaemon(true);

//添加含税
$task->addFunc(function () {

    $da = [];
    $da['1'] = 1;
    if($da['b'])
    {
        //echo '1133' . PHP_EOL;
    }

    //echo '1122' . PHP_EOL;

}, 'test1', 5, 1);

//根据命令执行
if ($command == 'start')
{
    $task->start();
}
elseif ($command == 'status')
{
    $task->status();
}
elseif ($command == 'stop')
{
    $task->stop($isForce);
}
else
{
    exit('This is command is not exists:' . $command . PHP_EOL);
}


