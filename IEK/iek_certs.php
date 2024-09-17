<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию

$vendor = "ИЭК";
$pathToLib = "/mnt/1s.s/certificates/lib/";
$pathToTxt = "/mnt/1s.s/certificates/txt/";
// $pathToXML = "https://www.iek.ru/partners/infobaza/docs/sertificates/sertApi.xml"; автоматом не скачивается, нужна авторизация.
// if (copy($pathToXML, basename($pathToXML))) {
// 	echo "Скачали свежий файл с данными";
// 	chmod(basename($pathToXML), 0777);
// }
// else echo "ошибка скачивания файла данных";


$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));

$i=0;
$doc = new DOMDocument; //загрузка файла данных сертификатов IEK
$doc->load("sertApi.xml");
$xpath = new DOMXPath($doc);
//print_r($doc);
echo "\n\rНа сайте активных товаров IEK ".$list->SelectedRowsCount()." \n\r";

While($obEl = $list->GetNext()){
	$text = "";
	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));
    if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE']; //создание ассоциативного массива
	$links = $xpath->query("/root/Product[Article='".$ob['VALUE']."']/Link");
	echo $ob['VALUE']."\n";

	foreach ($links as $link){
		$value = $link -> nodeValue;
		//echo  $value." -> ".dirname(__FILE__)."/".$obEl["XML_ID"].".pdf<br>\n";
		echo  $value." -> ".$pathToLib.basename($value)."  (".$i."/".$list->SelectedRowsCount().")\n";
		$text .= basename($value)."\n";
		if(!file_exists($pathToLib.basename($value))){
			//if (copy($value, dirname(__FILE__)."/".$obEl["XML_ID"].".pdf"))     echo "\n\rФайл успешно загружен на сервер";
			if (copy($value, $pathToLib.basename($value))){
				echo "\n\rФайл успешно загружен на сервер\n";
			}
		else echo "\n\rКакая-то ошибкa скачивания";
		}
		else echo "Такой файл уже есть. (".$i."/".$list->SelectedRowsCount().")\n";
	}
	if($text != "") {
		file_put_contents($pathToTxt.$obEl["XML_ID"].".txt", $text);
		echo "Обновим файл ".$pathToTxt.$obEl["XML_ID"].".txt\n";
	}
	else {
		if(file_exists($pathToTxt.$xml_id.".txt")){
			 unlink($pathToTxt.$xml_id.".txt");
		}
	}
	$i++;
	//if($i>100) break;//Ограничение на количество товаров (полезно для тестирования)
}
?>