<?php

/**
 * Think3.2.3支持
 */
class ThinkSupport
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
     * action
     * @var string
     */
    private $action;

    /**
     * force
     * @var string
     */
    private $force;

    /**
     * Support constructor.
     */
    public function __construct()
    {
        //保存Cli_Input
        $this->argv = $_SERVER['argv'];
        $this->argc = $_SERVER['argc'];

        //保存命令并清空Cli_Input
        $this->action = isset($_SERVER['argv']['1']) ? $_SERVER['argv']['1'] : '';
        $this->force = isset($_SERVER['argv']['2']) ? $_SERVER['argv']['2'] : '';
        $_SERVER['argv'] = [] && $_SERVER['argc'] = 0;

        //抑制Tp错误
        if (!isset($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        if (!isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = 'localhost';
    }

    /**
     * 加载Think代码
     * @param Closure $think
     * @return ThinkSupport
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
        //恢复Cli_Input.(方便自己扩展)
        $_SERVER['argv'] = $this->argv;
        $_SERVER['argc'] = $this->argc;

        //执行
        $code($this->action, $this->force);
    }
}

/**
 * Code start
 */
(new ThinkSupport())
    ->invokeThink(function () {
        //加载tp的代码
        require './index.php';
    })
    ->invokeYourCode(function ($action, $force) {
        // 加载Composer
        require './vendor/autoload.php';

        // $action值有start|status|stop

        // 编写你的代码
    });

/**
 * How to run ?
 * Use cmd or powerShell:
 * php ./index.php start|status|stop
 */