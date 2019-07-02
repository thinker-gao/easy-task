EasyTask 1.0 原生PHP常驻内存定时任务扩展
===============

> 运行环境要求PHP7.0+，推荐PHP7.1支持异步信号，7.1不依赖ticker(仅支持linux系统)

## 依赖扩展

* pcntl(一般默认自带)
* posix(一般默认自带)
* sysvmsg(需要自行安装)

## PHP扩展安装(推荐使用宝塔集成环境一键安装php扩展)


## 安装EasyTask只需要composer.json配置如下，然后执行composer update

~~~
  "require": {
    "easy-task/easy-task": "dev-master"
  }
~~~

如果需要更新框架使用
~~~
composer update topthink/framework
~~~

## 文档

[完全开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

## 参与开发

请参阅 [ThinkPHP 核心框架包](https://github.com/top-think/framework)。

## 版权信息

ThinkPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2019 by ThinkPHP (http://thinkphp.cn)

All rights reserved。

ThinkPHP® 商标和著作权所有者为上海顶想信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
