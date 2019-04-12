<?php
namespace Install\Controller;

use Install\Logic\CheckLogic;
use Install\Logic\InstallLogic;
use Think\Controller;
use Think\Db;
use Think\Exception;

class IndexController extends Controller
{
    /**
     * 安装首页
     */
    public function index()
    {
        //环境检查
        $check = new CheckLogic();

        //$check->chkenvpass =false;

        $this->assign('chkexts', $check->chkexts);
        $this->assign('chkrwpath', $check->chkrwpath);
        $this->assign('chkenvpass', $check->chkenvpass);
        $this->display();
    }

    /**
     * 阅读协议
     */
    public function save()
    {
        //外部参数
        $dbConf = [
            'DB_TYPE' => 'mysql',// 数据库类型
            'DB_PREFIX' => 'light',// 数据表前缀
            'DB_CHARSET' => 'utf8',// 网站编码
            'DB_HOST' => I('dbhost'),// 数据库地址
            'DB_PORT' => I('dbport'),// 数据库端口
            'DB_NAME' => I('dbname'),// 数据库名称
            'DB_USER' => I('dbuser'),// 数据库用户名
            'DB_PWD' => I('dbpass'),// 数据库密码
        ];
        $admin = [
            'admin_user' => I('admin_user'),
            'admin_pass' => I('admin_pass'),
        ];

        //数据校验
        if (empty($dbConf['DB_HOST']))
        {
            $this->ajaxResponse(1, '数据库地址不得为空！');

        }
        if (empty($dbConf['DB_PORT']))
        {
            $this->ajaxResponse(1, '数据库端口不得为空！');
        }
        if (empty($dbConf['DB_NAME']))
        {
            $this->ajaxResponse(1, '数据库名称不得为空！');
        }
        if (empty($dbConf['DB_USER']))
        {
            $this->ajaxResponse(1, '数据库用户不得为空！');
        }
        if (empty($dbConf['DB_PWD']))
        {
            $this->ajaxResponse(1, '数据库密码不得为空！');
        }
        if (empty($admin['admin_user']) || empty($admin['admin_pass']))
        {
            $this->ajaxResponse(1, '管理员账户和密码不得为空！');
        }

        //检查配置是否正确
        try
        {
            InstallLogic::chkConfig($dbConf);
        }
        catch (\Exception $ex)
        {
            //$msg = iconv("GB2312", "UTF-8", $ex->getMessage());
            $this->ajaxResponse(1, $ex->getMessage());
            //$this->ajaxResponse(1, 'sb');
        }


        $this->ajaxResponse(1, '正常！');

        die();

        //保存配置文件
        InstallLogic::setConfig($dbConf);


        if (!$rel['status'])
        {
            $this->ajaxResponse(2, $rel['message']);
        }


    }


}