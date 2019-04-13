<?php
namespace Install\Logic;

/**
 * 安装逻辑
 * @package Install\Logic
 */
class InstallLogic
{
    /**
     * PDO配置
     */
    public static $conf;

    /**
     * PDO连接对象
     */
    public static $conn;

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
     * 检查DB配置并初始化
     */
    public static function chkDbConfig($config)
    {
        try
        {
            static::$conf = $config;
            static::$conn = new \PDO("{$config['DB_TYPE']}:host={$config['DB_HOST']};port={$config['DB_PORT']}", $config['DB_USER'], $config['DB_PWD']);
        }
        catch (PDOException $e)
        {
            E($e->getMessage());
        }
    }

    /**
     * 创建数据库
     */
    public static function createDatebase()
    {
        $dbName = static::$conf['DB_NAME'];
        if (!static::$conn->exec("drop database if exists $dbName;create database $dbName;"))
        {
            $errMessage = static::$conn->errorInfo()['2'] ?? '';
            if ($errMessage)
            {
                E($errMessage);
            }
        }
    }

    /**
     * 运行时清理
     */
    public static function deleteRuntime()
    {
        $file = RUNTIME_PATH . 'common~runtime.php';
        return @unlink($file);
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

        //DB选择数据库
        $dbName = static::$conf['DB_NAME'];
        static::$conn->exec("use $dbName;");

        //DB执行SQL语句
        $sqlNode = explode(';', $sqlText);
        foreach ($sqlNode as $node)
        {
            $sql = trim($node);
            if (!$sql)
            {
                continue;
            }
            if (!static::$conn->exec($sql))
            {
                $errMessage = static::$conn->errorInfo()['2'] ?? '';
                if ($errMessage)
                {
                    E($errMessage);
                }
            }
        }
    }
}