<?php
/**
 * Created by PhpStorm.
 * User: ����
 * Date: 2016/2/12
 * Time: 20:07
 */

namespace Japanese;

class DialogueComponent extends \Block
{
    public function Start()
    {
//        $result = array(
//            "japanese"=>"日本人",
//            "ailas"=>"にほんじん",
//            "chinese"=>"日本人",
//            "nominal"=>"名词",
//            "familiarity"=>"1"
//        );
//        $this->db->view("japanese_word")->insert($result);
    }
    public function GetWord()
    {
        $result = $this->db->view("select * from (select * from `japanese_word` where `familiarity`<7 && TIMESTAMPDIFF(minute, now(), `nexttime`)<0) as a order by rand() limit 1");
        echo json_encode($result[0]);
    }
    public function Answer()
    {
        if($_POST["answer"]=="yes")
        {
            if($_POST["temp_status"])
                $this->db->view("japanese_word")->where("`id`='{$_POST["wordId"]}'")->update(array("familiarity"=>$_POST["familiarity"]+1,"nexttime"=>date("Y-m-d H:i:s",strtotime("+{$_POST["familiarity"]} day"))));
            else {
                $this->db->view("japanese_word")->where("`id`='{$_POST["wordId"]}'")->update(array("temp_status"=>1,"nexttime" => date("Y-m-d H:i:s", strtotime("+{$_POST["familiarity"]} day"))));
            }
        }
        else if($_POST["answer"]="no")
        {
            $this->db->view("japanese_word")->where("`id`='{$_POST["wordId"]}'")->update(array("temp_status"=>0));
        }
    }
}