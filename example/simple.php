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

//$task->setPhpPath('D:\php-7.0.32-Win32-VC14-x64\1.exe');

//设置运行时常驻内存
$task->setDaemon(false);

//添加含税
$task->addFunc(function () {

    file_get_contents('https://www.gaojiufeng.cn/?id=289');

}, 'test1', 3, 5);

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


