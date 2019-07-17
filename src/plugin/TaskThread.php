<?php
namespace EasyTask\plugin;

/**
 * 多线程基类
 */
class TaskThread extends \Thread
{

    /**
     * 线程执行的任务
     * @var $item
     */
    public $item;

    /**
     * 构造函数
     * @var $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * 获取线程Id
     * @return int
     */
    public function getCreatorId()
    {
        return parent::getCreatorId();
    }

    /**
     * 执行任务体
     */
    private function callTask()
    {
        $item = $this->item;
        if ($item['type'] == 0)
        {
            $func = $item['func'];
            $func();
        }
        elseif ($item['type'] == 1)
        {
            call_user_func([$item['class'], $item['func']]);
        }
        else
        {
            $object = new $item['class']();
            call_user_func([$object, $item['func']]);
        }
    }

    /**
     * 单线程执行的任务
     */
    function run()
    {
        //修复线程中时间问题
        date_default_timezone_set('Asia/Shanghai');

        //循环执行任务
        while (true)
        {
            //执行任务
            $this->callTask();

            //Cpu休息
            sleep($this->item['time']);
        }
    }
}
