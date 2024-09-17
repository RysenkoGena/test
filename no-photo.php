<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$sectFilter = Array("IBLOCK_ID"=>4, "DETAIL_PICTURE" => false, "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array("PROPERTY_proizvoditel" => "ASC"), $sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PROPERTY_proizvoditel"));
echo "<h3>Отчет по товарам без фото</h3>";
echo "На сайте активных товаров без фото: ".$list->SelectedRowsCount()."<br>".PHP_EOL;
echo '<table>
*если указано что <b>"есть файл в 1С"</b>, то из 1С они автоматически сейчас перекочуют на сайт))<br><br>
';

While($obEl = $list->GetNext()){
    $obEl["XML_ID"] = trim($obEl["XML_ID"]);
    if(strlen($obEl["XML_ID"] >= 4)){
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
    }
}
echo "</table>";