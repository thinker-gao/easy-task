<?php
namespace Install\Logic;

/**
 * 安装逻辑层
 * @package Install\Logic
 */
class SaveLogic
{
    /**
     * 扩展环境检测
     */
    public $chkexts;

    /**
     * 目录读写检测
     */
    public $chkrwpath = array();

    /**
     * 检查环境是否通过
     */
    public $chkenvpass = true;

    /**
     * IndexLogic constructor.
     */
    public function __construct()
    {
        //扩展安装检查
        $this->checkExts();

        //读写权限检查
        $this->checkPaths();
    }
}