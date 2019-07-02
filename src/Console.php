<?php
namespace EasyTask;

use MathieuViossat\Util\ArrayToTextTable;
use Wujunze\Colors;

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
     */
    public static function showTable($data)
    {
        $renderer = new ArrayToTextTable($data);
        $renderer->setValuesAlignment(ArrayToTextTable::AlignCenter);
        exit($renderer->getTable());
    }
}

