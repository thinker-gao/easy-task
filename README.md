<h4 style="text-align:center">EasyTask原生常驻内存定时任务</h4>
<p align="center">
<img src="https://www.gaojiufeng.cn/static/images/stable_version.svg" style="max-width:100%;">
<img src="https://www.gaojiufeng.cn/static/images/php_version.svg" style="max-width:100%;">
<img src="https://www.gaojiufeng.cn/static/images/license.svg" style="max-width:100%;"></a>
</p>


## <h4 style="text-align:left">  项目介绍 </h4>
<p>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;EasyTask是基于psr4构建的原生常驻内存定时任务定时器Composer包，同时支持windows、linux、mac环境运行。

&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;QQ群60973229(讨论交流,bug反馈)
</p>

## <h4>   运行环境 </h4>

<ul>
    <li>windows：PHP>=5.5 </li>  
    <li>linux|mac：PHP>=5.5 (依赖pcntl和posix扩展,一般默认已装;）</li>  
</ul>  

## <h4>  Composer安装 </h4>

~~~
  composer require easy-task/easy-task
~~~

~~~
  "require": {
    "easy-task/easy-task": "*"
  }
~~~

## <h5>【一】. 快速入门->创建任务 </h5>

~~~
//初始化
$task = new Task();

// 设置常驻内存
$task->setDaemon(true);

// 设置项目名称
$task->setPrefix('EasyTask');

// 设置记录运行时目录(日志或缓存目录)
$task->setRunTimePath('./Application/Runtime/');

// 1.添加闭包函数类型定时任务(开启2个进程,每隔10秒执行1次)
$task->addFunc(function () {
    $url = 'https://www.gaojiufeng.cn/?id=243';
    @file_get_contents($url);
}, 'request', 10, 2);

// 2.添加类的方法类型定时任务(同时支持静态方法)(开启1个进程,每隔20秒执行1次)
$task->addClass(Sms::class, 'send', 'sendsms', 20, 1);

// 3.添加指令类型的定时任务(开启1个进程,每隔10秒执行1次)
$command = 'php /www/web/orderAutoCancel.php';
$task->addCommand($command,'orderCancel',10,1);

// 启动任务
$task->start();
~~~

## <h5>【二】. 快速入门->连贯操作 </h5>

~~~
$task = new Task();
$task->setDaemon(true)
    ->setPrefix('ThinkTask')
    ->addClass(Sms::class, 'send', 'sendsms1', 20, 1)
    ->addClass(Sms::class, 'recv', 'sendsms2', 20, 1)
    ->addFunc(function () {
        echo 'Success3' . PHP_EOL;
    }, 'fucn', 20, 1)
    ->start();
~~~

## <h5>【三】. 快速入门->命令整合 </h5>

~~~
// 获取命令
$command = empty($_SERVER['argv']['1']) ? '' : $_SERVER['argv']['1'];

// 配置任务
$task = new Task();
$task->setDaemon(true)
    ->addFunc(function () {
        $url = 'https://www.gaojiufeng.cn/?id=271';
        @file_get_contents($url);
    }, 'request', 10, 2);;

// 根据命令执行
if ($command == 'start')
{
    $task->start();
}
elseif ($command == 'status')
{
    $task->status();
}
elseif ($command == 'stop')
{
    $task->stop();
}
else
{
    exit('Command is not exists');
}

启动: php this.php start
查询: php this.php status
关闭: php this.php stop
~~~

## <h5>【四】. 快速入门->认识输出信息 </h5>

~~~
┌─────┬──────────────┬─────────────────────┬───────┬────────┬──────┐
│ Pid │ Name         │ Started             │ Timer │ Status │ Ppid │
├─────┼──────────────┼─────────────────────┼───────┼────────┼──────┤
│ 32  │ Task_request │ 2020-01-10 15:55:44 │ 10    │ active │ 31   │
│ 33  │ Task_request │ 2020-01-10 15:55:44 │ 10    │ active │ 31   │
└─────┴──────────────┴─────────────────────┴───────┴────────┴──────┘
参数:
Pid:当前定时任务的进程id
Name:您为您的定时任务起的别名
Started:定时任务启动时间
Timer:定时任务执行间隔时间
Status:定时任务状态
Ppid:管理当前定时任务的守护进程id
~~~

## <h5>【五】. 进阶了解->linux命令 </h5>

~~~
-> 查询全部任务:ps aux | grep Task  (其中Task可以使用setPrefix方法修改默认名称)
-> 关闭单个任务:kill pid  (例如上面的第一个任务进行id是32,执行kill 32)
-> 关闭全部任务:kill ppid (例如上面的ppid是31,执行kill 31)
-> 禁止kill -9 ppid,否则其他子进程成为孤儿进程
~~~

## <h5>【六】. 进阶了解->window开发准则 </h5>

~~~
-> 请您使用(cmd|powershell)+管理员权限运行 
-> 请您在任何地方都使用绝对路径规范开发
-> 请您在不遵守绝对路径开发的规范前在您的入口文件第一行添加代码chdir(dirname(__FILE__));
~~~

## <h5>【七】. 进阶了解->框架集成 </h5>

&ensp;&ensp;[<font size=2>-> thinkphp3.2.x正在支持</font>](http://example.com/). 

&ensp;&ensp;[<font size=2>-> ThinkPhp5.0.x正在支持</font>](http://example.com/).

&ensp;&ensp;[<font size=2>-> ThinkPhp5.1.x正在支持</font>](http://example.com/).

## <h5>【八】. 进阶了解->推荐操作 </h5>

~~~
-> 推荐使用7.1以上版本的PHP,支持异步信号,不依赖ticks
-> 推荐安装php_event扩展基于事件轮询的毫秒级定时支持
~~~

## <h5>【九】. 学会感恩->感谢phpStorm提供免费授权码 </h5>
<p align="center"><a href="https://www.jetbrains.com/phpstorm/" target="_blank" ><img src="https://www.gaojiufeng.cn/static/images/phpstorm.svg" width="80" height="80"></p>



