<?php
namespace Install\Logic;

/**
 * 安装逻辑
 * @package Install\Logic
 */
class InstallLogic
{
    /**
     * 保存配置到配置文件
     * @param array $config
     */
    public static function setConfig($config = [])
    {
        foreach ($config as $key => $item)
        {
            setConfig($key, $item);
        }
    }

    /**
     * 检查配置文件是否正确
     */
    public static function createDatabase($dbname)
    {
        $model = new \Think\Model();
        $sql = "drop database if exists $dbname;create database $dbname;";
        $model->execute($sql);
    }

    /**
     * SQL文件导入
     */
    public static function importSqlData()
    {
        $sqlFile = './Public/Install/sql/install.sql';
        $sqlText = file_get_contents($sqlFile);

        echo $sqlText;

        $model = new \Think\Model();
        $model->execute($sqlText);
    }
}