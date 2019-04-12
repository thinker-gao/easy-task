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
        //$sql = "drop database if exists $dbname;create database $dbname;";
        //$model->execute($sql);
    }

    /**
     * SQL文件导入
     */
    public static function importSqlData()
    {
        //加载SQL文件
        $sqlFile = './Public/Install/sql/install.sql';
        $sqlText = file_get_contents($sqlFile);

        //过滤注释
        $sqlText = preg_replace('/(\/\*)(.*)(\*\/)/s', '', $sqlText);
        $sqlText = preg_replace('/-{2}\s-{28}(.*)-{2}\s-{28}/s', '', $sqlText);

        //循环执行SQL语句
        $model = new \Think\Model();
        $sqlNode = explode(';', $sqlText);
        foreach ($sqlNode as $node)
        {
            $sql = trim($node);
            if (!$sql)
            {
                continue;
            }

            //var_dump($sql);

            $model->execute($sql);
        }
    }
}