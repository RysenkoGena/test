<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$sectFilter = Array("IBLOCK_ID"=>4, "XML_ID" => 20265);
$list = CIBlockElement::GetList(Array("PROPERTY_proizvoditel" => "ASC"), $sectFilter, false, false, Array("ID", "DETAIL_PICTURE", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PROPERTY_proizvoditel"));
echo "<h3>Отчет по товарам без фото</h3>";

echo '<table>
*если указано что <b>"есть файл в 1С"</b>, то из 1С они автоматически сейчас перекочуют на сайт))<br><br>
';

While($obEl = $list->GetNext()){
    $obEl["XML_ID"] = trim($obEl["XML_ID"]);

    echo $obEl["DETAIL_PICTURE"]."<br>";
    echo basename(CFile::GetPath($obEl["DETAIL_PICTURE"]));
    /*if(strlen($obEl["XML_ID"] >= 4)){
        $dir = substr($obEl["XML_ID"], 0, -3) . "000/";
    }
    else $dir = "0000";
    $filename = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/images/'.$dir.$obEl["XML_ID"].".jpg";
    $fileNameMask = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/images/'.$dir.$obEl["XML_ID"]."_*";

    echo "<tr><td>".$obEl["PROPERTY_PROIZVODITEL_VALUE"]."<td>".$obEl["XML_ID"].PHP_EOL;
    if(file_exists($filename)){
        echo "<td>есть файл в 1С*! Отправляю его в new Images";
        copy($filename, $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/newImages/'.$obEl["XML_ID"].".jpg");
        foreach(glob($fileNameMask) as $file) {
            echo "есть дополнительный файл! отправляю его в newImages";
            copy($file, $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/newImages/'.basename($file));
        }
    }*/
}
echo "</table> Поиск файлов с размером 6941";
$i=0;
$dir1 = __DIR__."/../upload/iblock";
$dirs = array_diff(scandir($dir1), ['.','..']);
foreach ($dirs as $dir) {
    if(is_dir($dir1.'/'.$dir)){
        //echo $dir1.'/'.$dir."<br>";
        $files = array_diff(scandir($dir1.'/'.$dir), ['.','..']);
        foreach ($files as $file){
            if(filesize($dir1.'/'.$dir.'/'.$file)==6941) {
                $hash = hash_file('md5', $dir1.'/'.$dir.'/'.$file);
                $nophotos[] = basename($dir1.'/'.$dir.'/'.$file);
                //echo "есть файл с таким размером. Хэш: ".$hash."<br>";
                $i++;
            }
        }
    }
}
echo "<br>Всего найдено ".$i." таких файла<br>";
debug($nophotos);

$sectFilter = Array("IBLOCK_ID"=>4);
$list = CIBlockElement::GetList([], $sectFilter, false, false, Array("ID", "DETAIL_PICTURE", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PROPERTY_proizvoditel"));
$j=0;
While($obEl = $list->GetNext()) {
    $obEl["XML_ID"] = trim($obEl["XML_ID"]);


    //echo $obEl["DETAIL_PICTURE"] . "<br>";
    $imgName =  basename(CFile::GetPath($obEl["DETAIL_PICTURE"]));
    if(in_array($imgName, $nophotos)) {
        echo $obEl["XML_ID"] . "<br>";
        $j++;


        if (strlen($obEl["XML_ID"] >= 4)) {
            $dir = substr($obEl["XML_ID"], 0, -3) . "000/";
        } else $dir = "0000";
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/iarga.exchange/upload/images/' . $dir . $obEl["XML_ID"] . ".jpg";
        $fileNameMask = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/iarga.exchange/upload/images/' . $dir . $obEl["XML_ID"] . "_*";

        if (file_exists($filename)) {
            echo "<br>есть файл в 1С*! Отправляю его в new Images " . $obEl["XML_ID"];
            //copy($filename, $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/newImages/'.$obEl["XML_ID"].".jpg");
            foreach (glob($fileNameMask) as $file) {
                echo "есть дополнительный файл! отправляю его в newImages";
                //copy($file, $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/newImages/'.basename($file));
            }
        }
    }
}
echo "<br>Всего найдено ".$j." таких товаров<br>";



