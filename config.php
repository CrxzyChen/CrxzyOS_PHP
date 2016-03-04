<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/11
 * Time: 15:52
 */
session_start();//开启session 功能

/*
 * 加载系统配置
 */

define("IS_DEBUG",true);//是否开启调试模式
define("IS_LOGS",true);//是否开启日志
define("IS_INTELLISENCE",true);//是否开启智能提示
define("IS_DEFAULTHEAD",true);//是否使用默认前端头

define("SYSTEM_VERSION", '0.0.2.0');//设置系统版本号
define("SYSTEM_CHARSET","utf-8");//设置系统默认字符
define("SYSTEM_LANGUAGE","ch_zh");//设置系统默认语言
define("SYSTEM_DEFAULTPAGE","index");//设置默认Page

/*
 * 定义服务器基本信息基本信息
 */
define("SERVER_NAME",$_SERVER['SERVER_NAME']);//记录服务器名
define("SERVER_TIMEZONE","PRC");//设置时区
define("SERVER_PORT",$_SERVER['SERVER_PORT']);//记录服务器端口
define("SERVER_ADD",$_SERVER['SCRIPT_NAME']);//记录系统入口地址
define("SERVER_ROOT",($_SERVER[HTTPS]?"https://":"http://").SERVER_NAME.((SERVER_PORT==80)?"":(":".SERVER_PORT)).(dirname(SERVER_ADD)!="\\"?dirname(SERVER_ADD):dirname(SERVER_ADD).DIRECTORY_SEPARATOR));//记录客户端根目录
define("SERVER_COMMOM",SERVER_ROOT."Common".DIRECTORY_SEPARATOR);//记录客户端Common地址
define("SERVER_HOME",SERVER_ROOT."Home".DIRECTORY_SEPARATOR);//记录客户端HOME地址
define("SERVER_COMPONENT",SERVER_ROOT."Components".DIRECTORY_SEPARATOR);//记录客户端HOME地址
/*
 * 记录访问者信息
 */
define("USER_REMOTE_ADDR",$_SERVER['REMOTE_ADDR']);//记录浏览当前页面用户IP地址
define("USER_REMOTE_HOST",$_SERVER['REMOTE_HOST']);//记录浏览当前页面用户主机名
define("USER_REMOTE_PORT",$_SERVER['REMOTE_PORT']);//用户连接到服务器时所使用的端口
define("REQUEST_REMOTE_URL",$_SERVER['REQUEST_URI'] );//记录浏览页面的URL


/*
 * 定义框架基本路径
 */
define("DIR_ROOT",dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR);
define("DIR_SYSTEM",DIR_ROOT."System".DIRECTORY_SEPARATOR);
define("DIR_LIB",DIR_ROOT."Lib".DIRECTORY_SEPARATOR);
define("DIR_HOME",DIR_ROOT."Home".DIRECTORY_SEPARATOR);
define("DIR_LOGS",DIR_ROOT."Logs".DIRECTORY_SEPARATOR);
define("DIR_COMMON",DIR_ROOT."Common".DIRECTORY_SEPARATOR);
define("DIR_MODELS",DIR_ROOT."Models".DIRECTORY_SEPARATOR);
define("DIR_COMPONENTS",DIR_ROOT."Components".DIRECTORY_SEPARATOR);
/*
 * 加载数据库信息
 */
define("DB_USERNAME","root");
define("DB_ADDREASS","127.0.0.1:3306");
define("DB_PASSWORD","Crxzy123520");