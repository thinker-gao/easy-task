<?php

if (!function_exists('setConfig'))
{
    /**
     * 动态设置配置文件
     */
    function setConfig($key, $value)
    {
        //检查参数
        if (!$key || !$value) return false;

        //设置配置文件路径
        $configfile = CONF_PATH . 'config.php';

        //加载配置并设置配置
        $configdata = include $configfile;
        $configdata[$key] = $value;

        //重新拼接配置文件内容
        $configText = "<?php" . PHP_EOL . 'return [' . PHP_EOL;
        foreach ($configdata as $key => $item)
        {
            if (gettype($item) == 'boolean')
            {
                $item = $item ? 'true' : 'false';
            }
            else
            {
                $item = "'$item'";
            }

            $configText .= "'{$key}' => {$item}," . PHP_EOL;
        }
        $configText .= '];';

        //写入配置文件
        if (!@file_put_contents($configfile, $configText))
        {
            return false;
        };

        //返回
        return true;
    }
}