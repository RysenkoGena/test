<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию

$vendor = "Schneider Electric";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID"));
$count = $list->SelectedRowsCount();
echo "Найдено товаров: ".$count.PHP_EOL;

$artikuls=array();
While($obEl = $list->GetNext()){
	echo "Осталось: ". $count--." ";
   	$ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext();
	$external_count = intval(loadCurl($ob['VALUE']));
	//echo $external_count.PHP_EOL;
	if ($external_count >= 0 && CIBlockElement::SetPropertyValueCode($obEl["ID"], "vneshniy_sklad", $external_count )) echo "Изменен товар: ".$obEl["XML_ID"]." ".$external_count." шт.".PHP_EOL;
 }
 print_r($artikuls, true);

function loadCurl($artikul){
	$get = array(
		'request'  => '',
		'accessCode' => 'oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ',
		'commercialRef' => $artikul
	);
	$ch = curl_init('https://api.systeme.ru/new-api/JSON/getstock?' . http_build_query($get));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$html = curl_exec($ch);
	curl_close($ch);
	$obj = json_decode($html);
	//print_r($obj);
	//echo $obj->data[0]->stocks[0]->count;
	//echo $html.PHP_EOL;
	return $obj->data[0]->stocks[0]->count;
}
?>