<?php
namespace Install\Logic;

use Think\Exception;

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
    public static function chkConfig($config)
    {
        $dsn = "{$config['DB_TYPE']}:host={$config['DB_HOST']};port={$config['DB_PORT']};charset={$config['DB_CHARSET']}";
        try
        {
            $pdo = new \PDO($dsn, $config['DB_USER'], $config['DB_PWD']);

            echo 111;
        }
        catch (\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }
    }
}