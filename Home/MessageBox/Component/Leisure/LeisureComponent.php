<?php
/**
 * Created by PhpStorm.
 * User: 旭阳
 * Date: 2016/3/3
 * Time: 0:08
 */

namespace MessageBox;


class LeisureComponent extends \Block
{
    public $Leisure = array();
    public $Table = "";
    public $FirstWeek = "2016-02-29";
    public $Week = "";
    public function Start()
    {
        for($i = 0;$i<13;$i++)
        {
            for($j = 0;$j<7;$j++) {
                $this->Leisure[$i][$j] = "";
                $result = $this->db->view("select `student_name` from `student` where `student_num` in (select `student_num` from `student_course` where `course_num` in (select `course_num` from `course` where `week`='" . ($j + 1) . "' and `time`='" . ($i + 1) . "'))");
                if($result)
                {
                    foreach($result as $value)
                    {
                        $this->Leisure[$i][$j].=$value["student_name"]."<br>";
                    }
                }
            }
        }

        $this->CreateTable();
        $this->Week = $this->CurrentWeek();
    }
    public function CreateTable()
    {
        $this->Table = "<table border='1'>";
        $this->Table.="<tr>";
        $this->Table.="    <th>";
        $this->Table.="       星期一";
        $this->Table.="    </th>";
        $this->Table.="    <th>";
        $this->Table.="       星期二";
        $this->Table.="    </th>";
        $this->Table.="    <th>";
        $this->Table.="       星期三";
        $this->Table.="    </th>";
        $this->Table.="    <th>";
        $this->Table.="       星期四";
        $this->Table.="    </th>";
        $this->Table.="    <th>";
        $this->Table.="       星期五";
        $this->Table.="    </th>";
        $this->Table.="    <th>";
        $this->Table.="       星期六";
        $this->Table.="    </th>";
        $this->Table.="    <th>";
        $this->Table.="       星期天";
        $this->Table.="    </th>";
        $this->Table.="</tr>";
        for($i = 0;$i<13;$i++)
        {
            $this->Table.="<tr>";
            for($j = 0;$j<7;$j++)
            {
                $this->Table.="<td>";
                $this->Table.="    {$this->Leisure[$i][$j]}";
                $this->Table.="</td>";
            }
            $this->Table.="</tr>";
        }
        $this->Table.="</table>";
    }
    private function HowDay($year,$mouth)
    {
        if($mouth<8)
        {
            if($mouth%2==1)
                return 31;
            else if($mouth==2)
            {
                if($year%400!=0&&$year%4==0)
                    return 29;
                else
                    return 28;
            }
            else
                return 30;
        }
        else
        {
            if($mouth%2==1)
                return 30;
            else
                return 31;
        }
    }
    public function CurrentWeek()
    {
        $first = explode("-", $this->FirstWeek);
        //$current = explode("-","2016-4-4");
        $current = explode("-",date("Y-m-d"));
        if($current[2]-$first[2]<0)
        {
            $day = $current[2]+$this->HowDay($current[0],--$current[1])-$first[2];
        }
        for($i = $first[1];$i<$current[1];$i++)
        {
            $day+=$this->HowDay("2016",$i);
        }
        return intval($day/7+1);
    }
}