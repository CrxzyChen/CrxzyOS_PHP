<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/17
 * Time: 16:27
 */
class Label
{
    public $Name = "";
    public $Attribute = array();
    public $Context = "";
    public $IsSingle = "";

    public function __construct($name, $label)
    {
        $this->Name = $name;
        preg_match_all("/<\s*(\S*)(\s*\S*\s*=\s*(['\"])[\s\S]*?\\3)*\s*(\/>|>([\S\s]*?)<\/\\1>)/i", $label, $buff);
        $this->Context = $buff[5][0];
        if ($buff[4][0] == "/>")
            $this->IsSingle = true;
        else
            $this->IsSingle = false;
        if (preg_match_all("/(\S*)\s*=\s*(['\"])([\s\S]*?)\\2/i", $label, $buff)) {
            foreach ($buff[1] as $k => $v) {
                /*
                 * 替换全局变量
                 */
                $this->AddAttribute($v, $buff[3][$k]);
            }
        }
    }

    public function Html()
    {
        $html = "<$this->Name";
        foreach ($this->Attribute as $key => $value) {
            $html .= " $key=\"$value\"";
        }
        if ($this->IsSingle)
            return $html .= "/>";
        return $html .= ">$this->Context</$this->Name>";
    }

    public function AddAttribute($name, $value)
    {
        $this->Attribute[$name] = $value;
    }
}