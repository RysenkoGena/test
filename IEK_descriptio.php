<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

$arrFilter = Array("ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "DETAIL_TEXT"));
$count = $list->SelectedRowsCount();
echo "Найдено товаров: ".$count."<br>\n";

$artikuls=array(); $i = 0; $counts = array();
While($obEl = $list->GetNext()){
  //$i++;
  if($obEl["DETAIL_TEXT"] != ""){
    //echo $obEl["DETAIL_TEXT"].PHP_EOL;
    $i++;
    $ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"proizvoditel_filter"))->GetNext();
    $counts[$ob["VALUE"]]++;
    //print_r($ob["VALUE"]);
  } 
 }
 echo $i;
 debug($counts);

?>