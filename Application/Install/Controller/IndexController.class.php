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

        //获取网站域名
        $siteUrl = getServername();

        $this->assign('siteurl', $siteUrl);
        $this->assign('chkexts', $check->chkexts);
        $this->assign('chkrwpath', $check->chkrwpath);
        $this->assign('chkenvpass', $check->chkenvpass);
        $this->display();
    }

    /**
     * 保存配置
     */
    public function save()
    {
        //外部参数
        $dbConf = [
            'DB_TYPE' => 'mysql',// 数据库类型
            'DB_PREFIX' => 'light',// 数据表前缀
            'DB_CHARSET' => 'utf8',// 网站编码
            'DB_HOST' => trim(I('dbhost')),// 数据库地址
            'DB_PORT' => trim(I('dbport')),// 数据库端口
            'DB_NAME' => trim(I('dbname')),// 数据库名称
            'DB_USER' => trim(I('dbuser')),// 数据库用户名
            'DB_PWD' => trim(I('dbpass')),// 数据库密码
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

        //检查数据库配置并创建数据库
        try
        {
            InstallLogic::chkDbConfig($dbConf);
            InstallLogic::createDatebase();
            InstallLogic::importSqlData();
        }
        catch (\Exception $ex)
        {
            $this->ajaxResponse(1, iconv("GB2312", "UTF-8", $ex->getMessage()));
        }

        //保存配置文件
        InstallLogic::setConfig($dbConf);

        //运行时文件清理
        InstallLogic::deleteRuntime();

        //返回
        $this->ajaxResponse(0, 'success');
    }
}