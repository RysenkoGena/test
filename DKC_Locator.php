<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
function storeP($id,$artikul_dks,$edinica, $cena_akcii){
  $obStoreProduct = CCatalogStoreProduct::GetList(
     array("STORE_ID" => "ASC"),
     array("PRODUCT_ID" => $id),
     false,
     false,
     array("ID", "STORE_ID", "AMOUNT", "STORE_NAME", "STORE_DESCR")
  );
//echo "<table cellspacing=0 border=0 cellpadding=0>";
$cena = iarga::getprice($id);
if($cena_akcii!="" && $cena_akcii<$cena) $cena=$cena_akcii;
$cena=str_replace(".", ",", $cena);
$filedata="";

  while ($arStoreProduct = $obStoreProduct->Fetch()){
    if($arStoreProduct["STORE_DESCR"]== "в Анапе") $gorod="Анапа";
    else $gorod = "Краснодар";
    $adres="";
    if($arStoreProduct["STORE_DESCR"]== "в Анапе") $adres="ул. Парковая, 62 Б";
    elseif($arStoreProduct["STORE_DESCR"]== "на Онежской") $adres="ул. Онежская, 60";
    elseif($arStoreProduct["STORE_DESCR"]== "на Кр. Партизан") $adres="ул. Кр. Партизан, 194";
    elseif($arStoreProduct["STORE_DESCR"]== "на Солнечной") $adres="ул. Солнечная, 25";
    elseif($arStoreProduct["STORE_DESCR"]== "на Российской") $adres="ул. Российская, 252";
    elseif($arStoreProduct["STORE_DESCR"]== "на Уральской") $adres="ул. Уральская, 87";
    elseif($arStoreProduct["STORE_DESCR"]== "на Дзержинского") $adres="ул. Дзержинского, 98";
    elseif($arStoreProduct["STORE_DESCR"]== "на Западном обходе") $adres="ул. Западный обход, 34";
    elseif($arStoreProduct["STORE_DESCR"]== "на главном складе") $adres="ул. Текстильная 9Б";
    $text= date("d.m.Y").";".$artikul_dks.";".$arStoreProduct["AMOUNT"].";".$edinica.";".$cena.";".$gorod.";".$adres.";;0\n";
    $text = iconv("UTF-8", "Windows-1251", $text);
    if($arStoreProduct["AMOUNT"]>0) file_put_contents("020_2309070991_".date("dmY").".csv", $text, FILE_APPEND);
    }
  echo $filedata;
}

$vendor = "ДКС";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
//echo "Найдено товаров: ".$list->SelectedRowsCount()."<br>";
file_put_contents("020_2309070991_".date("dmY").".csv" , "");
$i=0; $artikuls=array();
While($obEl = $list->GetNext()){
  $res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul", ));
  $res_edinica = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"edinica", ));
  $res_cena_akcii = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"cenaakcii1", ));
  if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
  $ob_edinica = $res_edinica->GetNext();
  $ob_cena_akcii = $res_cena_akcii->GetNext();
  storeP($obEl["ID"],$ob['VALUE'],$ob_edinica['VALUE'],$ob_cena_akcii['VALUE']);
  //if ($i==0) print_r($obEl["XML_ID"]);
  //echo $i++;
}
echo '<a href=020_2309070991_'.date("dmY").'.csv>Скачать файл 020_2309070991_'.date("dmY").'.csv</a>';

$file = '020_2309070991_'.date("dmY").'.csv';
$remote_file = '020_2309070991_'.date("dmY").'.csv';

         ?><pre><? //print_r($artikuls); ?></pre><?

      ?>
