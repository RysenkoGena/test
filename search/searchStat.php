<?php
$_SERVER["DOCUMENT_ROOT"] = __DIR__."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

?><head><title>Запросы посетителей</title></head><?php


Bitrix\Main\Loader::includeModule("highloadblock");
function GetEntityDataClass($HlBlockId) {
    if (empty($HlBlockId) || $HlBlockId < 1)    return false;
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($HlBlockId)->fetch();
    return Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
}

$entity_data_class = GetEntityDataClass(6);
$rsData = $entity_data_class::getList(['order' => [], 'select' => array("*"), 'filter' => ['>UF_WORDSTAT_RESULTS' => 0]]);
while($el = $rsData->fetch()){
    $hls[$el["UF_WORDSTAT_QUERY"]] = $el["UF_WORDSTAT_RESULTS"];
}
//debug ($hls);

include $_SERVER["DOCUMENT_ROOT"]."/test/SEO/sinonims.php"; # тут просто массив с сопоставлениями - массив $sinonims

if(isset($_GET["month"])) $file =$_GET["month"];
else $file = "WordStat.".date("M.Y").".csv";

echo "<h1>Отчет поисковых запросов на сайте за месяц ". substr($file, 10, 8)."</h1>";

$files = glob(__DIR__."/*.csv");
//debug ($files);

$select = [];

foreach ($files as $item){
    $dateFile = strtotime($item);
    $select[strtotime(substr(basename($item), 10, 8))] = $item;
    if(isset($_GET["month"]) && $_GET["month"] == basename($item)) $selected = " selected";
    else $selected = "";
    //echo "<option value = '".basename($item)."'".$selected.">".substr(basename($item), 10, 6)."</option>";
}
echo "<form> выберите месяц: <select name='month' onChange='this.form.submit()'>";

krsort($select);

foreach($select as $item){
    if(isset($_GET["month"]) && $_GET["month"] == basename($item)) $selected = " selected";
    else $selected = "";
    echo "<option value = '".basename($item)."'".$selected.">".substr(basename($item), 10, 8)."</option>";
}




echo "</select><br>";
if(isset($_GET["noresult"]) && $_GET["noresult"] == "on") $checked = " checked";
else $checked = "";
echo "Отобразить только с нулевыми результатами 
<input type='checkbox' name='noresult' onChange='this.form.submit()'".$checked.">
</form>";
echo __DIR__."/".$file."<br>";
if(!file_exists($file))
    echo "не существует<br><br>";
else echo "есть файл";

$array = file(__DIR__."/".$file);
$stat = []; $results = []; $query = [];
$i = 0; $emptyResult = 0;

foreach ($array as $string){
    $words = explode("	", $string);
    if(!strpos($words[3], "192.168.") === 0 || $words[0] == "") continue;

    if(isset($_GET["noresult"]) && $_GET["noresult"] == "on" && (int)$words[1] != 0){
        //$emptyResult++;
        continue;
    }
    if((int)$words[1] == 0){
        $emptyResult++;
    }
    //echo $words[3]."<br>";
    //echo $words[0]."<br>";
    $word = mb_convert_encoding($words[0], "utf-8", "windows-1251");
    $word = mb_strtolower($word);
    if($word_previous == $word) continue;
    $word_previous = $word;
    $query[] = $word;
    if(!array_key_exists($word, $stat)){
        $stat[$word] = 1;
        $results[$word] = $words[1];
    }
    else $stat[$word]++;

    $i++;
}
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

    if(isset($_GET["noresult"]) && $_GET["noresult"] == "on" && $isOKresults)
        continue;

    $arrFilter = ["IBLOCK_ID"=>4, "PROPERTY_artikul"=>$key];
    $list = CIBlockElement::GetList(Array(),$arrFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PROPERTY_PROIZVODITEL"));
    //$productName = "";
    While($obEl = $list->GetNext()){
        $productName = $obEl["NAME"];
        if($obEl["PROPERTY_PROIZVODITEL_VALUE"]) $productName .= ", ".$obEl["PROPERTY_PROIZVODITEL_VALUE"];
    }
    echo "<tr><td>".$item."<td>".$b. "<a href='/products/?qa=".$key."' target=_blank>" .$key. "</a>" .$b_.$isOK."<td>".$results[$key].$isOKresults."<td>".$productName;

}
echo "</table>";




