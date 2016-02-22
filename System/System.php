<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/11
 * Time: 15:53
 */
class System
{
    /*
     * 用于引入文件
     */
    static public function Load($func)
    {
        if (is_file($func))
            require_once($func);
        else
            Errors::Exception($func . " Existn't ,Please Check Again");
    }

    public function __construct()
    {
        /*
         * 自定义错误处理函数
         */
        date_default_timezone_set(SERVER_TIMEZONE);//设置服务器时区

        if (is_file(DIR_SYSTEM . "Errors.php"))//加载自定义错误处理函数
        {
            require(DIR_SYSTEM . "Errors.php");
            set_error_handler("Errors::ErrorHanding", E_USER_ERROR);
            set_error_handler("Errors::WarningHanding", E_USER_WARNING);
            set_error_handler("Errors::NoticeHanding", E_USER_NOTICE);
        } else {
            die(DIR_SYSTEM . "Errors.php Existn't ,Please Check Again");
        }

        if (IS_DEBUG)//判断是否打开调试
        {
            ini_set("display_errors", true);
        }

        if (IS_LOGS)//判断是否记录日志
        {
            System::Load(DIR_SYSTEM . "Log.php");
            Log::RecordUserMsg();
        }

        System::Load(DIR_SYSTEM . "Block.php");//加载模块模型类
        System::Load(DIR_SYSTEM . "App.php");//加载加载器
        System::Load(DIR_SYSTEM . "Controller.php");//加载控制器基类
        System::Load(DIR_SYSTEM . "Label.php");//加载标签类
    }

    /*
     * 启动函数
     */
    public function startup()
    {
        App::SetArgs();//装在用户传入参数
        App::LoadApp();//加载用户选择的应用
        App::Run();
    }
}