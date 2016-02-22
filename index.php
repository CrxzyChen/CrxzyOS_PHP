<?php

if (is_file("config.php"))//加载配置文件
    require("config.php");
else {
    die("Can't Find Config.php");
}


if (is_file(DIR_SYSTEM . "System.php")) {
    require(DIR_SYSTEM . "System.php");
} else {
    die("Can't Find System.php");
}

$system = new System();
$system->startup();
