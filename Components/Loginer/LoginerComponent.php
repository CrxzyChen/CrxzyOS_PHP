<?php
/**
 * Created by PhpStorm.
 * User: 旭阳
 * Date: 2016/2/15
 * Time: 21:46
 */

namespace Loginer;


class LoginerComponent extends \Block
{
    public $action = "";
    public $table = "";
    public $fieldUsername = "username";
    public $fieldPassword = "password";
    public $fieldRight = "";

    public function Start()
    {
        $this->db->view($this->table);

    }
    private function verification($username)
    {
        if($this->fieldRight!="")
        {
            $result = $this->db->field("`$this->fieldRight`")->where("`$username`='".\App::$Args["POST"]["username"]."'")->where("`$this->fieldPassword`='".md5(\App::$Args["POST"]["password"])."'")->select();
            if($result->num>0)
            {
                return $result->row[0];
            }
            else{
                return "false";
            }
        }
        else{
            $result = $this->db->field("count(*)")->where("`$username`='".\App::$Args["POST"]["username"]."'")->where("`$this->fieldPassword`='".md5(\App::$Args["POST"]["password"])."'")->select();
            if($result->row[0]>0)
            {
                return "success";
            }
            else{
                return "false";
            }
        }
    }
    public function Form()
    {
        if(is_array($this->fieldUsername))
        {
            foreach($this->fieldUsername as $value)
            {
                if(($buf=$this->verification($value))!="false")
                {
                    echo $buf;
                    return;
                }

            }
            echo "false";
        }else{
            echo $this->verification($this->fieldUsername);
        }
    }
}