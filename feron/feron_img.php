<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
//$vendor = "Feron";
$vendor = array("Feron", "STEKKER", "SAFFIT");
$arrFilter = Array(	"PROPERTY_proizvoditel_filter"=>$vendor,	"ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);

$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
echo "Количество товаров ".$vendor.": ".$list->SelectedRowsCount()."\n";
$urlXML = "https://shop.feron.ru/bitrix/catalog_export/im.xml";
if(filemtime(dirname(__FILE__)."/im.xml") < (time() - 3600*24)){
	echo "Файл данных слишком стар. Дата файла " . date("F d Y H:i:s", filemtime(dirname(__FILE__)."/im.xml")).PHP_EOL;
	echo "скачивание XML файла ".$urlXML.PHP_EOL;
	curl_download($urlXML, dirname(__FILE__)."/im.xml");
}
else echo "Файл данных свеж".PHP_EOL;

echo "\n\rРабота с XML".PHP_EOL.PHP_EOL;
$doc = new DOMDocument;
$doc->load('im.xml');
$xpath = new DOMXPath($doc);

While($obEl = $list->GetNext()){
	$ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext();
    $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
}
//print_r($artikuls);
$imagesArr = array(); $j=0;
foreach($artikuls as $kod => $artikul){
	if($artikul != ""){
		$query = "/yml_catalog/shop/offers/offer[vendorCode='".$artikul."']/picture";
		//echo $query.PHP_EOL;
		$pictures = $xpath->query($query);
		//var_dump($pictures);
		$images = ""; $i = 0; 
		foreach ($pictures as $picture){
			if($i == 0) $imagesArr[$kod][$kod.".jpg"] = $picture -> nodeValue;
			else $imagesArr[$kod][$kod."_".$i.".jpg"] = $picture -> nodeValue;
			//echo $picture -> nodeValue.PHP_EOL;
			$i++;
		}
		$j++;
		//if($j > 10) break;
	}
}
$i = count($imagesArr);
echo "Массив файлов из " . $i." шт".PHP_EOL;
foreach ($imagesArr as $items){
	echo "Осталось товаров ".$i-- . " " . PHP_EOL;
	foreach($items as $fileName => $url){
		if(!file_exists("/var/newImages/Feron/".$fileName)){
			echo "файла ".$fileName." нет. Загружаем ".$url.PHP_EOL;
			//echo $url.PHP_EOL.dirname(__FILE__)."/img/".$fileName.PHP_EOL;
			curl_download($url, "/var/newImages/Feron/".$fileName);
		}
	}
	//if(!file_exists("img/".))
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

//var_dump($a);

?>