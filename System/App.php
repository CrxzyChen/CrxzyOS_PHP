<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/11
 * Time: 15:39
 */
class App
{
    static public $Appname;
    static public $Page;
    static public $Args;
    static public $Path;
    static public $MainController;
    static public $Application;
    static public $Config;
    static public $Cache;
    static public $CurrentPage;
    static public $CurrentComponentName;
    static public $CurrentRequireKind;

    /*
     * 压缩html
     */
    static private function CompressHtml($html_source)
    {
        preg_match_all("/<\s*script(\s*\S*\s*=\s*(['\"])\S*\\2)*\s*(>)[\s\S]*?(<)\/\s*script\s*>/",$html_source,$match,PREG_OFFSET_CAPTURE);
        /*处理script标签内部压缩问题*/
        $match[4]=array_reverse($match[4]);
        $match[3]=array_reverse($match[3]);
        foreach($match[4] as $key => $value)
        {
            if(!($len=($value[1]-$match[3][$key][1]-1)))
                continue;
            $pos = $match[3][$key][1]+1;
            $buf = substr($html_source,$match[3][$key][1]+1,$len);
            $buf = preg_replace('/\/\/.*([\\r\\n])/', ' ', $buf);
            $string = str_split($html_source,$pos);
            $head = array_shift($string);
            $string = str_split(implode($string),$len);
            array_shift($string);
            $end =implode($string);
            $html_source = $head.$buf.$end;
        }
        $chunks = preg_split('/(<pre.*?\/pre>)/ms', $html_source, -1, PREG_SPLIT_DELIM_CAPTURE);
        $html_source = '';//[higrid.net]修改压缩html : 清除换行符,清除制表符,去掉注释标记
        foreach ($chunks as $c) {
            if (strpos($c, '<pre') !== 0) {//[higrid.net] remove new lines & tabs
                $c = preg_replace('/[\\n\\r\\t]+/', ' ', $c);// [higrid.net] remove extra whitespace
                $c = preg_replace('/\\s{2,}/', ' ', $c);// [higrid.net] remove inter-tag whitespace
                $c = preg_replace('/>\\s</', '><', $c);// [higrid.net] remove CSS & JS comments
                $c = preg_replace('/\\/\\*.*?\\*\\//i', '', $c);
            }
            $html_source .= $c;
        }
        return $html_source;
    }
    static public function Find($name)
    {
        if($name=="root")
            return self::$Application;
        foreach(self::$Page as $key=>$value)
        {
            if($key==$name)
                return self::$Application->{self::$CurrentPage};
        }
        return self::$Application->{self::$CurrentPage}->Find($name);
    }
    static public function Run()//运行Application
    {
        /*
        * 如果用户没有指定加载页，则读取默认页
        */
        if (self::$CurrentPage == null) {
            self::$CurrentPage = self::$Config["default_page"];
        }

        $class = new ReflectionClass(self::$MainController);

        self::$Application = $class->newInstance();


        if(self::$CurrentRequireKind!="")
        {
            self::Find(self::$CurrentComponentName)->{self::$CurrentRequireKind}();
        }else{
            self::$Application->{self::$CurrentPage}();
        }
    }
    static public function View($Page)
    {
        if (IS_DEBUG || !isset(self::$Cache[$Page])) {
            self::compile();
            return self::$Application->{self::$CurrentPage}->View;
        } else {
            $FileName=md5(self::$Appname.$Page);
            return self::$Cache[$FileName];
        }
    }
    /*
     * 编译生成缓冲前端
     */
    static public function compile()
    {
        /*
        * 生成ViewHead
        */
        $head = '<head>';
        self::$Application->{self::$CurrentPage}->LoadHead();
        foreach (self::$Application->{self::$CurrentPage}->Head as $heads) {
            $head .=$heads->Html();
        }
        $head .= "</head>";

        $body = "<body>" . self::$Application->{self::$CurrentPage}->RenderView() . "</body>";
        self::$Application->{self::$CurrentPage}->View = self::CompressHtml("<!DOCTYPE html><html>" . $head . $body . "</html>");

        $FileName=md5(self::$Appname.self::$CurrentPage);
        file_put_contents(self::$Path . "Pages" . DIRECTORY_SEPARATOR . "Cache" . DIRECTORY_SEPARATOR . $FileName . ".html", self::$Application->{self::$CurrentPage}->View);//写入缓存文件
    }

    static public function SetPage($pagename)
    {
        self::$CurrentPage = $pagename;
    }

    static public function LoadCache()//加载已经形成的前端缓冲
    {
        if(file_exists(self::$Path . "Pages" . DIRECTORY_SEPARATOR . "Cache"))
        {
            $dir = dir(self::$Path . "Pages" . DIRECTORY_SEPARATOR . "Cache");
            $cache = array();
            while ($file = $dir->read()) {
                if (preg_match("/([\s\S]*).html/i", $file, $match)) {
                    $cache[$match[1]] = file_get_contents(self::$Path . "Pages" . DIRECTORY_SEPARATOR . "Cache" . DIRECTORY_SEPARATOR . $file);
                }
            }
            self::$Cache = $cache;
        }else{
            mkdir(self::$Path . "Pages" . DIRECTORY_SEPARATOR . "Cache");
        }
    }

    static public function LoadConfig()//加载应用设置
    {
        /*
         * 读取全局设置
         */
        self::$Config['charset'] = SYSTEM_CHARSET;
        self::$Config['language'] = SYSTEM_LANGUAGE;
        self::$Config['default_page'] = SYSTEM_DEFAULTPAGE;
        /*
         * 加载应用设置
         */
        if (is_file(self::$Path . "config.php")) {
            require self::$Path . "config.php";
            foreach (call_user_func(self::$Appname . "\\" . "config::Setting") as $setting) {
                self::$Config[$setting[0]] = $setting[1];
            };
        }
    }

    static public function LoadApp()//加载应用
    {
        /*
         * 查看应用是否存在
         */
        if (is_file(DIR_HOME . self::$Appname . DIRECTORY_SEPARATOR . self::$Appname . "Controller.php")) {
            require(DIR_HOME . self::$Appname . DIRECTORY_SEPARATOR . self::$Appname . "Controller.php");
            if (class_exists(self::$Appname . "\\" . self::$Appname . "Controller")) {

                self::$MainController = self::$Appname . "\\" . self::$Appname . "Controller";
            } else {
                self::$MainController = "Controller";
            }
        } else
            Errors::Exception(DIR_HOME . self::$Appname . DIRECTORY_SEPARATOR . self::$Appname . "Controller.php Existn't ,Please Check Again");
        /*
         * 加载引用设置
         */
        self::$Path = DIR_HOME . self::$Appname . DIRECTORY_SEPARATOR;//设置软件更路径


        self::LoadConfig();
        self::LoadPage();
        self::LoadCache();
    }

    static public function LoadPage()//挂载应用页面
    {
        $dir = dir(App::$Path . "Pages");
        while ($file = $dir->read()) {
            if (preg_match("/^[\s\S]*?.html$/i", $file)) {
                $content = file_get_contents(App::$Path . "Pages" . DIRECTORY_SEPARATOR . $file);//获取文件名中内容
                $file = str_replace(".html", "", $file);//去除文件名中的.html
                $compenent = array();
                /*
                 * 查看是否存在Page层控制器，若存在，利用该子类立Page模型，若不存在在使用基类建立Page模型
                 */
                if (is_file(App::$Path . "Pages" . DIRECTORY_SEPARATOR . $file . "Page.php")) {
                    require(App::$Path . "Pages" . DIRECTORY_SEPARATOR . $file . "Page.php");
                    if (class_exists(self::$Appname . "\\" . $file . "Page")) {
                        $class = new ReflectionClass(self::$Appname . "\\" . $file . "Page");
                        self::$Page[$file] = $class->newInstance($content);
                    } else {
                        Errors::Exception(self::$Appname . "\\" . $file . "Controller Exist Please Try Again");
                    }
                } else {
                    self::$Page[$file] = new Block($content);
                }
            }
        }
    }

    static public function SetArgs()
    {
        self::$Args["GET"] =& $_GET;
        self::$Args["POST"] =& $_POST;
        self::$Args["FILE"] =& $_FILES;
        self::$Args["COOKIE"] =& $_COOKIE;
        self::$Args["SESSION"] =& $_SESSION;
        /*
         * 获取应用名
         */
        if (isset(self::$Args["GET"]["appname"])) {
            self::$Appname = self::$Args["GET"]["appname"];
        } else if (isset(self::$Args["POST"]["appname"])) {
            self::$Appname = self::$Args["POST"]["appname"];
        } else if (isset(self::$Args["SESSION"]["appname"])) {
            self::$Appname = self::$Args["SESSION"]["appname"];
        } else if (isset(self::$Args["COOKIE"]["appname"])) {
            self::$Appname = self::$Args["COOKIE"]["appname"];
        } else {
            self::$Appname = "System";//如果都没有则进入系统桌面
        }
        /*
         * 获取应用页面
         */
        self::$Args["SESSION"]["appname"] = self::$Appname;//记录当前应用

        if (isset(self::$Args["GET"]["page"])) {
            self::$CurrentPage = self::$Args["GET"]["page"];
        } else if (isset(self::$Args["POST"]["page"])) {
            self::$CurrentPage = self::$Args["POST"]["page"];
        } else if (isset(self::$Args["SESSION"]["page"])) {
            self::$CurrentPage = self::$Args["SESSION"]["page"];
        } else if (isset(self::$Args["COOKIE"]["page"])) {
            self::$CurrentPage = self::$Args["COOKIE"]["page"];
        }
        self::$Args["SESSION"]["page"] = self::$CurrentPage ;//记录当前页面

        if (isset(self::$Args["GET"]["componentName"])) {
            self::$CurrentComponentName = self::$Args["GET"]["componentName"];
        } else if (isset(self::$Args["POST"]["componentName"])) {
            self::$CurrentComponentName = self::$Args["POST"]["componentName"];
        } else if (isset(self::$Args["SESSION"]["componentName"])) {
            self::$CurrentComponentName = self::$Args["SESSION"]["componentName"];
        } else if (isset(self::$Args["COOKIE"]["componentName"])) {
            self::$CurrentComponentName = self::$Args["COOKIE"]["componentName"];
        }

        if (isset(self::$Args["GET"]["requireKind"])) {
            self::$CurrentRequireKind = self::$Args["GET"]["requireKind"];
        } else if (isset(self::$Args["POST"]["requireKind"])) {
            self::$CurrentRequireKind = self::$Args["POST"]["requireKind"];
        } else if (isset(self::$Args["SESSION"]["requireKind"])) {
            self::$CurrentRequireKind = self::$Args["SESSION"]["requireKind"];
        } else if (isset(self::$Args["COOKIE"]["requireKind"])) {
            self::$CurrentRequireKind = self::$Args["COOKIE"]["requireKind"];
        }
    }
}