CrxzyOS_PHP
    Commom                  //共用文件层
    Home                    //应用跟目录
        Appname
            Controller.php
            Javascript
            Css
            Images
            Pages
                Page1.html
                Page2.html
            Component
                ComponentName
                    Model.php
                    View.html
                    Javascript
                    Css
                    Images
                ComponentName2
                    Model.php
                    View.html
                    Javascript
                    Css
                    Images

    Lib                     //存放开发库
    Logs                    //存放日志
    Models                  //存放模板
        App                 //APP模板
        Compontent          //组件模板
        Head                //头部连接模板
    System
        Server
            AppCreator.php  //应用创建器加载
        App.php             //应用加载器类
        Compontent.php      //组件基类
        Controller.php      //应用控制器基类
        Error.php           //自定义错误处理类
        Page.php            //页基类
        Log.php             //日志类
        System.php          //系统
    config.php              //系统配置文件
    index.php               //系统入口
    ReadMe.txt              //系统解释文件