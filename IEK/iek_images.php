<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию

$vendor = "ИЭК";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
$count = $list->SelectedRowsCount();
$i=0;
//echo "\n\rРабота с XML";

$urlXML = "https://lk.iek.ru/lk/infobaza/files/images_videos/products/jpeg/xml/image_import_jpeg.xml";
if(filemtime(dirname(__FILE__)."/image_import_jpeg.xml") < (time() - 3600*24)){
	echo "Файл данных слишком стар. Дата файла " . date("F d Y H:i:s", filemtime(dirname(__FILE__)."/image_import_jpeg.xml")).PHP_EOL;
	echo "скачивание XML файла ".$urlXML.PHP_EOL;
	curl_download($urlXML, dirname(__FILE__)."/image_import_jpeg.xml");
}
else echo "Файл данных свеж".PHP_EOL;

$doc = new DOMDocument;
$doc->load('image_import_jpeg.xml');
$xpath = new DOMXPath($doc);

echo "\n\rНа сайте активных товаров IEK ".$count." \n\r";

While($obEl = $list->GetNext()){
	echo ($count-$i);
	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));
    if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
	$products = $xpath->query("/root/Product[Article='".$ob['VALUE']."']/Link");
	$images = "";
	foreach ($products as $product) $images = $product -> nodeValue;
	$images." -> ".dirname(__FILE__)."/".$obEl["XML_ID"].".jpg<br>";
	//if ($images == "" && file_exists("/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/upload/images/".$obEl["XML_ID"].".jpg")) {
	//	copy("/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/upload/images/nofoto.jpg" ,"/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/upload/images/".$obEl["XML_ID"].".jpg");
	//	echo "файл ".$obEl["XML_ID"].".jpg переименован (убит)\n";
	//}
	if(!file_exists("/var/newImages/".$vendor."/".$obEl["XML_ID"].".jpg")){
		echo "/var/newImages/".$vendor."/".$obEl["XML_ID"].".jpg".PHP_EOL;
		if($images != ""){
			if ($images != "" && copy($images, "/var/newImages/".$vendor."/".$obEl["XML_ID"].".jpg"))  
				echo "\n\rФайл ".$obEl["XML_ID"].".jpg успешно загружен на сервер".PHP_EOL;

			else echo "\n\rКакая-то ошибкa при скачивании файла ".$obEl["XML_ID"].".jpg (оригинал: ".$images.")\n";
		}
		else echo "\nна удаленном сервер нет файла для скачивания \n";
	}
	else echo "\n\rФайл ".$obEl["XML_ID"].".jpg"." уже существует";
	$i++;
	//if($i>1000) break;
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