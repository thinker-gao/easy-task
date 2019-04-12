<?php
$dbms='mysql';     //数据库类型
$host='192.168.2.5'; //数据库主机名
$dbName='test';    //使用的数据库
$user='root';      //数据库连接用户名
$pass='';          //对应的密码
$dsn="$dbms:host=$host;dbname=$dbName";

try {
    $dbh = new PDO($dsn, $user, $pass); //初始化一个PDO对象
    $dbh = null;
} catch (PDOException $e) {
  

  mb_detect_encoding($content);
$content = json_encode($e->getMessage(),JSON_UNESCAPED_UNICODE);

var_dump($content);

}