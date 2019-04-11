<?php
namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller
{
    /***
     * @CMS安装Lock文件
     */
    private $installLockFile;

    /**
     * 初始化安装检测
     */
    public function __construct()
    {
        $this->installLockFile = RUNTIME_PATH . 'Lock' . DIRECTORY_SEPARATOR . 'install.lock';
        if (is_file($this->installLockFile) == false)
        {
            redirect('/Install/index/index', 0, '系统未安装,跳转中。。。');
        }
    }
}