<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
echo "Модуль ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"." загружен.\r\n";

$pathToLib = "/mnt/1s.s/certificates/lib/";
$pathToTxt = "/mnt/1s.s/certificates/txt/";
$url = "https://e-catalogue.legrand.ru/upload/certificates/";
$vendor = "Legrand";

$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));

$i=0;
$fileName = "certificates_table.csv";
echo "\n\rРабота с файлом ".$fileName;

$f = fopen($fileName,'r');
$elements = array();
while (($buffer = fgets($f)) !== false) {
	$pieces = explode(";", $buffer);
	if($pieces[3] == "not_oblig .pdf") continue;
	$elements[$pieces[0]] = $pieces[3];
	//print_r ($buffer);
	//$array[] = $buffer;
	//echo $pieces[0]." ".$pieces[3]."\n";
}

// while (!feof($f)){
//     echo fgets($f);
// }
fclose($f);


echo "\n\rНа сайте активных товаров ".$vendor." ".$list->SelectedRowsCount()." \n\r";
While($obEl = $list->GetNext()){
	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));
    if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
	$xml_id = $obEl["XML_ID"]; $artikul = $ob['VALUE'];
	if(isset($elements[$artikul])){
		echo "XML_ID ".$xml_id."->".$artikul."->".$elements[$artikul]."\n";
			//echo $files -> file."!!<br>";
			$text = $elements[$artikul]."\n";
			if(!file_exists($pathToLib.$elements[$artikul])){
				if (copy($url.$elements[$artikul], $pathToLib.$elements[$artikul])){
					echo "\n\rФайл ".$elements[$artikul]." успешно загружен на сервер\n";
				}
			else echo "\n\rКакая-то ошибкa скачивания файла ".$elements[$artikul]."\n";
			}
	}
		//$text .= wFile($certificate->url, $obEl["XML_ID"], $j, $pathToLib);
		$j++;

	$text_count = " (".$i." из ".$list->SelectedRowsCount().")";
	if($text != "") {
		//echo $text."!<br>";
		file_put_contents($pathToTxt.$xml_id.".txt", $text); // >>55555.txt
		echo "Обновим файл ".$pathToTxt.$xml_id.".txt.".$text_count."\n";
	}
	else {
		if(file_exists($pathToTxt.$xml_id.".txt")){
			 unlink($pathToTxt.$xml_id.".txt");
		}
	}
	$i++;
	//if($i>10) break;
}
?>