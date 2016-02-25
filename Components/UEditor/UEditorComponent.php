<?php
/**
 * Created by PhpStorm.
 * User: ����
 * Date: 2016/2/22
 * Time: 13:42
 */

namespace UEditor;


class UEditorComponent extends \Block
{
    public $address = "";

    private function setAddress(&$CONFIG)
    {
        if ($this->address != "") {
            $CONFIG["scrawlPathFormat"] = "$this->address/image/{yyyy}{mm}{dd}/{time}{rand:6}";
            $CONFIG["imagePathFormat"] = "$this->address/image/{yyyy}{mm}{dd}/{time}{rand:6}";
            $CONFIG["snapscreenPathFormat"] = "$this->address/image/{yyyy}{mm}{dd}/{time}{rand:6}";
            $CONFIG["catcherPathFormat"] = "$this->address/image/{yyyy}{mm}{dd}/{time}{rand:6}";
            $CONFIG["videoPathFormat"] = "$this->address/video/{yyyy}{mm}{dd}/{time}{rand:6}";
            $CONFIG["filePathFormat"] = "$this->address/file/{yyyy}{mm}{dd}/{time}{rand:6}";
            $CONFIG["imageManagerListPath"] = "$this->address/image/";
            $CONFIG["fileManagerListPath"] = "$this->address/file/";
        }
    }

    public function ensureStatus()
    {

        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");

        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($this->ComponentPath . "php/config.json")), true);
        $action = $_GET['action'];
        $this->setAddress($CONFIG);
        $CONFIG[""];
        switch ($action) {
            case 'config':
                $result = json_encode($CONFIG);
                break;
            /* �ϴ�ͼƬ */
            case 'uploadimage':
                /* �ϴ�Ϳѻ */
            case 'uploadscrawl':
                /* �ϴ���Ƶ */
            case 'uploadvideo':
                /* �ϴ��ļ� */
            case 'uploadfile':
                $result = include($this->ComponentPath . "php/action_upload.php");
                break;

            /* �г�ͼƬ */
            case 'listimage':
                $result = include($this->ComponentPath . "php/action_list.php");
                break;
            /* �г��ļ� */
            case 'listfile':
                $result = include($this->ComponentPath . "php/action_list.php");
                break;

            /* ץȡԶ���ļ� */
            case 'catchimage':
                $result = include($this->ComponentPath . "action_crawler.php");
                break;

            default:
                $result = json_encode(array(
                    'state' => '�����ַ����'
                ));
                break;
        }

        /* ������ */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state' => 'callback�������Ϸ�'
                ));
            }
        } else {
            echo $result;
        }
    }
}