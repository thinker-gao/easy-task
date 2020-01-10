<h3>EasyTask -- 原生常驻内存定时任务  <h4>(QQ交流群60973229)</h4></h3>
===============

> 运行环境：windows | linux | macos 


## <h4>【一】 环境配置</h4>

<ul>
    <li>windows：PHP>=5.4</li>  
    <li>linux|mac：PHP>=5.4（依赖pcntl和posix扩展，一般默认已安装，推荐PHP7.1以上，支持异步信号，不依赖ticks）</li>  
</ul>  

## <h4>【二】 Composer安装</h4>

~~~
  composer require easy-task/easy-task
~~~

~~~
  "require": {
    "easy-task/easy-task": "*"
  }
~~~

## <h4>【三】 快速使用 （后面其他的只给命令演示和demo文件，更多的只给函数介绍） </h4>

<h5>3.1 创建多个定时任务</h5>

~~~
$task = new Task();

//设置常驻内存
$task->setDaemon(true);

//设置关闭标准输入输出(定时任务中任何输入和打印全部关闭,不显示)
$task->setCloseInOut(true);

//设置记录日志,当日志存在异常类型抛出到外部
$task->setWriteLog(true, true);

//1.添加闭包函数类型定时任务(开启2个进程,每隔10秒执行1次)
$task->addFunc(function () {
    $url = 'https://www.gaojiufeng.cn/?id=243';
    @file_get_contents($url);
}, 'request', 10, 2);

//2.添加类的方法类型定时任务(同时支持静态方法)(开启1个进程,每隔20秒执行1次)
$task->addClass(Sms::class, 'send', 'sendsms', 20, 1);

//3.添加指令类型的定时任务(开启1个进程,每隔10秒执行1次)
$command = 'php /www/web/orderAutoCancel.php';
$task->addCommand($command,'orderCancel',10,1);

//启动任务
$task->start();
~~~

addFunc函数第一个参数传递闭包函数，编写自己需要的逻辑，第二个参数是任务的别名，在输出结果中会体现，第三个参数是每隔多少秒执行1次，第四个参数是启动几个进程来执行

<h5>3.2 使用连贯操作</h5>

~~~
$task = new Task();
$task->setDaemon(true)
    ->setCloseInOut(true)
    ->setPrefix('ThinkTask')
    ->addClass(Sms::class, 'send', 'sendsms1', 20, 1)
    ->addClass(Sms::class, 'recv', 'sendsms2', 20, 1)
    ->addFunc(function () {
        echo 'Success3' . PHP_EOL;
    }, 'fucn', 20, 1)
    ->start();
~~~

<h5>3.3 整合启动任务、查看状态、关闭任务(仅供参考)</h5>

~~~
//获取命令行输入参数
$cliArgv = $_SERVER['argv'];
$command = empty($cliArgv['1']) ? '' : $cliArgv['1'];  //获取输入的是start,status,stop中的哪一个
$isForce = !empty($cliArgv['2']) && $cliArgv['2'] == '-f' ? true : false;  //获取是否要强制停止

//配置定时任务
$task = new Task();
$task->setDaemon(true)
    ->setCloseInOut(true)
    ->setWriteLog(true, true)
    ->addFunc(function () {
        $url = 'https://www.gaojiufeng.cn/?id=271';
        @file_get_contents($url);
    }, 'request', 10, 2);;

//根据命令执行
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
    $task->stop($isForce);
}
else
{
    exit('This is command is not exists:' . $command . PHP_EOL);
}

//启动命令: php this.php start
//查询命令: php this.php status
//关闭命令: php this.php stop
//强制命令: php this.php stop -f
~~~

<h5>3.4 认识启动后表格输出信息</h5>

~~~
┌─────┬──────────────┬─────────────────────┬───────┬────────┬──────┐
│ pid │ task_name    │ started             │ timer │ status │ ppid │
├─────┼──────────────┼─────────────────────┼───────┼────────┼──────┤
│ 32  │ Task_request │ 2020-01-10 15:55:44 │ 10s   │ active │ 31   │
│ 33  │ Task_request │ 2020-01-10 15:55:44 │ 10s   │ active │ 31   │
└─────┴──────────────┴─────────────────────┴───────┴────────┴──────┘
参数说明:
    pid:当前定时任务的进程id
    task_name:您为您的定时任务起的别名
    started:定时任务启动时间
    timer:定时任务执行间隔时间
    status:定时任务状态
    ppid:管理当前定时任务的守护进程id
~~~

<h5>3.5 手工Linux命令管理</h5>

~~~
  3.5.1查询全部任务:ps aux | grep Task  (其中Task可以使用setPrefix方法修改默认名称)
  3.5.2关闭单个任务:kill pid  (例如上面的第一个任务进行id是32,执行kill 32)
  3.5.3关闭全部任务:kill ppid (例如上面的ppid是31,执行kill 31)
  提示:请不要直接kill -9 ppid,否则其他子进程成为孤儿进程
~~~


<h5>3.6 Windows特别说明</h5>

~~~
  3.6.1 windows支持的函数:
                setPrefix(),
                addCommand(),
                start(),
        
  3.6.2 windows必须使用cmd管理员权限执行
  3.6.3 windows不可保证稳定性,排查跟踪困难
~~~


