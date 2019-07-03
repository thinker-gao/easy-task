EasyTask -- 原生常驻内存定时任务
===============

> 运行环境：linux+PHP7.0以上，强烈推荐PHP7.1以上，PHP7.1拥有异步信号管理，不再依赖ticks特性，性能更好。

## <h2>【一】 安装PHP扩展</h2>

* pcntl(一般默认自带，提供多进程管理能力)
* posix(一般默认自带，提供进程信息能力)
* sysvmsg(需要自行安装，提供Linux IPC消息队列能力)
* 推荐使用[宝塔集成环境](http://www.bt.cn/)一键安装php扩展

## <h2>【二】 Composer安装</h2>

~~~
  composer require easy-task/easy-task
~~~

~~~
  "require": {
    "easy-task/easy-task": "dev-master"
  }
~~~

## <h2>【三】 代码案例</h2>

1.创建一个闭包函数每隔10秒执行一次
~~~
//实例化task类
$task = new Task();

//设置task常驻内存
$task->setDaemon(true);

//添加一个闭包函数10秒请求一次某个文章链接,增加这个文章的访问量
//参数解释:
//request是为这个闭包函数起的别名
//10是这个闭包函数10秒执行1次
//1是这个闭包函数使用1个进程来执行
$task->addFunction(function () {
    $url = 'https://www.gaojiufeng.cn/?id=243';
    @file_get_contents($url);
}, 'request', 10, 1);

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
