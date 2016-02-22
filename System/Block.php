<?php

/**
 * Created by PhpStorm.
 * User: ����
 * Date: 2016/1/31
 * Time: 23:59
 */
class Block
{
    public $Content = '';
    public $View = '';
    public $Title = '';
    public $Component = array();
    public $Head = array();
    public $Body = '';
    public $Attrute = '';
    public $IsCompletion = '';//�Ƿ��������ṹ

    /*
     * ���ڴ��
     */
    public function Find($name)
    {
        if($this->Component)
        {
            foreach($this->Component as $key=>$value)
            {
                if($key==$name)
                    return $value;
                else
                {
                    if(($result=$value->Find($name))!=null)
                        return $value->Find($name);
                }
            }
            return null;
        }
        else
            return null;
    }
    public function LoadHead()
    {
        /*
         * ����Ĭ��ͷ
         */
        if (IS_DEFAULTHEAD) {
            $head = file_get_contents(DIR_MODELS . "Head/DefaultHead.html");
            $this->AnalyHead($head);
        }
        $this->GetHead();
        $this->ClearRepetitionHead();
    }

    public function GetHead()
    {
        $this->AnalyHead();
        if ($this->Component) {
            foreach ($this->Component as $component) {
                $this->Head = array_merge($this->Head, $component->GetHead());
            }
        }
        return $this->Head;
    }

    public function ClearRepetitionHead()//��ȥ�ظ�ͷ�ļ���Ϣ
    {
        $SRC = array();
        foreach ($this->Head as $key => $heads) {
            if ($heads->Name == "script" && $heads->Attribute["src"] != null) {
                foreach ($SRC as $src) {
                    if ($src == $heads->Attribute["src"]) {
                        unset($this->Head[$key]);
                        goto out;
                    }
                }
                $SRC[] = $heads->Attribute["src"];
            }
            out:
        }
    }

    public function LoadComponent()
    {
        if (preg_match_all("/(<)component[\s\S]*?(>)<\/component(>)/i", $this->Body, $match, PREG_OFFSET_CAPTURE)) {
            foreach ($match[0] as $key => $components) {
                $components = $components[0];
                $Attrute = array();
                $Attrute["begin"] = $match[1][$key][1];
                $Attrute["end"] = $match[3][$key][1] + 1;
                $Attrute["offset"] = $match[2][$key][1] + 1;//��¼��������ַ
                preg_match_all("/(\S*)\s*=\s*(['\"])([\S\s]*?)\\2/i", $components, $val);
                foreach ($val[1] as $key=>$value) {
                    $val[3][$key] = preg_replace("/\s+/i"," ",$val[3][$key]);
                    $val[3][$key] = trim($val[3][$key]);
                    if(sizeof($buf = explode(" ",$val[3][$key]))>1)
                    {
                        $Attrute[$value] = $buf;
                    }else
                    {
                        $Attrute[$value] = $val[3][$key];
                    }

                }
                if (!isset($Attrute["name"]))
                    $Attrute["name"] = $Attrute["component"];
                if (is_dir(App::$Path . "Component" . DIRECTORY_SEPARATOR . $Attrute["component"])) {
                    System::Load(App::$Path . "Component" . DIRECTORY_SEPARATOR . $Attrute["component"] . DIRECTORY_SEPARATOR . $Attrute["component"] . "Component.php");
                    $class = new ReflectionClass(App::$Appname . "\\" . $Attrute["component"] . "Component");
                    if (is_file(App::$Path . "Component" . DIRECTORY_SEPARATOR . $Attrute["component"] . DIRECTORY_SEPARATOR . (isset($Attrute["view"])?$Attrute["view"].".html":"index.html"))) {
                        $obj = $class->newInstance(file_get_contents(App::$Path . "Component" . DIRECTORY_SEPARATOR . $Attrute["component"] . DIRECTORY_SEPARATOR . "index.html"));
                    } else {
                        $obj = $class->newInstance("");
                    }
                } else if (is_dir(DIR_COMPONENTS . $Attrute["component"])) {
                    System::Load(DIR_COMPONENTS . $Attrute["component"] . DIRECTORY_SEPARATOR . $Attrute["component"] . "Component.php");
                    $class = new ReflectionClass($Attrute["component"] . "\\" . $Attrute["component"] . "Component");
                    if (is_file(DIR_COMPONENTS . $Attrute["component"] . DIRECTORY_SEPARATOR . "index.html")) {
                        $obj = $class->newInstance(file_get_contents(DIR_COMPONENTS . $Attrute["component"] . DIRECTORY_SEPARATOR . (isset($Attrute["view"])?$Attrute["view"].".html":"index.html")));
                    } else {
                        $obj = $class->newInstance("");
                    }
                } else {
                    Errors::Exception("{$Attrute["class"]} Component Exist Please Check Again");
                }
                $obj->Attrute = $Attrute;
                foreach ($Attrute as $key => $value) {
                    if (isset($obj->{$key})) {
                        $obj->{$key} = $value;
                    }
                }
                $component[$Attrute["name"]] = $obj;
            }
            $this->Component = $component;
        }
    }

    public function RenderView()
    {
        $body = $this->Body;
        if ($this->Component) {
            $compontents = array_reverse($this->Component);
            foreach ($compontents as $compontent) {

                if ($compontent->Component) {
                    $compontent->View = $compontent->RenderView();
                } else {
                    $compontent->View = $compontent->Body;
                    if (preg_match_all("/\\$([^ };\"]+)/", $compontent->View, $match))//���view��$��ͷ���ַ���
                    {

                        foreach ($match[0] as $key => $value) {
                            $compontent->View = str_replace($value, $compontent->{trim($match[1][$key])}, $compontent->View);
                        }
                    }
                }
                if (isset($compontent->Attrute["display"]) && $compontent->display == "hidden") {
                    $buff = str_split($body, $compontent->begin);
                    $buff2 = str_split($body, $compontent->end);
                    array_shift($buff2);
                    $buff2 = implode($buff2);
                    $body = $buff[0] . $buff2;
                } else {
                    $buff = str_split($body, $compontent->offset);
                    $buff[0] .= $compontent->View;
                    $body = implode($buff);
                }
            }
        }
        return $body;
    }

    /*
     * ����Htmlͷ������
     */
    public function ReplaceArgs(&$obj)
    {
        if ($obj->Attribute) {
            foreach ($obj->Attribute as $key => &$value) {
                $value = str_replace("SERVER_COMMOM/", SERVER_COMMOM, $value);
                $value = str_replace("SERVER_HOME/", SERVER_HOME, $value);
                $value = str_replace("LACOL/", SERVER_HOME . App::$Appname . "/Component/$this->component/", $value);
                $value = str_replace("COMPONENTS/",SERVER_COMPONENT . "$this->component/", $value);
            }
        }
    }

    public function AnalyHead()
    {
        if (func_num_args()) {//û�д���ʱ����content��ͷ������ʱ���������ַ���
            $target = func_get_arg(0);
            if (preg_match_all("/<\s*(\S*)(\s*\S*\s*=\s*(['\"])[\s\S]*?\\3)*\s*(\/>|>([\S\s]*?)<\/\\1>)/i", $target, $match)) {
                foreach ($match[0] as $key => $value) {
                    $obj = new Label($match[1][$key], $value);
                    $this->ReplaceArgs($obj);
                    $this->Head[] = $obj;
                }
            }
        } else if (preg_match("/<\s*head[\s\S]*?>([\s\S]*?)<\/\s*head\s*>/i", $this->Content, $buff)) {
            if (preg_match_all("/<\s*(\S*)(\s*\S*\s*=\s*(['\"])[\s\S]*?\\3)*\s*(\/>|>([\S\s]*?)<\/\\1>)/i", $buff[1], $match)) {
                foreach ($match[0] as $key => $value) {
                    $obj = new Label($match[1][$key], $value);
                    $this->ReplaceArgs($obj);
                    $this->Head[] = $obj;
                }
            }
        }

    }

    public function __construct($text)
    {
        $this->Content = $text;

        if (preg_match("/<\s*html[\s\S]*?>[\s\S]*?<\/\s*html\s*>/i", $this->Content))//�ж�ҳ���Ƿ���������HTML�ṹ
        {
            $this->IsCompletion = true;

            if (preg_match("/<\s*body[\s\S]*?>([\s\S]*)<\/\s*body\s*?>/i", $text, $buff)) {
                $this->Body = $buff[1];
            } else
                Errors::Exception("Structure Of Page Is Incomplete");
        } else {
            $this->Body = $this->Content;//��html�����ṹ��Content���ݼ�Body����
        }
        $this->LoadComponent();
    }

    /*
     * ���ڹ������
     */
    public function Start()
    {
    }//ҳ����غ���

    public function __get($value)
    {
        if (isset($this->Component[$value]))
            return $this->Component[$value];
        else if (isset($this->Attrute[$value]))
            return $this->Attrute[$value];
        return false;
    }
}