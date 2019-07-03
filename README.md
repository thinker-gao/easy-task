EasyTask -- 原生常驻内存定时任务
===============

> 运行环境：linux+PHP7.0以上，强烈推荐PHP7.1以上，PHP7.1拥有异步信号管理，不再依赖ticks特性，性能更好。

## 需要安装的PHP扩展

* pcntl(一般默认自带，提供多进程管理能力)
* posix(一般默认自带)
* sysvmsg(需要自行安装，提供Linux IPC消息队列能力)
* 推荐使用[宝塔集成环境](http://www.bt.cn/)一键安装php扩展

## Composer安装

~~~
  "require": {
    "easy-task/easy-task": "dev-master"
  }
~~~

1.创建一个匿名函数定时任务
~~~
$task = new Task();
//设置常驻
$task->setDaemon(true);

//设置匿名函数任务,起个别名叫baby,10秒执行1次
$task->addFunction(function () {
    echo '1' . PHP_EOL;
}, 'baby', 10, 1);

//启动定时任务
$task->start();
~~~

## 文档



## 参与开发



## 版权信息

遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2019 

All rights reserved。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
