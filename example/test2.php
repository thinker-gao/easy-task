<?php

$time = time();

$data = [];

if($data['a'])
{
    file_put_contents('D:\wwwroot\EasyTask\example\2.txt', $time);
}

file_put_contents('D:\wwwroot\EasyTask\example\1.txt', $time);