<?PHP 
$start = microtime(true);
/*$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
ob_end_flush(); //отключить буферизацию*/



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
echo 'Скрипт был выполнен за ' . (microtime(true) - $start) . ' секунд';
$APPLICATION->SetTitle("Отбор товаров с Российской");

getItemsBySectionId(1231); // 01. кабель
getItemsBySectionId(1342); // 02. лампы
getItemsBySectionId(1435); // 03. светильники
getItemsBySectionId(1579); // 04. электрооборудование
getItemsBySectionId(1716); // 05. эл. изделия
getItemsBySectionId(1849); // 06. розетки
getItemsBySectionId(2102); // 10. модулька
getItemsBySectionId(2183); // 11. счетчики
getItemsBySectionId(2192); // 12. щиты


function getItemsBySectionId($sectionId){
  if($sectionId == 1231){ //кабель, разбиваем по 50 м
      $storeAmount = 50;
      //$price5 = 50;
  }else{
    $storeAmount = 0;
    //$price5 = 0;
  }
  $arFilter = Array(
      "LOGIC" => "AND",
      //"<PROPERTY_CENAZ" => $price5,
      "IBLOCK_ID" => 4,
      "ACTIVE"=> "Y",
      "PROPERTY_EXISTS_AT_STORES" => 78621,  //78621 склад российская
      "SECTION_ID"=>$sectionId,
      "INCLUDE_SUBSECTIONS" => "Y",
      ">CATALOG_STORE_AMOUNT_10" => $storeAmount, //10 склад Российская
     
    );

  echo "<br><br>";
  $res =  CIBlockElement::GetList(Array(), $arFilter, false, false, Array("ID", "IBLOCK_ID","XML_ID","NAME","PROPERTY_CENAZ","PROPERTY_ARTIKUL"));
  echo "Количество найденых элементов: ".$res->result->num_rows. "<br>";
  //$num = $res::SelectedRowsCount();
  //echo $num." строк найдено<br>";
  global $ar;
  while($ar = $res->GetNext()) {
    if($sectionId == 1231){
      if($ar["PROPERTY_CENAZ_VALUE"] < (350/50)) continue;
        $amount = CCatalogStoreProduct::GetList(
          array(),
          array("PRODUCT_ID" => $ar["ID"], "STORE_ID" => 10),
          false,
          false,
          array("AMOUNT")
        )->Fetch();
        $amount = " Количество бухт: ".floor($amount["AMOUNT"] / 50);
        $text = " за 50 метров. ".$amount;
        $price = round($ar["PROPERTY_CENAZ_VALUE"] * 50 * 1.6, 2, PHP_ROUND_HALF_UP);
    }
    elseif($sectionId == 1342 || $sectionId == 1849){ // если лампы и розетки
      if($ar["PROPERTY_CENAZ_VALUE"] < 35) continue;
      elseif($ar["PROPERTY_CENAZ_VALUE"] >= 350) {
        $text = setKit(1);
        if(!$text) continue;
      }
      elseif($ar["PROPERTY_CENAZ_VALUE"] >= 70 && $ar["PROPERTY_CENAZ_VALUE"] < 350){
        $text = setKit(5);
        if(!$text) continue;
      }
      elseif($ar["PROPERTY_CENAZ_VALUE"] >= 35 && $ar["PROPERTY_CENAZ_VALUE"] < 70){
        $text = setKit(10);
        if(!$text) continue;
      }
    }
    elseif($sectionId == 1435 || $sectionId == 1579 || $sectionId == 1716 || $sectionId == 2183 || $sectionId == 2192){
      if($ar["PROPERTY_CENAZ_VALUE"] < 350) continue;
      else{
        $text = setKit(1);
        if(!$text) continue;
      }
    }
    elseif($sectionId == 2102){ // если модульное оборудование
      if($ar["PROPERTY_CENAZ_VALUE"] < 70) continue;
      elseif($ar["PROPERTY_CENAZ_VALUE"] >= 350) {
        $text = setKit(1);
        if(!$text) continue;
      }

      elseif($ar["PROPERTY_CENAZ_VALUE"] >= 115 && $ar["PROPERTY_CENAZ_VALUE"] < 350){
        $text = setKit(3);
        if(!$text) continue;
      }
      
      elseif($ar["PROPERTY_CENAZ_VALUE"] >= 70 && $ar["PROPERTY_CENAZ_VALUE"] < 115){
        $text = setKit(5);
        if(!$text) continue;
      }      
    }
    else $text = "";
    $ar["NAME"] = str_replace(" ФИНАЛЬНАЯ РАСПРОДАЖА", "", $ar["NAME"]);
    if($ar["PROPERTY_ARTIKUL_VALUE"]) $productName = str_replace($ar["PROPERTY_ARTIKUL_VALUE"], "", $ar["NAME"]);
    else $productName = $ar["NAME"];
    echo "<a href=/p/".$ar["XML_ID"].">".$productName."</a> ".$text."<br>";
    //echo "<a href=/p/".$ar["XML_ID"].">".$ar["NAME"]."</a> ".($ar["PROPERTY_CENAZ_VALUE"])." руб.".$text."<br>";
    //if($sectionId == 1231) debug($ar);
    if($sectionId != 2102) break;
  }
}

echo '<br>Скрипт был выполнен за ' . (microtime(true) - $start) . ' секунд';
function setKit($kit){
  global $ar;
  //$kit = 5;
  //echo $ar["ID"];
  $amount = CCatalogStoreProduct::GetList(
    array(),
    array("PRODUCT_ID" => $ar["ID"], "STORE_ID" => 10),
    false,
    false,
    array("AMOUNT")
  )->Fetch();
 
  if($amount["AMOUNT"] < $kit) return false;
  if($kit > 1) $amount = " Количество доступных наборов: ".floor($amount["AMOUNT"] / $kit);
  if($kit == 1) $amount = " Доступное количество: ".floor($amount["AMOUNT"] / $kit);
  //echo $amount." ";
  if($kit > 1)  $text = " за набор из ".$kit." штук. ".$amount;
  if($kit == 1)  $text = " за штуку. ".$amount;
  $price = round($ar["PROPERTY_CENAZ_VALUE"] * $kit * 1.6, 2, PHP_ROUND_HALF_UP);
  $text = $price." руб.".$text;
  return $text;
}