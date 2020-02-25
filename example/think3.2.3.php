<?php

class Support
{
    /**
     * argv
     * @var mixed
     */
    private $argv;

    /**
     * argc
     * @var mixed
     */
    private $argc;

    /**
     * command
     * @var string
     */
    private $command;

    /**
     * Support constructor.
     */
    public function __construct()
    {
        //重置工作目录(only_win_system)
        chdir(dirname(__FILE__));

        //保存Cli_Input
        $this->argv = $_SERVER['argv'];
        $this->argc = $_SERVER['argc'];

        //保存命令并清空Cli_Input
        $this->command = isset($_SERVER['argv']['1']) ? $_SERVER['argv']['1'] : '';
        $_SERVER['argv'] = [] && $_SERVER['argc'] = 0;
    }

    /**
     * 加载Think代码
     * @param Closure $think
     * @return Support
     */
    public function invokeThink($think)
    {
        ob_start();
        $think();
        ob_get_clean();
        return $this;
    }

    /**
     * 加载你的代码
     * @param Closure $code
     */
    public function invokeYourCode($code)
    {
        //恢复Cli_Input
        $_SERVER['argv'] = $this->argv;
        $_SERVER['argc'] = $this->argc;

        //执行
        $code($this->command);
    }
}

//How to use it

(new Support())
    ->invokeThink(function () {
        //复制think下index.php的代码
    })
    ->invokeYourCode(function ($command) {
        // 加载Composer
        require './vendor/autoload.php';

        // $command值有start|status|stop

        // 编写你的代码
    });

// How to run

// php ./index.php start|status|stop