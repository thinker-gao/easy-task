<?php
namespace EasyTask\Process;

use \Closure as Closure;
use EasyTask\Env;
use EasyTask\Helper;

/**
 * 抽象类
 * (Linux|Win计划迭代抽象至此)
 * Class Process
 * @package EasyTask\Process
 */
abstract class Process
{
    /**
     * 开始运行
     */
    abstract public function start();

    /**
     * 运行状态
     */
    abstract public function status();

    /**
     * 停止运行
     * @param bool $force
     */
    abstract public function stop($force = false);

    /**
     * 检查是否可写标准输出日志
     * @return bool
     */
    protected function canWriteStd()
    {
        return Env::get('daemon') && !Env::get('closeStdOutPutLog');
    }
}