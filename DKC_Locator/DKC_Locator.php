<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
CModule::IncludeModule('iblock');
$exclude = ["06531", "F00003", "55032", "9192025", "57920", "03458", "04026"]; // исключения артикулов


$vendor = "ДКС";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
//echo "Найдено товаров: ".$list->SelectedRowsCount()."<br>";
$text = "Складские остатки на дату;Код продукции ДКС;Количество;Базовая единица измерения ;Сумма с НДС ;Город;Адрес офиса продаж дистр.;Внутр. код офиса продаж дистр.;Срок доставки\n";
$text = iconv("UTF-8", "Windows-1251", $text);
file_put_contents("020_2309070991_".date("dmY").".csv" , $text);
$i=0; $artikuls=array();
While($obEl = $list->GetNext()){
  $ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul", ))->GetNext();
  $measure = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($obEl["ID"])[$obEl["ID"]]['MEASURE']['SYMBOL_RUS'];
  $res_cena_akcii = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"cenaakcii1", ))->GetNext();
  if ($ob){
    if(in_array($ob['VALUE'], $exclude)) continue;
    $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
  }
  if($measure == "упак") $measure = "уп";
  storeP($obEl["ID"], $ob['VALUE'], $measure, $ob_cena_akcii['VALUE']);
}
echo '<a href=020_2309070991_'.date("dmY").'.csv>Скачать файл 020_2309070991_'.date("dmY").'.csv</a>';

$file 	     = '020_2309070991_'.date("dmY").'.csv';
$remote_file = '020_2309070991_'.date("dmY").'.csv';

function storeP($id, $artikul_dks, $edinica, $cena_akcii){
  $obStoreProduct = CCatalogStoreProduct::GetList(
     array("STORE_ID" => "ASC"),
     array("PRODUCT_ID" => $id),
     false,
     false,
     array("ID", "STORE_ID", "AMOUNT", "STORE_NAME", "STORE_DESCR")
  );
    $cena = iarga::getprice($id);
    if($cena_akcii != "" && $cena_akcii < $cena) $cena = $cena_akcii;
    $cena=str_replace(".", ",", $cena);

  while ($arStoreProduct = $obStoreProduct->Fetch()){
      if($arStoreProduct["STORE_DESCR"] == "в Анапе") $gorod="Анапа";
      else $gorod = "Краснодар";
      $adres="";
      if($arStoreProduct["STORE_DESCR"]== "в Анапе") $adres="ул. Парковая, д. 62Б";
      elseif($arStoreProduct["STORE_DESCR"]== "на Онежской") $adres="ул. Онежская, д. 60";
      elseif($arStoreProduct["STORE_DESCR"]== "на Кр. Партизан") $adres="ул. Кр. Партизан, д. 194";
      elseif($arStoreProduct["STORE_DESCR"]== "на Солнечной") $adres="ул. Солнечная, д. 25";
      elseif($arStoreProduct["STORE_DESCR"]== "на Российской") $adres="ул. Российская, д. 252";
      elseif($arStoreProduct["STORE_DESCR"]== "на Россинского") $adres="ул. К. Россинского , д. 7";
      elseif($arStoreProduct["STORE_DESCR"]== "на Уральской") $adres="ул. Уральская, д. 87";
      elseif($arStoreProduct["STORE_DESCR"]== "на Дзержинского") $adres="ул. Дзержинского, д. 98/3";
      elseif($arStoreProduct["STORE_DESCR"]== "на Западном обходе") $adres="ул. Западный обход, д. 34";
      elseif($arStoreProduct["STORE_DESCR"]== "на главном складе") $adres="ул. Текстильная, д. 9Б";
      $text= date("d.m.Y").";".$artikul_dks.";".$arStoreProduct["AMOUNT"].";".$edinica.";".$cena.";".$gorod.";".$adres.";;0".PHP_EOL;
      $text = iconv("UTF-8", "Windows-1251", $text);
      if($arStoreProduct["AMOUNT"]>0) file_put_contents("020_2309070991_".date("dmY").".csv", $text, FILE_APPEND);
    }
}