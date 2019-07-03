<?php
namespace EasyTask;

require './vendor/autoload.php';

require './src/Task.php';
require './src/Process.php';
require './src/Console.php';
require './src/Command.php';
require './src/SysMsg.php';


class Sms
{
    public function send()
    {
        echo 'Success1' . PHP_EOL;
    }

    public static function recv()
    {
        echo 'Success2' . PHP_EOL;
    }
}

//初始化Task对象
$task = new Task();
try
{
    $task->setDaemon(true)
        ->setChdir(true)
        ->setInOut(true)
        ->setPrefix('ThinkTask')
        ->addClass(Sms::class, 'send', 'sendsms1', 20, 1)
        ->addClass(Sms::class, 'recv', 'sendsms2', 20, 1)
        ->addFunction(function () {
            echo 'Success3' . PHP_EOL;
        }, 'fucn', 20, 1)
        ->start();
}
catch (\Exception $exception)
{
    //错误输出
    var_dump($exception->getMessage());
}







