<?php
namespace EasyTask;

class Exception extends \Exception
{
    /**
     * @var 错误级别
     */
    protected $severity;

    public function __construct($severity, $errstr, $errfile, $errline)
    {
        $this->line = $errline;
        $this->file = $errfile;
        $this->code = 0;
        $this->message = $errstr;
        $this->severity = $severity;
    }
}

