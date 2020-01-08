<?php
require '../src/Task.php';
require '../src/Helper.php';
require '../src/Command.php';
require '../src/Console.php';
require '../src/Error.php';
require '../src/Process/Linux.php';

//实例化Task
$task = new \EasyTask\Task();

$task->setWriteLog(true, true);

$task->addFunc(function () {
    echo time() . PHP_EOL;
}, 'test', 10, 1);

$task->setDaemon(true);
$task->setInOut(true);

$task->start();
