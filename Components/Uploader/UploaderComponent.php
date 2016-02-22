<?php
/**
 * Created by PhpStorm.
 * User: 旭阳
 * Date: 2016/2/19
 * Time: 20:45
 */

namespace Uploader;


class UploaderComponent extends \Block
{
    private $BufferPool = DIR_HOME . "UploadBuffer" . DIRECTORY_SEPARATOR;
    private $TargetPath = DIR_HOME . "Upload";
    private $filePath = '';
    private $fileName = '';
    private $chunk = '';
    private $chunks = '';
    private $fileType = '';
    public $handle = "";

    public function Start()
    {
    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }
        /*
         * $_SERVER['REQUEST_METHOD']用于检测服务器的提交方法
        * OPTIOMS同GET,POST一样，为提交方法，如果是OPTIOMS方法，则退出程序
        *
        */

        if (!empty($_REQUEST['debug'])) {
            $random = rand(0, intval($_REQUEST['debug']));
            if ($random === 0) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }
        /*
         * 当$_REQUEST['debug']==0时，
        * 服务器相应500；
        * 目的应该是用于前端代码错误检测
        */
        // 5 minutes execution time

        @set_time_limit(5 * 60);//设置服务器响应超时时间为5分钟
        // Uncomment this one to fake upload time
        // usleep(5000);
        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        // Create target dir

        if (!file_exists($this->BufferPool)) {
            @mkdir($this->BufferPool);//创建目标路径
        }

        // Create target dir

        // Get a file name

        /*
         * 检测文件完整性
        * 如果文件已经全部上传完毕
        * 则开始合并文件
        */
        if (isset($_REQUEST["name"])) {
            $this->fileName = $_REQUEST["name"];//根据请求设置文件名
        } else if (!empty($_FILES)) {
            $this->fileName = $_FILES["file"]["name"];//如果请求没有指出名字，则设置文件原名为文件名
        } else {
            $this->fileName = uniqid("file_");//否则由服务器生成一个唯一ID;
        }
        $this->fileName = urlencode($this->fileName);
        $this->filePath = $filePath = $this->BufferPool . DIRECTORY_SEPARATOR . $this->fileName;//DIRECTORY_SEPARATOR内存分离符，用于兼容linux 和  window间的"\"

        // Chunking might be enabled
        $this->chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;//请求是否包含数据块，如果有，存放到$this->chunk,，没有$this->chunk赋值为0，$this->chunk用于表示数据号;
        $this->chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;//请求是否包含数据块组，如果有，存放到$this->chunks,，没有$this->chunks赋值为1，$this->chunks表示数据数量;

        // Remove old temp files

        // Open temp file
        //echo "{$filePath}_{$this->chunk}.parttmp";
        if (!$out = @fopen("{$filePath}_{$this->chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {//检测文件时以哪种形式上传
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {//检测文件是否是有HTTP_POST上传,以及是否有传输错误
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }
            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {//检测文件是否能够打开
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {//二进制形式上传文件
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);//以4kb为单位大小写入文件
        }
        @fclose($out);
        @fclose($in);
        rename("{$filePath}_{$this->chunk}.parttmp", "{$filePath}_{$this->chunk}.part");//上传成功后将命名从临时改为正式命名
        $index = 0;
        $done = true;
        /*
         * 检测文件完整性
         */
        for ($index = 0; $index < $this->chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
                break;
            }
        }
        if ($done) {
            if ($this->handle != "")
                $handle = \App::Find($this->handle)->Upload($this);
            else if (method_exists(\App::Find("root"),"Upload"))
                $handle = \App::Find("root")->Upload($this);
            else {
                $this->Save($this->TargetPath);
            }
        }
    }

    public function Save($TargetPath, $fileName = "")
    {
        if (!file_exists($TargetPath)) {
            @mkdir($TargetPath);//创建上传缓冲路径
        }
        if (!is_dir($TargetPath) || !$dir = opendir($TargetPath)) {//如果目标路径不是合法路径，报错！
            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
        }
        $this->TargetPath = $TargetPath;
        $uploadPath = $TargetPath . DIRECTORY_SEPARATOR . ($fileName ? preg_replace('/^([^\.]*?)\./', $fileName . ".", $this->fileName) : $this->fileName);
        if (!$out = @fopen($uploadPath, "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        if (flock($out, LOCK_EX)) {//确保文件只有一个用户正在使用
            for ($index = 0; $index < $this->chunks; $index++) {
                if (!$in = @fopen("{$this->filePath}_{$index}.part", "rb")) {
                    break;
                }
                while ($buff = fread($in, 4096)) {
                    //fwrite($out,"{$filePath}_{$index}.part");
                    fwrite($out, $buff);
                }
                @fclose($in);
                @unlink("{$this->filePath}_{$index}.part");
            }
            flock($out, LOCK_UN);
        }
        @fclose($out);
    }
}