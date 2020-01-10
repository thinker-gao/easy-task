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
$task->setCloseInOut(true);

//设置文件掩码
$task->setUmask(0);

//添加定时任务(通过闭包方式)
$task->addFunc(function () {
    //开1个进程,每隔10秒执行1次
    file_put_contents('/mnt/d/wwwroot/EasyTask/example/sendSms.txt', time());
    $a++;
    //var_dump(1).PHP_EOL;
}, 'sendSms', 5, 1);


//添加定时任务(通过类和方法的方式)
class Mail
{
    public function send()
    {
        //开2个进程,每隔30秒执行1次
        file_put_contents('/mnt/d/wwwroot/EasyTask/example/sendMail.txt', time());

        //var_dump(2).PHP_EOL;
    }
}

$task->addClass('Mail', 'send', 'sendMail', 5, 2);

$command = 'php /www/web/orderAutoCancel.php';
$task->addCommand($command,'orderCancel',10,1);

//启动全部定时任务
$task->start();



