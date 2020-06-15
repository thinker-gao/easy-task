<?php
namespace EasyTask\Exception;

/**
 * Class ErrorException
 * @package EasyTask\Exception
 */
class ErrorException extends \Exception
{
    /**
     * 错误级别
     * @var int
     */
    protected $severity;

    /**
     * 构造函数
     * ErrorException constructor.
     * @param string $severity
     * @param string $errStr
     * @param string $errFile
     * @param string $errLine
     */
    public function __construct($severity, $errStr, $errFile, $errLine)
    {
        $this->line = $errLine;
        $this->file = $errFile;
        $this->code = 0;
        $this->message = $errStr;
        $this->severity = $severity;
    }
}

