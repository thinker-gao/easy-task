<h3>EasyTask -- PHP原生常驻内存定时任务定时器</h3>
<p align="left">
<img src="./icon/php_version.svg" style="max-width:100%;">
<img src="./icon/license.svg" style="max-width:100%;"></a>
</p>

> 运行环境:windows | linux | mac


## <h4>【一】 环境配置 </h4>

<ul>
    <li>windows：PHP>=5.5 (推荐安装event扩展,事件循环毫秒级支持)</li>  
    <li>linux|mac：PHP>=5.5 (依赖pcntl和posix扩展,一般默认已安装;推荐安装event扩展,事件循环毫秒级支持）</li>  
</ul>  

## <h4>【二】 Composer安装 </h4>

~~~
  composer require easy-task/easy-task
~~~

~~~
  "require": {
    "easy-task/easy-task": "*"
  }
~~~

## <h4>【三】 快速使用  </h4>

<h5>3.1 创建任务</h5>

~~~
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

<h5>3.2 连贯操作</h5>

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

<h5>3.3 启动|查看|关闭命令整合(Demo)</h5>

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

<h5>3.4 启动信息</h5>

~~~
┌─────┬──────────────┬─────────────────────┬───────┬────────┬──────┐
│ pid │ name         │ started             │ timer │ status │ ppid │
├─────┼──────────────┼─────────────────────┼───────┼────────┼──────┤
│ 32  │ Task_request │ 2020-01-10 15:55:44 │ 10    │ active │ 31   │
│ 33  │ Task_request │ 2020-01-10 15:55:44 │ 10    │ active │ 31   │
└─────┴──────────────┴─────────────────────┴───────┴────────┴──────┘
参数说明:
        pid:当前定时任务的进程id
        name:您为您的定时任务起的别名
        started:定时任务启动时间
        timer:定时任务执行间隔时间
        status:定时任务状态
        ppid:管理当前定时任务的守护进程id
~~~

<h5>3.5 手工管理(Linux命令)</h5>

~~~
查询全部任务:ps aux | grep Task  (其中Task可以使用setPrefix方法修改默认名称)
关闭单个任务:kill pid  (例如上面的第一个任务进行id是32,执行kill 32)
关闭全部任务:kill ppid (例如上面的ppid是31,执行kill 31)
提示:请不要直接kill -9 ppid,否则其他子进程成为孤儿进程
~~~

<h5>3.6 Windows支持</h5>

~~~
windows必须使用cmd或powershell以管理员权限运行 
~~~


