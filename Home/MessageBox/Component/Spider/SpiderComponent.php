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
    private $username="";
    public $header = "jwgl";
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
            $this->target = "http://$this->header.buct.edu.cn/($this->keycode)/default2.aspx";
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
        curl_setopt($ch,CURLOPT_REFERER,"http://$this->header.buct.edu.cn/".($this->keycode!=""?"($this->keycode)/":"")."CheckCode.aspx");
        curl_setopt($ch, CURLOPT_URL, "http://$this->header.buct.edu.cn/".($this->keycode!=""?"($this->keycode)/":"")."CheckCode.aspx");
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
        $this->target = "http://$this->header.buct.edu.cn/default2.aspx";

        if(!isset($_SESSION["__VIEWSTATE"]))
        {
            $this->Init();
        }
    }
    public function Login(){
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
        file_put_contents("test.html",$output);

        curl_close($ch);
        if(preg_match("/欢迎您/i",$output))
        {
            echo "操作成功！";
            unset($_SESSION["__VIEWSTATE"]);
            preg_match("/>(\S*)同学/i",$output,$match);
            $this->username = iconv("UTF-8","GB2312//IGNORE",$match[1]);
            return true;
        }
        else if(preg_match("/ERROR/i",$output)){
            echo "系统繁忙，请稍后重试！";
            unset($_SESSION["__VIEWSTATE"]);
            return false;
        }else {
            preg_match("/alert\('([\s\S]*?)'\)/",$output,$match);
            echo $match[1];
            unset($_SESSION["__VIEWSTATE"]);
            return false;
        }
    }
    public function GetSchedule()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
        curl_setopt($ch,CURLOPT_REFERER,"http://$this->header.buct.edu.cn/".($_SESSION["keycode"]!=""?"({$_SESSION["keycode"]})/":"")."xs_main.aspx?xh={$_POST["username"]}");
        curl_setopt($ch, CURLOPT_URL, "http://$this->header.buct.edu.cn/".($_SESSION["keycode"]!=""?"({$_SESSION["keycode"]})/":"")."xskbcx.aspx?xh={$_POST["username"]}&xm=$this->username&gnmkdm=N121603");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);
        curl_setopt($ch,  CURLOPT_FOLLOWLOCATION, 1);
        $output = curl_exec($ch);
        $output = iconv("GB2312", "UTF-8//IGNORE", $output) ;
        file_put_contents("test.html",$output);

        /*
         * 获取当前学期
         */
        preg_match_all("/selected[\s\S]*?>([\s\S]*?)</",$output,$match);
        $this->result["Time"]["year"] = $match[1][0];
        $this->result["Time"]["term"] = $match[1][1];
        /*
         * 获取学生信息
         */
        preg_match("/学号：([\s\S]*?)</",$output,$match);
        $this->result["Student"]["student_num"] = $match[1];
        preg_match("/姓名：([\s\S]*?)</",$output,$match);
        $this->result["Student"]["student_name"] = $match[1];
        preg_match("/行政班：([\s\S]*?)</",$output,$match);
        $this->result["Student"]["student_class"] = $match[1];
        preg_match("/专业：([\s\S]*?)</",$output,$match);
        $this->result["Student"]["student_major"] = $match[1];
        preg_match("/学院：([\s\S]*?)</",$output,$match);
        $this->result["Student"]["student_academy"] = $match[1];
        /*
         * 获取课程信息
         */
        preg_match('/<table id=\"Table1\"[\s\S]*?>([\S\s]*?)<\/table>/i',$output,$Page);
        preg_match_all("/<tr>([\s\S]*?)<\/tr>/",$Page[0],$match);
        array_shift($match[1]);
        array_shift($match[1]);
        $table=array(array());//用于记录表格指针
        for($i = 0;$i<13;$i++)
        {
            for($j=1;$j<=7;$j++)
                $table[$i][$j]=0;
        }
        foreach($match[1] as $key=>$value)
        {
            $y=1;
            preg_match_all("/<td[\s\S]*?>([\s\S]*?)<\/td>/",$value,$buff);
            foreach($buff[0] as $key2=>$value2)
            {
                while($table[$key][$y])
                {
                    $y++;
                }

                if(preg_match("/<td[^>]*?>(上午|下午|晚上)<\/td>/",$value2))
                    continue;
                if(preg_match("/<td[^>]*?>第\S{1,2}节<\/td>/",$value2))
                {
                    continue;
                }
                if(preg_match("/<td[^>]*?>&nbsp;<\/td>/",$value2))
                {
                    $y++;
                    continue;
                }


                preg_match("/<td[^>]*?rowspan=\"(\S*)\"[^>]*?>([\s\S]*?)<\/td>/",$value2,$buff2);
                if(!sizeof($buff2))
                {
                    preg_match("/<td[^>]*?>([\s\S]*?)<\/td>/",$value2,$buff2);
                    $buff2[2]=$buff2[1];
                    $buff2[1]=1;
                }

                for($i = 0;$i<$buff2[1];$i++)
                    $table[$key+$i][$y]=1;

                $buff2[2] = explode("<br>",$buff2[2]);
                preg_match("/\{([\s\S]*?)\}/",$buff2[2][1],$buff3);
                $Class["course_interval"] = $buff3[1];
                $Class["course_num"] = md5($buff2[2][0].$buff2[2][2]);
                $Class["week"] = $y;
                $Class["course_name"] = $buff2[2][0];
                $Class["teacher"] = $buff2[2][2];
                $Class["classroom"] = $buff2[2][3];
                switch(sizeof($buff2[2]))
                {
                    case 5:
                        preg_match("/([\S]*?)\(/", $buff2[2][4], $buff3);
                        preg_match('/\((\S*?)\)/', $buff2[2][4], $buff4);

                        $Week = $this->DateToWeek($buff3[1]);

                        $Exam['exam_num'] = md5($buff2[2][0].$buff4[1]);
                        $Exam['exam_name'] = $buff2[2][0];
                        $Exam['week'] = $Week[0];
                        $Exam['date'] = $buff3[1];
                        $Exam['time'] = $buff4[1];

                        if(!isset($checkexam[$Week[0]][$Exam['time']]))
                        {
                            $checkexam[$Week[0]][$Exam['time']]=1;
                            $this->result["Exam"][] = $Exam;
                        }
                        break;
                    case 9:
                        for($i = 0;$i<$buff2[1];$i++)
                        {
                            $Class["time"] = $key+1+$i;
                            $this->result["Course"][] = $Class;
                        }
                        var_dump($buff2[2]);
                        $Class["course_num"] = md5($buff2[2][5].$buff2[2][7]);
                        $Class["week"] = $y;
                        $Class["course_name"] = $buff2[2][5];
                        $Class["teacher"] = $buff2[2][7];
                        $Class["classroom"] = $buff2[2][8];
                        break;
                }

                for($i = 0;$i<$buff2[1];$i++)
                {
                    $Class["time"] = $key+1+$i;
                    $this->result["Course"][] = $Class;
                }
                $y++;
            }
        }

    }
    public function Save()
    {
        $count = $this->db2->view("student")->where("`student_num`='{$this->result["Student"]["student_num"]}'")->select()->num;
        if(!$count)
        {
            $this->db2->view("student")->insert($this->result["Student"]);
        }
        foreach($this->result["Course"] as $value){
            $count = $this->db2->view("course")->where("`course_num`='{$value["course_num"]}' && `week`='{$value["week"]}' && `time`='{$value["time"]}'")->select()->num;
            if(!$count)
                $this->db2->view("course")->insert($value);
            $count = $this->db2->view("student_course")->where("`course_num`='{$value["course_num"]}' && `student_num`='{$this->result["Student"]["student_num"]}'")->select()->num;
            if(!$count)
            {
                $array = array("student_num"=>$this->result["Student"]["student_num"],"course_num"=>$value["course_num"]);
                $array = array_merge($array,$this->result["Time"]);
                $this->db2->view("student_course")->insert($array);
            }
        }
        foreach($this->result["Exam"] as $value){
            $count = $this->db2->view("exam")->where("`exam_num`='{$value["exam_num"]}'")->select()->num;
            if(!$count)
                $this->db2->view("exam")->insert($value);
            $count = $this->db2->view("student_exam")->where("`exam_num`='{$value["exam_num"]}' && `student_num`='{$this->result["Student"]["student_num"]}'")->select()->num;
            if(!$count)
            {
                $array = array("student_num"=>$this->result["Student"]["student_num"],"exam_num"=>$value["exam_num"]);
                $array = array_merge($array,$this->result["Time"]);
                $this->db2->view("student_exam")->insert($array);
            }
        }
    }
    public function Form()
    {
        if($this->Login())
        {
            $this->GetSchedule();
            $this->Save();
        }
    }
}