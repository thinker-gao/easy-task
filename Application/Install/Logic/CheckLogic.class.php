<?php
namespace Install\Logic;

/**
 * 安装检查
 * @package Install\Logic
 */
class CheckLogic
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

    /**
     * 检查扩展安装
     */
    private function checkExts()
    {
        $this->chkexts = C('chkexts');
        $loadedExts = get_loaded_extensions();
        foreach ($this->chkexts as $key => $item)
        {
            $this->chkexts[$key] = in_array($key, $loadedExts);
            if (!$this->chkexts[$key])
            {
                $this->chkenvpass = false;
            }
        }
    }

    /**
     * 检查读写权限
     */
    private function checkPaths()
    {
        $chkrwpath = C('chkrwpath');
        foreach ($chkrwpath as $key => $item)
        {
            $path = APP_PATH . $key;
            $isread = is_readable($path);
            $iswrite = is_writeable($path);
            array_push($this->chkrwpath, array(
                'path' => $path,
                'read' => $isread,
                'write' => $iswrite
            ));
            if (!$isread || !$iswrite)
            {
                $this->chkenvpass = false;
            }
        }
    }
}