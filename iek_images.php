<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo "Модуль ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"." загружен.\r\n";
$file_name="iek.txt";
$array = array();
$file_array = file($file_name);
 if(!$file_array) echo "Ошибка открытия файла ".$file_name;
 else {
		for($i=0; $i < count($file_array); $i++){
			$tex  = "".$file_array[$i]."";
			$pieces = explode(";", $tex);
			$array[$pieces[0]]=$pieces[1];
		}
 }	 
//print_r($array);
$ii=0;
echo "\n\rРабота с XML";
$doc = new DOMDocument;
$doc->load('image_import_jpeg.xml');
$xpath = new DOMXPath($doc);

echo "\n\rНачало цикла перебора\n\r";
  foreach ($array as $kod => $artikul){
	$artikul=trim($artikul);
	$ii++;
	$arFilter = Array("IBLOCK_ID"=>4, "CODE"=>$kod);
	$res = CIBlockElement::GetList(Array(), $arFilter);
	if ($ob = $res->GetNextElement()){
		$arFields = $ob->GetFields(); // поля элемента
//	    $arFields = $ob->TIMEST; // поля элемента
//		echo $arFields[ID];
//	    print_r($arFields);
		$arProps = $ob->GetProperties(); // свойства элемента
//    	print_r($arProps);
		$el = new CIBlockElement;
$products = $xpath->query("/NewDataSet/DirectImage[Article='".$artikul."']/IMAGE_URL");
foreach ($products as $product) $a = $product->nodeValue;
		echo $ii." ".$kod." ".$artikul." => ".$a."\n\r";
		if (copy($a, "/var/www/images/".$kod.".jpg"))     echo "\n\rФайл успешно загружен на сервер";
		else echo "\n\rКакая-то ошибкa";
//		$res = $el->Update($arFields[ID], Array("DETAIL_TEXT"=>$a));
	}
} 
?>