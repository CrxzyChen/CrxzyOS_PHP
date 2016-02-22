<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/11
 * Time: 15:52
 */
class Log
{
    /*
     * 记录访问者信息
     */
    static private function Lock($filename)
    {
        $logfile = fopen($filename,"a+");//打开当天的日志文件
        $startTime=microtime();//记录打开的时间
        do{
            $canWrite=flock($logfile,LOCK_EX);
            if(!$canWrite){
                usleep(round(rand(0,100)*1000));
            }
        }while((!$canWrite)&&((microtime()-$startTime)<1000));//等待日志文件可以使用，时间如果超过1m放弃写入文件
        if($canWrite){
            return $logfile;
        }
        else
            return false;
    }
    static private function UnLock($logfile)
    {
        flock($logfile,LOCK_UN);
    }
    static public function RecordUserMsg()
    {
       if($file = self::Lock(DIR_LOGS.date("Y_m_d").".log")){
           fwrite($file,"DATETIME:".date("Y-m-d H:i:s")." IP:".USER_REMOTE_ADDR." HOST:".USER_REMOTE_HOST." PORT:".USER_REMOTE_PORT." URL:".SERVER_NAME.((SERVER_PORT==80)?:(":".SERVER_PORT)).REQUEST_REMOTE_URL."\n");
           self::UnLock($file);
       }
    }
    static public function RecordError($err)
    {
        if($file = self::Lock(DIR_LOGS.date("Y_m_d").".err")){
            fwrite($file,$err."\n");
            self::UnLock($file);
        }
    }
}