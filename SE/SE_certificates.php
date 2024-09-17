<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию

$pathToLib = "/mnt/1s.s/certificates/lib/";
$pathToTxt = "/mnt/1s.s/certificates/txt/";
$vendor = "Schneider Electric";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
echo "Найдено товаров ".$vendor.": ".$list->SelectedRowsCount()."\n";

$i=0; $artikuls=array();
While($obEl = $list->GetNext()){
	$i++;$j=0; $text="";
	$ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext(); //получаем артикул товара
    $artikuls[$obEl["XML_ID"]] = $ob['VALUE']; //Создаем ассоциативный массив
	$xml_id = $obEl["XML_ID"];

	$certificates = load($ob['VALUE']);
	if (count($certificates) == 0) continue;
	//echo "Код ".$obEl["XML_ID"]." ".count($certificates)."\n\n";
	//echo $certificates[count($certificates)-1]->type->description." ".$certificates[count($certificates)-1]->url."<br>";
	//wFile($certificates[count($certificates)-1]->url, $obEl["XML_ID"]);
	
	foreach ($certificates as $certificate){
		$text .= wFile($certificate->url, $obEl["XML_ID"], $j, $pathToLib);
		//echo $certificate->type->description." ".$certificate->url."<br>";
		$j++;
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
	//if($i>10) break;
}
function load($artikul){
	$method="GET";                                          // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://api.systeme.ru/';                      // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = 'new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=';
	$request = $serv_addr.$serv_page.$artikul;
  
  	//$server_answer = file_get_contents('https://web.se-ecatalog.ru/new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=IMT35090');
  	$server_answer = file_get_contents($request);
	$obj=json_decode($server_answer);
	$etim=$obj->data[0]->certificates;
	return $etim;
}
function wFile($url, $artikul, $i, $pathToLib){
	//$url = 'https://web.se-ecatalog.ru/certificates/default/download/1066';
	$path = $pathToLib.$artikul."_".$i.".pdf";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	curl_close($ch);
	echo $path."\n";
	//echo $data;
	file_put_contents($path, $data);
	return $artikul."_".$i.".pdf\n";
}
?>