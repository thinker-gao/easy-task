<?php

require '../vendor/autoload.php';

use \EasyTask\Task;

//$p = 'a';
//
//var_dump(\EasyTask\Helper::getFormatLogFilePath($p));
//
//die();


//获取命令行输入参数
$cliArgv = $_SERVER['argv'];
$command = empty($cliArgv['1']) ? '' : $cliArgv['1'];  //获取输入的是start,status,stop中的哪一个
$isForce = !empty($cliArgv['2']) && $cliArgv['2'] == '-f' ? true : false;  //获取是否要强制停止

//配置定时任务
$task = new Task();
$task->setDaemon(true)
    //->setCloseInOut(true)
    ->setWriteLog(true, false)
    ->addFunc(function () {
        $url = 'https://www.gaojiufeng.cn/?id=271';
        file_get_contents($url);
    }, 'request', 10, 2);;

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
