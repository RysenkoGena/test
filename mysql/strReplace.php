<?
#
# Заменяет все вхождения $search на $replace во всех файлах директории $dir
#
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

$search = "www.yugkabel";
$replace = "yugkabel";

$dir = $_SERVER["DOCUMENT_ROOT"]."/reg";

findFiles($dir);


function findFiles($dir){
    global $search, $replace;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        if(is_dir($dir . '/' . $file))
            findFiles(($dir . '/' . $file));
        else {
            $filePath = $dir . '/' . $file;
            echo $filePath . "<br><br>" . PHP_EOL;
            $text = file_get_contents($filePath);
            if(strpos($text, $search) !== false) {
                echo "<b>Исправить файл</b> " . $filePath . "<br>";
                $text = str_replace($search, $replace, $text);
                if(file_put_contents($filePath, $text))
                    echo "Записано!<br>";
            }
            //echo "<b>Исправить файл</b> ".$filePath."<br>";
        }
    }
}