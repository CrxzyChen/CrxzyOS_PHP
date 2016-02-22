<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/11
 * Time: 18:06
 */
class Errors
{
    static private function PrintfDebugBacktrace()
    {
    }
    static public function  ErrorHanding($errno, $errstr, $errfile,$errline)
    {
        echo 1;
    }
    /*
     * 打印调试堆栈
     */
    static public function NoticeHanding($errno, $errstr, $errfile, $errline)
    {
        ob_clean();
        $E = "[$errno] $errstr   [line]$errfile $errline";
        echo "<h1>[$errno] $errstr</h1><br>";
        echo "<h2>[Line]$errfile $errline</h2>";
        //self::PrintfDebugBacktrace();
        if(IS_LOGS)
            log::RecordError($E);
    }
    static public function WarningHanding($errno, $errstr, $errfile, $errline)
    {
        echo 3;
    }
    static public function FatalErrorHanding($errno, $errstr, $errfile, $errline)
    {
        echo 4;
    }

    static public function Exception($error)
    {
        trigger_error($error);
    }
}