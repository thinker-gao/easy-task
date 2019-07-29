<h3>EasyTask -- 原生常驻内存定时任务  <h4>(QQ交流群60973229)</h4></h3>
===============

> 运行环境：windows | linux | macos 


## <h4>【一】 环境配置</h4>

<ul>
    <li>windows：PHP>=5.4，安装pthreads扩展，需要PHP-ZTS(线程安全版)</li>  
    <li>linux|mac：PHP>=5.4，安装[pcntl|posix](一般自带)扩展，推荐PHP7.1以上，异步信号，不依赖ticks</li>  
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

## <h4>【三】 快速使用  </h4>

<h5>3.1 创建一个闭包函数每隔10秒执行一次</h5>

~~~
//初始化Task对象
$task = new Task();

//设置常驻内存
$task->setDaemon(true);

//添加闭包函数任务
$task->addFunc(function () {
    $url = 'https://www.gaojiufeng.cn/?id=243';
    @file_get_contents($url);
}, 'request', 10, 2);

//启动任务
$task->start();
~~~

addFunc函数第一个参数传递闭包函数，编写自己需要的逻辑，第二个参数是任务的别名，在输出结果中会体现，第三个参数是每隔多少秒执行1次，第四个参数是启动几个进程来执行

<h5>3.2 每隔20秒执行一次类的方法(同时支持静态方法)</h5>

~~~
//初始化Task对象
$task = new Task();

//设置常驻内存
$task->setDaemon(true);

//设置执行类的方法
$task->addClass(Sms::class, 'send', 'sendsms', 20, 1);

//启动任务
$task->start();
~~~

<h5>3.3 同时添加多个定时任务(支持闭包和类混合添加)</h5>

~~~
//初始化Task对象
$task = new Task();

//设置常驻内存
$task->setDaemon(true);

//添加执行普通类
$task->addClass(Sms::class, 'send', 'sendsms1', 20, 1);

//添加执行静态类
$task->addClass(Sms::class, 'recv', 'sendsms2', 20, 1);

//添加执行闭包函数
$task->addFunc(function () {
    echo 'Success3' . PHP_EOL;
}, 'fucn', 20, 1);

//启动任务
$task->start();
~~~

<h5>3.4 使用连贯操作</h5>

~~~
//初始化Task对象
$task = new Task();

$task->setDaemon(true)
    ->setChdir(true)
    ->setInOut(true)
    ->setPrefix('ThinkTask')
    ->addClass(Sms::class, 'send', 'sendsms1', 20, 1)
    ->addClass(Sms::class, 'recv', 'sendsms2', 20, 1)
    ->addFunc(function () {
        echo 'Success3' . PHP_EOL;
    }, 'fucn', 20, 1)
    ->start();
~~~

<h5>3.5 查看任务运行状态</h5>

~~~
//初始化
$task = new Task();

//查看运行状态(windows不支持)
$task->status();
~~~

<h5>3.6 停止运行任务</h5>

~~~
//初始化
$task = new Task();

//普通停止任务(windows不支持)
$task->stop();

//强制停止任务(windows不支持)   
//$task->stop(true);
~~~

<h5>3.7 手工操作任务</h5>

~~~
  3.7.1 停止所有任务 kill  ppid (ppid每次在输出结果中会输出,ppid是守护进程id,kill掉会终止相关的任务)
  3.7.2 停止单个任务 kill  pid  (pid每次在输出结果中会输出)
  3.7.3 查询全部任务 ps aux | grep 守护进程名(默认是EasyTask)
~~~


<h5>3.8 Task函数说明</h5>

~~~
  3.8.1 setDaemon 是否常驻运行(windows不支持)
  3.8.2 setChdir 是否卸载工作区(windows不支持)
  3.8.3 setInOut 是否关闭输入输出
  3.8.4 setPrefix 设置任务进程前缀名称,守护进程的名称就是它
  3.8.5 start 启动定时任务
  3.8.6 status 查看任务状态 (windows不支持)
  3.8.7 stop 停止任务 (windows不支持)
~~~

## <h4>【四】 框架整合</h4>

<h5>4.1 ThinkPHP3.2.3 </h5>

4.1.1 根目录创建console.php ,文件代码

~~~
namespace EasyTask;

// 检测PHP环境
use Home\Server\SmsServer;

if (version_compare(PHP_VERSION, '5.3.0', '<')) die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', True);

// 定义应用目录
define('APP_PATH', './Application/');

// 获取命令行参数并重置
$argv = getArgvs();

// 引入ThinkPHP
require './ThinkPHP/ThinkPHP.php';

// 引入Composer包
require './vendor/autoload.php';


// 提取命令行输入的命令-s
$func = $argv['1'];
$force = false;
if (count($argv) < 2)
{
    exit('命令不存在！' . PHP_EOL);
}
$allowFunc = array('start', 'status', 'stop');
if (!in_array($argv['1'], $allowFunc))
{
    exit('命令不存在！' . PHP_EOL);
}
if (!empty($argv['2']) && $argv['2'] == '-f')
{
    $force = true;
}
// 提取命令行输入的命令-e

// 创建定时任务
$task = new Task();
try
{
    // 监听到启动命令
    if ($func == 'start')
    {
        //设置常驻
        $task->setDaemon(true);

        //闭包方式添加
        $task->addFunc(function () {
            SmsServer::send();
        }, '短信服务', 5, 1);

        //执行类方式添加
        $class = '\\Home\\Server\\MailServer';
        $task->addClass($class, 'send', '邮件服务', 5, 1);

        //启动定时任务
        $task->start();
    }

    // 监听到查询状态命令
    if ($func == 'status')
    {
        $task->status();
    }

    // 监听到停止命令
    if ($func == 'stop')
    {
        $task->stop($force);
    }
}
catch (\Exception $exception)
{
    //异常输出
    exit($exception->getMessage());
}

/**
 * 获取命令行参数
 */
function getArgvs()
{
    $argv = $_SERVER['argv'];
    if (count($_SERVER['argv']) >= 2)
    {
        $_SERVER['argv'] = array($argv['0']);
        $_SERVER['argc'] = count($_SERVER['argv']);
    }
    return $argv;
}
~~~

<h5>4.2 ThinkPHP5 </h5>

4.2.1 创建一个自定义命令类文件，新建application/common/command/Task.php

~~~
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Task extends Command
{
    protected function configure()
    {
        //添加输入参数
        $this->setName('Task')
            ->addArgument('func', Argument::OPTIONAL, "func")
            ->addArgument('force', Argument::OPTIONAL, "force");
    }

    protected function execute(Input $input, Output $output)
    {
        //获取输入参数
        $func = trim($input->getArgument('func'));
        $force = trim($input->getArgument('force'));
        $func = $func ?: '';
        $force = $force == '-f' ? true : false;

        //校验输入参数
        $allowFunc = ['start', 'status', 'stop'];
        if (!in_array($func, $allowFunc))
        {
            $output->writeln('命令不存在');
            exit();
        }

        //初始化Task
        $task = new \EasyTask\Task();
        try
        {
            if ($func == 'start')
            {
                //设置常驻
                $task->setDaemon(true);

                //添加任务测试(可以创建一个配置文件,把所有要执行的类循环添加进去)
                $task->addFunc(function () {
                    echo '1122' . PHP_EOL;
                }, 'request', 20, 1);

                //启动定时任务
                $task->start();
            }
            if ($func == 'status')
            {
                $task->status();
            }
            if ($func == 'stop')
            {
                $task->stop($force);
            }
        }
        catch (Exception $exception)
        {
            //输出错误信息
            var_dump($exception->getMessage());
        }
    }
}
~~~

4.2.2 配置application/command.php文件

~~~
return [
    'app\common\command\Task',
];
~~~

执行命令: 
php think Task  start


