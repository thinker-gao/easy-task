<?php

require '../vendor/autoload.php';

use \EasyTask\Task;

class Mail
{
    public function send()
    {
        echo '1024' . PHP_EOL;
        echo date('Y-m-d H:i:s').PHP_EOL;
    }
}

//获取命令行输入参数
$cliArgv = $_SERVER['argv'];
$command = empty($cliArgv['1']) ? '' : $cliArgv['1'];  //获取输入的是start,status,stop中的哪一个
$isForce = !empty($cliArgv['2']) && $cliArgv['2'] == '-f' ? true : false;  //获取是否要强制停止

//配置定时任务
$task = new Task();
$task->setDaemon(false)
    ->setCloseInOut(false)
    ->setWriteLog(false, true)
    ->addClass('Mail', 'send', 'req', 1, 1);

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
