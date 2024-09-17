<?
$_SERVER['DOCUMENT_ROOT'] = __DIR__."/..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
$path =__DIR__."/../bitrix/modules/main/include/prolog_before.php";
echo $path.PHP_EOL;

//require($path);
$arr = ["qwe", 123];
printfile(__DIR__, $arr);
//d($arr);