<?php

use \EasyTask\Helper;

/**
 * 检查你的cron命令是否符合预期
 * 自动生成15条你的命令执行的时间顺序表
 */

//输入你的cron命令,例如在每天上午8点到11点的第3分钟和第15分钟执行
$command = '3,15 8-11 * * *';

//循环输出你的cron命令执行时间列表,输出15条
$i = 15;
while ($i--)
{
    static $date = 'now';
    $date = Helper::getCronNextDate($command, $date);

    echo $date . PHP_EOL;
}