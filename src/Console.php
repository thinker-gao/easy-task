<?php
namespace EasyTask;

use MathieuViossat\Util\ArrayToTextTable;
use Zend\Text\Table\Decorator\Ascii;

class Console
{
    /**
     * 控制台抛出异常
     * @param string $message 错误信息
     * @throws
     */
    public static function error($message)
    {
        throw new \Exception($message);
    }

    /**
     * 控制台输出表格
     * @param array $data 输出数据
     * @param boolean $exit 输出后是否退出
     */
    public static function showTable($data, $exit = true)
    {
        $renderer = new ArrayToTextTable($data);
        $renderer->setDecorator(new Ascii());
        $renderer->setValuesAlignment(ArrayToTextTable::AlignCenter);
        echo($renderer->getTable());
        if ($exit)
        {
            exit();
        }
    }
}

