<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
$vendor = array("Feron", "STEKKER", "SAFFIT");
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor,	"ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);

$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
echo "Количество товаров ".$vendor.": ".$list->SelectedRowsCount()."\n";
$urlXML = "https://shop.feron.ru/bitrix/catalog_export/im.xml";
//echo "скачивание XML файла ".$urlXML;
//curl_download($urlXML, dirname(__FILE__)."/im.xml");

echo "\n\rРабота с XML".PHP_EOL.PHP_EOL;
$doc = new DOMDocument;
if(!$doc->load(dirname(__FILE__)."/im.xml")) echo "Ошибка загрузки файла".PHP_EOL;
$xpath = new DOMXPath($doc);

While($obEl = $list->GetNext()){
	$ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext();
    $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
	$ids["ID"][$ob['VALUE']] = $obEl["ID"];
	$ids["XML_ID"][$ob['VALUE']] = $obEl["XML_ID"];
}
//echo "массив artikuls".PHP_EOL;
//print_r($artikuls);
var_dump($xpath);
$imagesArr = array(); $j=0;
foreach($artikuls as $kod => $artikul){
	if($artikul != ""){
		$query = "/yml_catalog/shop/offers/offer[vendorCode='".$artikul."']/param";
		//echo $query.PHP_EOL;
		$params = $xpath->query($query);
		//var_dump($params);
		$images = ""; $i = 0; 
		foreach ($params as $param){
			if($param->getAttribute('name') == "Количество на складе «Москва»"){
				//echo $param -> nodeValue.PHP_EOL;
				$stocks[$artikul] = $param -> nodeValue;
			}
			$i++;
		}
		$j++;
		//if($j > 10) break;
	}
}
echo "массив stoks".PHP_EOL;
print_r($stocks);

$i = count($stocks);
$ii = 0;
foreach($stocks as $key => $external_count){
    $ii++;
    if ($external_count >= 0 && CIBlockElement::SetPropertyValueCode($ids["ID"][$key], "vneshniy_sklad", $external_count)) echo "Изменен товар: ".$ids["XML_ID"][$key]." ".$ids["ID"][$key]." ".$external_count." шт.<br>".PHP_EOL;
    //if($ii > 10) break;
}

function curl_download($url, $file)
{
	// открываем файл, на сервере, на запись
	$dest_file = @fopen($file, "w");
	$resource = curl_init();	// открываем cURL-сессию
	curl_setopt($resource, CURLOPT_URL, $url);	// устанавливаем опцию удаленного файла
	curl_setopt($resource, CURLOPT_FILE, $dest_file);	// устанавливаем место на сервере, куда будет скопирован удаленной файл
	curl_setopt($resource, CURLOPT_HEADER, 0);	// заголовки нам не нужны
	curl_exec($resource);	// выполняем операцию
	curl_close($resource);	// закрываем cURL-сессию
	fclose($dest_file);		// закрываем файл
}
?>