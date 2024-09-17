<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');


$vendor = "ДКС";
//$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
//$arrFilter = Array("ACTIVE"=>"Y");
$arrFilter = Array();

$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
//$sectFilter = Array("IBLOCK_ID"=>4);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
//echo "Найдено товаров: ".$list->SelectedRowsCount()."<br>";
file_put_contents("020_2309070991_".date("dmY").".csv" , "");
$i=0; $artikuls=array();
While($obEl = $list->GetNext()){
  $res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul", ));
  $res_MRC = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"cenamrc", ));
  $res_cenaroznichnaya = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"cenaroznichnaya", ));
  if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
  $ob_MRC = $res_MRC->GetNext();
  $ob_cenaroznichnaya = $res_cenaroznichnaya->GetNext();
  //storeP($obEl["ID"],$ob['VALUE'],$ob_edinica['VALUE'],$ob_cena_akcii['VALUE']);
  if($ob_MRC['VALUE'] > $ob_cenaroznichnaya['VALUE']) echo "<a href=/products/".$obEl["XML_ID"]." target=_blank>".$obEl["XML_ID"]."</a><br>";
  //if ($i==0) print_r($obEl["XML_ID"]);
  //echo $i++;
}
//echo '<a href=020_2309070991_'.date("dmY").'.csv>Скачать файл 020_2309070991_'.date("dmY").'.csv</a>';

//$file = '020_2309070991_'.date("dmY").'.csv';
//$remote_file = '020_2309070991_'.date("dmY").'.csv';

         ?><pre><? //print_r($artikuls); ?></pre><?

      ?>
