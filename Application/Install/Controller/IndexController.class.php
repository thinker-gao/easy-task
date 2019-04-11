<?php
namespace Install\Controller;

use Think\Controller;

class IndexController extends Controller
{
    /**
     * 安装首页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 阅读协议
     */
    public function step1()
    {
        $this->display();
    }
}