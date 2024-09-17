<?php

$_SERVER["DOCUMENT_ROOT"] = __DIR__."/../../../../../";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

echo "<h1>Отчет о пустых результатах поиска на сайте</h1>";

$files = glob(__DIR__."/*.csv");
//debug ($files);
$select = [];

foreach ($files as $item){
    $dateFile = strtotime($item);
    $select[strtotime(substr(basename($item), 10, 8))] = $item;
    $data[strtotime(substr(basename($item), 10, 8))] = file($item);
}
ksort($data);

foreach($data as $key => $item){
    echo $key."<br>";
}

foreach ($data as $key=>$dat) {

    echo date("M Y", $key) . ": ";

    $array = $dat;
    $stat = [];
    $results = [];
    $i = 0;
    $emptyResult = 0;
    foreach ($array as $string) {
        $words = explode("	", $string);
        if (!strpos($words[3], "192.168.") === 0 || $words[0] == "") continue;

        if ((int)$words[1] == 0) {
            $emptyResult++;
        }

        $word = mb_convert_encoding($words[0], "utf-8", "windows-1251");
        $word = mb_strtolower($word);
        if ($word_previous == $word) continue;
        $word_previous = $word;

        $i++;
    }
    echo round($emptyResult/$i*100, 1)."%<br>";
}

/*
arsort($stat);
echo "Количество запросов за месяц: ".count($query)."<br>";
echo "всего разных слов: ".count($stat)."<br><br>";
if(!isset($_GET["noresult"]))
    echo "Процент пустых результатов поиска: ".round($emptyResult/$i*100, 1)."%<br>";
//debug($stat);
$i = 0;
echo "Список первых 5000 поисковых запросов:<table>
<tr><th>Кол-во запросов в месяц</th><th>Запрос</th><th>найдено товаров<th>Возможное название товара ";
//debug($sinonims);

foreach ($stat as $key => $item){
    $i++; $productName = ""; $b = ""; $b_ = "";
    if($i > 5000) break;
    if(strlen($key) <= 6 && $key < 200000 && substr($key,0,1) != 0 && substr($key,0,2) != "00") {
        $arrFilter = ["IBLOCK_ID"=>4, "XML_ID"=>$key];
        $list = CIBlockElement::GetList(Array(),$arrFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PROPERTY_PROIZVODITEL"));
        $productName = "";
        While($obEl = $list->GetNext()){
            $productName = $obEl["NAME"];
            if($obEl["PROPERTY_PROIZVODITEL_VALUE"]) $productName .= ", ".$obEl["PROPERTY_PROIZVODITEL_VALUE"];
        }
        if($productName) {
            $b = "<b>";
            $b_ = "</b>";
        }
    }
    else {
        $b = "";
        $b_ = "";
    }
    $isOKresults = "";
    if(array_key_exists($key, $sinonims)) {
        $isOK = " <span style='color:green;'> &#9989;" . $sinonims[$key]["r"] . "</span>";
        $isOKresults = " <span style='color:green;'> &#9989;".$hls[$sinonims[$key]["r"]]."</span>";
        $isOKresults = " <span style='color:green;'> &#9989;".$hls[$sinonims[$key]["r"]]."</span>";
    }
    else {
        $isOK = "";
        $isOKresults = "";
    }

    if($isOKresults == ""){
        if(array_key_exists($key, $hls))
            $isOKresults = " <span style='color:green;'> &#9989;".$hls["$key"]."</span>";
        else $isOKresults = "";
    }


    $arrFilter = ["IBLOCK_ID"=>4, "PROPERTY_artikul"=>$key];
    $list = CIBlockElement::GetList(Array(),$arrFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PROPERTY_PROIZVODITEL"));
    //$productName = "";
    While($obEl = $list->GetNext()){
        $productName = $obEl["NAME"];
        if($obEl["PROPERTY_PROIZVODITEL_VALUE"]) $productName .= ", ".$obEl["PROPERTY_PROIZVODITEL_VALUE"];
    }
    echo "<tr><td>".$item."<td>".$b. $key .$b_.$isOK."<td>".$results[$key].$isOKresults."<td>".$productName;

}
echo "</table>";
*/