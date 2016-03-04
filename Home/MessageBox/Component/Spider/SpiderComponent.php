<?php
/**
 * Created by PhpStorm.
 * User: 旭阳
 * Date: 2016/3/3
 * Time: 0:08
 */

namespace MessageBox;


class SpiderComponent extends \Block
{
    private $postData = array(
        "__VIEWSTATE"=>"",
        "__VIEWSTATEGENERATOR"=>"",
        "txtUserName"=>"",
        "TextBox2"=>"",
        "txtSecretCode"=>"",
        "RadioButtonList1"=>"%D1%A7%C9%FA",
        "Button1"=>"",
        "lbLanguage"=>"",
        "hidPdrs"=>"",
        "hidsc"=>""
    );
    private $result = "";
    private $keycode = "";
    private $target = "";
    public $cookie_jar = "";
    private function Init()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->target);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
        $output=curl_exec($ch);
        curl_close($ch);
        if(preg_match("/Object moved to/i",$output))
        {
            preg_match("/<a href='\/\((\S*?)\)/",$output,$match);
            $_SESSION["keycode"] = $this->keycode = $match[1];
            $this->target = "http://jmis.buct.edu.cn/($this->keycode)/default2.aspx";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$this->target );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_AUTOREFERER,true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
            $output=curl_exec($ch);
            curl_close($ch);
        }
        $output = iconv("GB2312//IGNORE", "UTF-8", $output) ;
        file_put_contents("test.txt",$output);

        preg_match_all("/<input type=\"h[\S\s]*?ue=\"(\S*)\"/i",$output,$match);
        $_SESSION["__VIEWSTATE"]=$match[1][0];
        if(sizeof($match[1])==2)
        {
            $_SESSION["__VIEWSTATEGENERATOR"]=$match[1][1];
        }
        $_SESSION["Target"] = $this->target;
        $this->GetCheckCode();
    }
    private function DateToWeek($args)
    {
        if(preg_match('/^(\S*?)\x{5e74}(\S*?)\x{6708}(\S*?)\x{65e5}$/iu',$args,$buff)||preg_match('/^(\S*?)\/(\S*?)\/(\S*?)$/iu',$args,$buff))
        {
            array_shift($buff);
            foreach($buff as $key => $val)
                $buff[$key] = intval($val);
            if($buff[1]==1||$buff[1]==2)//判断month是否为1或2　
            {
                $buff[0]--;
                $buff[1]+=12;
            }
            $c=intval($buff[0]/100);
            $y=$buff[0]%100;
            $week=(intval($c/4)-2*$c+($y+intval($y/4))+(13*intval(($buff[1]+1)/5))+$buff[2]-1)%7;
            $Week[] = $week<=0?$week+7:$week;
            switch( $Week[0])
            {
                case 1:
                    $Week[] = "星期一";
                    break;
                case 2:
                    $Week[] = "星期二";
                    break;
                case 3:
                    $Week[] = "星期三";
                    break;
                case 4:
                    $Week[] = "星期四";
                    break;
                case 5:
                    $Week[] = "星期五";
                    break;
                case 6:
                    $Week[] = "星期六";
                    break;
                case 7:
                    $Week[] = "星期日";
                    break;
            }
            return $Week;
        }
        else
        {
            die("日期格式有误,请使用如（1980年1月1日）或（1980/1/1）格式");
            return false;
        }
    }
    private function GetCheckCode()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
        curl_setopt($ch,CURLOPT_REFERER,"http://jmis.buct.edu.cn/".($this->keycode!=""?"($this->keycode)/":"")."CheckCode.aspx");
        curl_setopt($ch, CURLOPT_URL, "http://jmis.buct.edu.cn/".($this->keycode!=""?"($this->keycode)/":"")."CheckCode.aspx");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);
        curl_setopt($ch,  CURLOPT_FOLLOWLOCATION, 1);
        $output=curl_exec($ch);
        file_put_contents("test.gif",$output);
        curl_close($ch);
    }
    public function Start()
    {
        $this->cookie_jar = \App::$Path."pic.cookie";
        $this->target = "http://jmis.buct.edu.cn/default2.aspx";

        if(!isset($_SESSION["__VIEWSTATE"]))
        {
            $this->Init();
        }
    }
    public function Form()
    {
        $ch = curl_init();
        $this->postData["__VIEWSTATE"] = $_SESSION["__VIEWSTATE"];
        $this->postData["__VIEWSTATEGENERATOR"] = isset($_SESSION["__VIEWSTATEGENERATOR"])?$_SESSION["__VIEWSTATEGENERATOR"]:"";
        $this->postData["txtUserName"] = $_POST["username"];
        $this->postData["TextBox2"] = $_POST["password"];
        $this->postData["txtSecretCode"] = $_POST["checkcode"];
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
        curl_setopt($ch,CURLOPT_REFERER,$_SESSION["Target"]);
        curl_setopt($ch, CURLOPT_URL, $_SESSION["Target"]);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($this->postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);
        curl_setopt($ch,  CURLOPT_FOLLOWLOCATION, 1);
        $output=curl_exec($ch);
        $output = iconv("GB2312//IGNORE", "UTF-8", $output) ;
        curl_close($ch);
        if(preg_match("/欢迎您/i",$output))
        {
            echo "操作成功！";
            unset($_SESSION["__VIEWSTATE"]);
        }
        else if(preg_match("/ERROR/i",$output)){
            echo "系统繁忙，请稍后重试！";
            unset($_SESSION["__VIEWSTATE"]);
            return 0;
        }else {
            preg_match("/alert\('([\s\S]*?)'\)/",$output,$match);
            echo $match[1];
            unset($_SESSION["__VIEWSTATE"]);
            return 0;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
        curl_setopt($ch,CURLOPT_REFERER,"http://jmis.buct.edu.cn/".($_SESSION["keycode"]!=""?"({$_SESSION["keycode"]})/":"")."xs_main.aspx?xh=2013014106");
        curl_setopt($ch, CURLOPT_URL, "http://jmis.buct.edu.cn/".($_SESSION["keycode"]!=""?"({$_SESSION["keycode"]})/":"")."xskbcx.aspx?xh=2013014106&xm=%B3%C2%D0%F1%D1%F4&gnmkdm=N121603");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);
        curl_setopt($ch,  CURLOPT_FOLLOWLOCATION, 1);
        $output = curl_exec($ch);
        $output = iconv("GB2312//IGNORE", "UTF-8", $output) ;
        file_put_contents("test.html",$output);
        preg_match('/<table id=\"Table1\"[\s\S]*?>([\S\s]*?)<\/table>/i',$output,$Page);
        preg_match_all('/<td align="Center"[^>]*?>([^&]*?)<\/td>/i',$Page[0],$Page);
        $checkexam = array();
        foreach($Page[1] as $var)
        {
            if(preg_match('/(\x{661f}\x{671f})/u',$var))
                continue;
            $vars = explode("<br>",$var);
            preg_match('/\x{5468}([\S]*?)\x{7b2c}([\S]*?)\x{8282}\{\x{7b2c}([\S]*?)\x{5468}\}/iu',$vars[1],$buff);
            array_shift($buff);
            switch($buff[0])
            {
                case '一':
                    $buff[0] = 1;
                    break;
                case '二':
                    $buff[0] = 2;
                    break;
                case '三':
                    $buff[0] = 3;
                    break;
                case '四':
                    $buff[0] = 4;
                    break;
                case '五':
                    $buff[0] = 5;
                    break;
                case '六':
                    $buff[0] = 6;
                    break;
                case '七':
                    $buff[0] = 7;
            }
            $vars[1] = $buff;
            $buff[1] = explode(',',$buff[1]);

            foreach ($buff[1] as $buff2)
            {
                $Class["course_num"] = md5($vars[2].$vars[0]);
                $Class["week"] = $buff[0];
                $Class["time"] = $buff2;
                $Class["course_name"] = $vars[0];
                $Class["course_interval"] = $vars[1][2];
                $Class["teacher"] = $vars[2];
                $Class["classroom"] = $vars[3];

                $this->result["Course"][] = $Class;
            }

            if (isset($vars[4]))
            {
                preg_match("/([\S]*?)\(/", $vars[4], $buff);
                preg_match('/\((\S*?)\)/', $vars[4], $buff2);

                $Week = $this->DateToWeek($buff[1]);

                $Exam['exam_num'] = md5($vars[0].$buff2[1]);
                $Exam['exam_name'] = $vars[0];
                $Exam['week'] = $Week[0];
                $Exam['date'] = $buff[1];
                $Exam['time'] = $buff2[1];

                if(!isset($checkexam[$Week[0]][$Exam['time']]))
                {
                    $checkexam[$Week[0]][$Exam['time']]=1;
                    $this->result["Exam"][] = $Exam;
                }
            }
        }
        var_dump($this->result);
        foreach($this->result["Course"] as $value){
            $count = $this->db->view("course")->where("`course_num`='{$value["course_num"]}' && `week`='{$value["week"]}' &&　`time`='{$value["time"]}'")->select()->num;
            if(!$count)
                $this->db->view("course")->insert($value);
        }
        foreach($this->result["Exam"] as $value){
            $count = $this->db->view("exam")->where("`exam_num`='{$value["exam_num"]}'")->select()->num;
            if(!$count)
                $this->db->view("exam")->insert($value);
        }
    }
}