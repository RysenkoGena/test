<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
function storeP($id,$artikul_dks,$kod){

    $text = $artikul_dks.";".$kod.";https://yugkabel.ru/products/".$kod."/;\n";
    $text = iconv("UTF-8", "Windows-1251", $text);
    file_put_contents("020_2309070991_".date("dmY").".csv", $text, FILE_APPEND);
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
  storeP($obEl["ID"],$ob['VALUE'],$obEl["XML_ID"]);
  //if ($i==0) print_r($obEl["XML_ID"]);
  //echo $i++;
}
echo '<a href=020_2309070991_'.date("dmY").'.csv>Скачать файл 020_2309070991_'.date("dmY").'.csv</a>';

$file = '020_2309070991_'.date("dmY").'.csv';
$remote_file = '020_2309070991_'.date("dmY").'.csv';

         ?><pre><? //print_r($artikuls); ?></pre><?

      ?>
