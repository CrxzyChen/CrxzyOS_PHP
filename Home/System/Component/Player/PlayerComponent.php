<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/12
 * Time: 16:44
 */

namespace System
{
    class PlayerComponent extends \Block
    {
        public $VideoPosition = "http://219.225.7.198:8081/Videos/%E5%AF%92%E8%9D%89%E9%B8%A3%E6%B3%A3%E4%B9%8B%E6%97%B6/%E3%81%B2%E3%81%90%E3%82%89%E3%81%97%E3%81%AE%E3%81%AA%E3%81%8F%E9%A1%B7%E3%81%AB%2001.mp4";
        public $Caption = "http://219.225.7.198:8081/Videos/%E5%AF%92%E8%9D%89%E9%B8%A3%E6%B3%A3%E4%B9%8B%E6%97%B6/%E3%81%B2%E3%81%90%E3%82%89%E3%81%97%E3%81%AE%E3%81%AA%E3%81%8F%E9%A1%B7%E3%81%AB%2001.vtt";
        public $Width = 480;
        public $Height = 640;

        public function get()
        {
        }
    }
}

