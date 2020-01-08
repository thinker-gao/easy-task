<?php

require '../vendor/autoload.php';

//实例化Task
$task = new \EasyTask\Task();

$task->setWriteLog(true, true);

$task->addFunc(function () {

    $data = [];
    if($data['a'])
    {
        echo time() . PHP_EOL;
    }

}, 'test', 10, 1);

$task->setDaemon(true);
$task->setInOut(true);

$task->start();
