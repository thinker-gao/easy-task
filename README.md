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

3.1 创建一个闭包函数每隔10秒执行一次
~~~
//初始化Task对象
$task = new Task();
try
{
    //设置常驻内存
    $task->setDaemon(true);

    //设置闭包函数任务
    $task->addFunction(function () {
        $url = 'https://www.gaojiufeng.cn/?id=243';
        @file_get_contents($url);
    }, 'request', 10, 2);

    //启动任务
    $task->start();
}
catch (\Exception $exception)
{
    //错误输出
    var_dump($exception->getMessage());
}
~~~

输出结果:
~~~
┌─────┬──────────────────┬─────────────────────┬───────┬────────┬──────┐
│ PID │    TASK_NAME     │       STARTED       │ TIMER │ STATUS │ PPID │
├─────┼──────────────────┼─────────────────────┼───────┼────────┼──────┤
│ 134 │ EasyTask_request │ 2019-07-03 10:13:19 │  10s  │ active │ 133  │
│ 135 │ EasyTask_request │ 2019-07-03 10:13:19 │  10s  │ active │ 133  │
└─────┴──────────────────┴─────────────────────┴───────┴────────┴──────┘
~~~

代码解释: 
addFunction函数第一个参数传递闭包函数，编写自己需要的逻辑，第二个参数是任务的别名，在输出结果中会体现，第三个参数是每隔多少秒执行1次，第四个参数是启动几个进程来执行

3.2 每隔20秒执行一次类的方法(同时支持静态方法)
~~~
class Sms
{
    public function send()
    {
        echo 'Success' . PHP_EOL;
    }
}

//初始化Task对象
$task = new Task();
try
{
    //设置常驻内存
    $task->setDaemon(true);

    //设置闭包函数任务
    $task->addClass(Sms::class, 'send', 'sendsms', 20, 1);

    //启动任务
    $task->start();
}
catch (\Exception $exception)
{
    //错误输出
    var_dump($exception->getMessage());
}
~~~
## 文档



## 参与开发



## 版权信息

遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2019 

All rights reserved。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
