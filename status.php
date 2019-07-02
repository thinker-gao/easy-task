<?php
namespace EasyTask;

require './vendor/autoload.php';

require './src/Task.php';
require './src/Process.php';
require './src/Console.php';
require './src/Command.php';
require './src/SysMsg.php';


//初始化
$task = new Task();

//启动任务
$task->status();









