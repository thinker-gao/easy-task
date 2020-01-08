<?php
namespace EasyTask;

use MathieuViossat\Util\ArrayToTextTable;
use Zend\Text\Table\Decorator\Ascii;

class Console
{
    /**
     * 控制台输出表格
     * @param array $data 输出数据
     * @param boolean $exit 输出后是否退出
     */
    public static function showTable($data, $exit = true)
    {
        var_dump($data);
        if ($exit)
        {
            exit();
        }
    }
}

