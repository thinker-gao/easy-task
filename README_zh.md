<p><h4>EasyTask简单易用的PHP常驻内存定时器</h4></p>
<a href="//shang.qq.com/wpa/qunwpa?idkey=d436563ad70f4e19d4a98b3e86cfe5272fd4a628a0d7f4c6d552b0012c55b4d7">官方ＱＱ群点击一键加入</a> | <a href="./README.md">English document</a> 
<br><br>
<p align="">
<a href="" rel="noopener noreferrer" target="_blank" rel="noopener noreferrer">
<img src="https://www.gaojiufeng.cn/static/images/stable_version.svg" style="max-width:100%;">
<img src="https://www.gaojiufeng.cn/static/images/php_version.svg" style="max-width:100%;">
<img src="https://www.gaojiufeng.cn/static/images/license.svg" style="max-width:100%;">
</a>
</p>

## <h4 style="text-align:left">  项目介绍 </h4>
<p>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;EasyTask,PHP常驻内存定时器Composer包，与Workerman定时器效果完全一致，多个定时器是同时在多个进程中运行的，您可以用它来完成需要重复运行的任务(如订单超时自动取消,短信邮件异步推送,队列/消费者/频道订阅者等等)，甚至处理Crontab计划任务(如每天凌晨1点-3点同步DB数据,每月1号生成月度统一报表,每晚10点重启nginx服务器等等)；内置任务异常上报功能，异常错误您都可以自定义处理(例如实现异常错误自动短信邮件通知)；还支持任务异常退出自动重启功能，让您的任务运行更稳定 ，工具包同时支持windows、linux、mac环境运行。
</p>

## <h4>   运行环境 </h4>

<ul>
    <li>windows：PHP>=5.5 (依赖com_dotnet+wpc扩展）<a href="https://www.kancloud.cn/a392223903/easytask/1666906" target="_blank">文档+安装教程</a></li>  
    <li>linux|mac：PHP>=5.5 (依赖pcntl+posix扩展）<a href="https://www.kancloud.cn/a392223903/easytask/1666906" target="_blank">文档+安装教程</a></li>
</ul>  

## <h4>  Composer安装 </h4>

~~~
  composer require easy-task/easy-task
~~~

## <h5>【一】. 快速入门->创建任务 </h5>

~~~
// 初始化
$task = new Task();

// 设置非常驻内存
$task->setDaemon(false);

// 设置项目名称
$task->setPrefix('EasyTask');

// 设置记录运行时目录(日志或缓存目录)
$task->setRunTimePath('./Application/Runtime/');

// 1.添加闭包函数类型定时任务(开启2个进程,每隔10秒执行1次你写闭包方法中的代码)
$task->addFunc(function () {
    $url = 'https://www.gaojiufeng.cn/?id=243';
    @file_get_contents($url);
}, 'request', 10, 2);

// 2.添加类的方法类型定时任务(同时支持静态方法)(开启1个进程,每隔20秒执行一次你设置的类的方法)
$task->addClass(Sms::class, 'send', 'sendsms', 20, 1);

// 3.添加指令类型的定时任务(开启1个进程,每隔10秒执行1次)
$command = 'php /www/web/orderAutoCancel.php';
$task->addCommand($command,'orderCancel',10,1);

// 4.添加闭包函数任务,不需要定时器,立即执行(开启1个进程)
$task->addFunc(function () {
    while(true)
    {
       //todo
    }
}, 'request', 0, 1);

// 5.每晚9点半通过curl命令访问网站
$task->addCommand('curl https://www.gaojiufeng.cn', 'curl', '30 21 * * *', 1);

// 启动任务
$task->start();
~~~

## <h5>【二】. 快速入门->连贯操作 </h5>

~~~
// 初始化
$task = new Task();

// 设置常驻内存
$task->setDaemon(true)   

// 设置项目名称
->setPrefix('ThinkTask')   

// 设置系统时区
->setTimeZone('Asia/Shanghai')  

// 设置子进程挂掉自动重启
->setAutoRecover(true)  

// 设置PHP运行路径,一般Window系统才需要设置,当系统无法找到才需要您手动设置
->setPhpPath('C:/phpEnv/php/php-7.0/php.exe')

/**
 * 设置运行时目录(日志或缓存目录)
 */
->setRunTimePath('./Application/Runtime/')

/**
 * 设置关闭标准输出的STD文件记录
 */
->setCloseStdOutLog(true);

/**
 * 关闭EasyTask的异常注册
 * EasyTask将不再监听set_error_handler/set_exception_handler/register_shutdown_function事件
 */
->setCloseErrorRegister(true)

/**
 * 设置接收运行中的错误或者异常(方式1)
 * 您可以自定义处理异常信息,例如将它们发送到您的邮件中,短信中,作为预警处理
 * (不推荐的写法,除非您的代码健壮)
 */
->setErrorRegisterNotify(function ($ex) {
    //获取错误信息|错误行|错误文件
    $message = $ex->getMessage();
    $file = $ex->getFile();
    $line = $ex->getLine();
})

/**
 * 设置接收运行中的错误或者异常的Http地址(方式2)
 * Easy_Task会POST通知这个url并传递以下参数:
 * errStr:错误信息
 * errFile:错误文件
 * errLine:错误行
 * 您的Url收到POST请求可以编写代码发送邮件或短信通知您
 * (推荐的写法)
 */
->setErrorRegisterNotify('https://www.gaojiufeng.cn/rev.php')

// 添加任务定时执行闭包函数
->addFunc(function () {
    echo 'Success3' . PHP_EOL;
}, 'fucn', 20, 1)   

// 添加任务定时执行类的方法
->addClass(Sms::class, 'send', 'sendsms1', 20, 1)   

// 添加任务定时执行命令
->addCommand('php /www/wwwroot/learn/curl.php','cmd',6,1)

// 启动任务
->start();
~~~

## <h5>【三】. 快速入门->命令整合 </h5>

~~~
// 获取命令
$force = empty($_SERVER['argv']['2']) ? '' : $_SERVER['argv']['2'];
$command = empty($_SERVER['argv']['1']) ? '' : $_SERVER['argv']['1'];

// 配置任务
$task = new Task();
$task->setRunTimePath('./Application/Runtime/');
$task->addFunc(function () {
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
    $force = ($force == 'force'); //是否强制停止
    $task->stop($force);
}
else
{
    exit('Command is not exist');
}

启动任务: php console.php start
查询任务: php console.php status
普通关闭: php console.php stop
强制关闭: php console.php stop force
~~~

## <h5>【四】. 快速入门->认识输出信息 </h5>

~~~
┌─────┬──────────────┬─────────────────────┬───────┬────────┬──────┐
│ pid │ name         │ started             │ time │ status │ ppid │
├─────┼──────────────┼─────────────────────┼───────┼────────┼──────┤
│ 32  │ Task_request │ 2020-01-10 15:55:44 │ 10    │ active │ 31   │
│ 33  │ Task_request │ 2020-01-10 15:55:44 │ 10    │ active │ 31   │
└─────┴──────────────┴─────────────────────┴───────┴────────┴──────┘
参数:
pid:任务进程id
name:任务别名
started:任务启动时间
time:任务执行时间
status:任务状态
ppid:守护进程id
~~~

## <h5>【五】. 进阶了解->建议阅读 </h5>

~~~
(1). 建议您使用绝对路径进行开发,是标准更是规范
(2). 禁止在任务中使用exit/die语法,否则导致整个进程退出
(3). Windows安装Wpc扩展时请关闭杀毒软件,避免误报
(4). Windows建议开启popen,pclose方法,会自动尝试帮您解决CMD输出中文乱码问题,请尽量使用CMD管理员方式运行
(5). Windows命令行不支持utf8国际标准编码，可切换git_bash来运行,解决乱码问题
(6). Windows提示Failed to create COM object `Wpc.Core': 无效的语法,请按照文档安装Wpc扩展
(7). Windows提示com() has been disabled for security reasons,请在php.ini中删除disable_classes = com配置项目
(8). 日志文件在运行时目录的Log目录下,标出输入输出异常文件在运行时目录Std目录下
(9). 普通停止任务,任务会在执行成功后开始安全退出,强制停止任务直接退出任务,可能正在执行就强制退出
(10). 开发遵守先同步启动测试正常运行无任何报错再设置异步运行,有问题查看日志文件或者标准输入输出异常文件,或者上QQ群反馈
~~~

## <h5>【六】. 进阶了解->框架集成教程 </h5>

&ensp;&ensp;[<font size=2>-> thinkphp3.2.x教程</font>](https://www.gaojiufeng.cn/?id=293). 

&ensp;&ensp;[<font size=2>-> thinkPhp5.x.x教程</font>](https://www.gaojiufeng.cn/?id=294).

&ensp;&ensp;[<font size=2>-> thinkPhp6.x.x教程</font>](https://www.gaojiufeng.cn/?id=328).

&ensp;&ensp;[<font size=2>-> laravelPhp6.x.x教程</font>](https://www.gaojiufeng.cn/?id=295).

## <h5>【七】. 进阶了解->推荐操作 </h5>

~~~
(1).推荐使用7.1以上版本的PHP,支持异步信号,不依赖ticks
(2).推荐安装php_event扩展基于事件轮询的毫秒级定时支持
~~~

## <h5>【八】. 进阶了解->CronTab支持 </h5>

~~~
自2.3.6版本开始移除Crontab的支持,请通过PHP自带时间函数进行处理.
(1).例如只需要每天晚上20点执行,判断不是20点执行Return即可.
$task->addFunc(function () {
    $hour = date('H');
    if ($hour != 20)
    {
        return;
    }
    
    //Write your code
}, 'request', 1, 1);
(2).例如只需要每周日执行,判断不是周日执行Return即可
$task->addFunc(function () {
    $week = date('w');
    if ($week !== 0)
    {
        return;
    }
    
    //Write your code
}, 'request', 1, 1);
~~~

## <h5>【九】. 特别感谢 </h5>
~~~
(1).ThinkPHP(命令行输出组件基于Tp_Table组件),官方地址:http://www.thinkphp.cn/
~~~
## <h5>【十】. Bug反馈 </h5>
~~~
请反馈至QQ群777241713,感谢持续反馈的用户,是您的反馈让EasyTask越来越稳定!
~~~