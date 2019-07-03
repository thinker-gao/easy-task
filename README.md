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
    "easy-task/easy-task": "*"
  }
~~~

## <h2>【三】 代码案例</h2>

<h4>3.1 创建一个闭包函数每隔10秒执行一次</h4>

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

    //设置执行类的方法
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

3.3 同时添加多个定时任务(支持闭包和类混合添加)
~~~
//初始化Task对象
$task = new Task();
try
{
    //设置常驻内存
    $task->setDaemon(true);

    //添加执行普通类
    $task->addClass(Sms::class, 'send', 'sendsms1', 20, 1);

    //添加执行静态类
    $task->addClass(Sms::class, 'recv', 'sendsms2', 20, 1);

    //添加执行闭包函数
    $task->addFunction(function () {
        echo 'Success3' . PHP_EOL;
    }, 'fucn', 20, 1);

    //启动任务
    $task->start();
}
catch (\Exception $exception)
{
    //错误输出
    var_dump($exception->getMessage());
}
~~~

3.4 使用连贯操作
~~~
//初始化Task对象
$task = new Task();
try
{
    $task->setDaemon(true)
        ->setChdir(true)
        ->setInOut(true)
        ->setPrefix('ThinkTask')
        ->addClass(Sms::class, 'send', 'sendsms1', 20, 1)
        ->addClass(Sms::class, 'recv', 'sendsms2', 20, 1)
        ->addFunction(function () {
            echo 'Success3' . PHP_EOL;
        }, 'fucn', 20, 1)
        ->start();
}
catch (\Exception $exception)
{
    //错误输出
    var_dump($exception->getMessage());
}
~~~


3.5 查看任务运行状态,(请单独创建一个status.php来执行查看状态操作或根据输入命令来隔离启动任务和查看状态的代码，后面会有案例写个一个文件中)
~~~
//初始化
$task = new Task();

//查看运行状态
$task->status();
~~~

3.6 停止运行任务(如果你启动多次任务，然后执行一次停止，历史执行中的进程也会终止！)
~~~
//初始化
$task = new Task();

//普通停止任务
$task->stop();

//强制停止任务   
//$task->stop(true);
~~~

3.7 手工Kill停止任务
~~~
  3.7.1 停止所有任务 kill  ppid (ppid每次在输出结果中会输出,ppid是守护进程id,kill掉会终止相关的任务)
  3.7.2 停止单个任务 kill  pid  (pid每次在输出结果中会输出)
  3.7.3 忘记了输出结果怎么查询全部的任务pid, ps aux | grep 守护进程名 ,默认的守护进程名是EasyTask,然后去kill守护进程的进程即可
~~~


3.8 函数说明
~~~
  3.8.1 setDaemon 是否常驻运行
  3.8.2 setChdir 是否卸载工作区
  3.8.3 setInOut 是否关闭输入输出
  3.8.4 setPrefix 设置任务进程前缀名称,守护进程的名称就是它
  3.8.5 setIpcKey 设置IPC通信Key,除非你懂得怎么设置,否则请不要设置
~~~

3.9 整合命令到一个php文件,创建console.php(一般由用户自行封装，这里做个demo)
    <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;启动命令: php ./console.php start
    <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;状态命令: php ./console.php status
    <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;停止命令: php ./console.php stop
    <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;强制停止命令: php ./console.php stop -f
~~~
//实例化Task
$task = new Task();

//提取命令行传参的命令
$argv = $_SERVER['argv'];
if (!empty($argv['1']))
{
    if ($argv['1'] == 'start')
    {
        //启动命令
        $task->setDaemon(true)->addFunction(function () {
            //重复执行的逻辑写在这里
        }, 'request', 15, 1)->start();
    }
    if ($argv['1'] == 'status')
    {
        //状态命令
        $task->status();
    }
    if ($argv['1'] == 'stop')
    {
        //停止命令
        $force = false;
        if (!empty($argv['2']) && $argv['2'] == '-f')
        {
            $force = true;
        }
        $task->stop($force);
    }
}
~~~


## <h2>【四】 其他框架引入</h2>


