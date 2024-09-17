<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
$vendor = "Schneider Electric";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
$count = $list->SelectedRowsCount();
echo "Найдено товаров: ".$count.PHP_EOL;

echo "\n\rНачало цикла перебора\n\r";
$i=0; $artikuls=array();
While($obEl = $list->GetNext()){
	echo PHP_EOL."Осталось товаров: ".($count - $i++).PHP_EOL;
	if(file_exists("/var/newImages/SE/".$obEl["XML_ID"].".jpg")){
		echo "файл ".$obEl["XML_ID"].".jpg уже заргужен ранее".PHP_EOL;
		continue;
	}
	$ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext();
    $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
	$ids[$ob['VALUE']]["XML_ID"] = $obEl["XML_ID"];
	$ids[$ob['VALUE']]["ID"] = $obEl["ID"];
}
//var_dump($artikuls);

$allImagesLinks = array(); $n=0; $j=0;
$countItems = count($artikuls);
echo "Обновляем картинки для ".$countItems. " товаров".PHP_EOL;
foreach($artikuls as $id => $artikul){
	$j++;
	if($n == 0) $text = $artikul;
	else $text .= (",".$artikul);
	$n++;
	if($n >= 45 || $j == $countItems) {
		$n = 0;
		//echo $text.PHP_EOL.PHP_EOL;
		$imgLinks = loadCurl($text);
		var_dump($imgLinks);
		foreach($imgLinks as $VendorArtikul => $imgLink){
			$allImagesLinks[$VendorArtikul] = $imgLink;
		}
	}
}
var_dump($allImagesLinks);

	foreach ($allImagesLinks as $VendorArtikul => $links){
		$j=0;
		//var_dump($links);
		foreach($links as $link){
			if ($j == 0) $text = ".jpg";
			else $text = "_".$j.".jpg";
			if(!file_exists("/var/newImages/SE/".$ids[$VendorArtikul]["XML_ID"].$text)){
				echo "Загружаем файл ".$link." -> /var/newImages/SE/".$ids[$VendorArtikul]["XML_ID"].$text.PHP_EOL;
				//if (copy($link, "/var/newImages/SE/".$ids[$VendorArtikul]["XML_ID"].$text))  echo "\n\rФайл успешно загружен на сервер".PHP_EOL;
				if (curl_download($link, "/var/newImages/SE/".$ids[$VendorArtikul]["XML_ID"].$text))  echo "\n\rФайл успешно загружен на сервер".PHP_EOL;
				else echo "Какая-то ошибкa".PHP_EOL.PHP_EOL;
			}
			else echo "\n\rФайл ".$ids[$VendorArtikul]["XML_ID"].$text." уже существует".PHP_EOL;
			$j++;
		}
	}

function loadCurl($artikul){
	$ARR = explode(",", $artikul);
	echo "Передано артикулов ".count($ARR).PHP_EOL;
	$get = array(
		'request'  => '',
		'accessCode' => 'oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ',
		'commercialRef' => $artikul
		//'commercialRef' => "ATN000313,ATN000383"
	);
	//$ch = curl_init('https://web.se-ecatalog.ru/new-api/JSON/getdata?' . http_build_query($get)); //старый адрес сменился на новый
	$ch = curl_init('https://api.systeme.ru/new-api/JSON/getdata?' . http_build_query($get));
	//echo 'https://web.se-ecatalog.ru/new-api/JSON/getdata?' . http_build_query($get).PHP_EOL;
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$html = curl_exec($ch);
	curl_close($ch);
	$obj = json_decode($html);
	print_r($html);

	//return $obj->data[0]->images;
	$links = array();
	foreach($obj->data as $x){
		foreach($x->images as $image){
			$links[$x->commercialRef][] = $image->url;
		}
	}
	echo "ответ получен".PHP_EOL;
	return $links;
	//return $obj->data;
	//echo $obj;
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
	return true;
}

function load($artikul){
	$method="GET";                                          // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://web.se-ecatalog.ru/';                      // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = 'new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=';
	$request = $serv_addr.$serv_page.$artikul;
   	//$server_answer = file_get_contents('https://web.se-ecatalog.ru/new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=IMT35090');
  	$server_answer = file_get_contents($request);
	$obj=json_decode($server_answer);
	$etim=$obj->data[0]->images;
	return $etim;
}
?>