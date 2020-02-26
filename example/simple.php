<?php

file_put_contents('./1,txt', getcwd(), FILE_APPEND);

include 'D:/wwwroot/EasyTask/vendor/autoload.php';

use \EasyTask\Task;

//实例化Task
$task = new Task();


//添加含税
$task->addFunc(function () {

    file_get_contents('http://www.gaojiupan.cn/e/public/ViewClick/?classid=80&id=3085&addclick=1');

    echo 1122;

}, 'test1', 10, 3);

$task->stop();

//增加autoCliListen();



