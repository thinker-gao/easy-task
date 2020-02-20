<?php
require 'D:/wwwroot/EasyTask/vendor/autoload.php';

use \EasyTask\Task;

//获取命令行输入参数
$cliArgv = $_SERVER['argv'];
$command = empty($cliArgv['1']) ? '' : $cliArgv['1'];  //获取输入的是start,status,stop中的哪一个
$isForce = !empty($cliArgv['2']) && $cliArgv['2'] == '-f' ? true : false;  //获取是否要强制停止


//实例化Task
$task = new Task();

//设置记录日志,当日志存在异常影响代码执行时抛出到外部
$task->setWriteLog(true, false);

//设置运行时常驻内存
$task->setDaemon(true);

//关闭标准输入输出(关闭后程序运行时任何输出不会显示到终端)
$task->setCloseInOut(false);

//设置文件掩码
$task->setUmask(0);


//添加定时任务(通过类和方法的方式)
class Mail
{
    public function send()
    {
        file_get_contents('https://www.gaojiufeng.cn/?id=292');
    }
}

$task->addClass('Mail', 'send', 'curl', 25, 10);

//$task->addCommand('php D:/wwwroot/EasyTask/example/b.php ');

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


