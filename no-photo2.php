<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$sectFilter = Array("IBLOCK_ID"=>4, "!DETAIL_PICTURE" => false, "ACTIVE"=>"Y");
$list = CIBlockElement::GetList(Array("PROPERTY_proizvoditel" => "ASC"), $sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PROPERTY_proizvoditel", "DETAIL_PICTURE"));
echo "<h3>Отчет по товарам с фото только на сайте (нет в 1С картинки)</h3>";
echo "На сайте активных товаров с фото: ".$list->SelectedRowsCount()."<br>".PHP_EOL;
echo '<table>';
$i = 0;
While($obEl = $list->GetNext()){
    $obEl["XML_ID"] = trim($obEl["XML_ID"]);
    if(strlen($obEl["XML_ID"]) >= 4){
        $dir = substr($obEl["XML_ID"], 0, -3) . "000/";
    }
    else $dir = "0000/";
    $filename = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/images/'.$dir.$obEl["XML_ID"].".jpg";
    $fileNameMask = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/images/'.$dir.$obEl["XML_ID"]."_*";


    if(!file_exists($filename)){
        echo $filename;
        $from = CFile::GetPath($obEl["DETAIL_PICTURE"]);
        $i++;
        echo "<tr><td>".$obEl["PROPERTY_PROIZVODITEL_VALUE"]."<td>".$obEl["XML_ID"].PHP_EOL;
        echo "<td>нет файла в 1С!";
        copy($_SERVER['DOCUMENT_ROOT'].$from, $obEl["XML_ID"].".jpg");
        /*foreach(glob($fileNameMask) as $file) {
            echo "есть дополнительный файл! отправляю его в newImages";
            copy($file, $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iarga.exchange/upload/newImages/'.basename($file));
        }*/
    }
    //if($i > 2) break;
}
echo "</table>
всего таких товаров: ".$i;