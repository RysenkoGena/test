<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию

$pathToLib = "/mnt/1s.s/certificates/lib/";
$pathToTxt = "/mnt/1s.s/certificates/txt/";
$vendor = "EKF";
$apiKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjA2YWU1NWM4NWJhNDEwNGQ2NTExOWJkNDg3NGE1MDY4YjAzN2RiNWFmZmM0MDRjNDRlZGQ0ZmUwYmRjYjEwMjBhNjllZmUyODM3ZDgxNDkyIn0.eyJhdWQiOiIxIiwianRpIjoiMDZhZTU1Yzg1YmE0MTA0ZDY1MTE5YmQ0ODc0YTUwNjhiMDM3ZGI1YWZmYzQwNGM0NGVkZDRmZTBiZGNiMTAyMGE2OWVmZTI4MzdkODE0OTIiLCJpYXQiOjE2MzA0OTc4MzMsIm5iZiI6MTYzMDQ5NzgzMywiZXhwIjoxNzg4MjY0MjMwLCJzdWIiOiIyMzMiLCJzY29wZXMiOltdfQ.Y3uZkSkRisCNJ8848sMzdyRHMZnBpxq7Z8FjymrVKIn1o_jPulecRS_5piY3tf_EHmS0AbJV1N_LQzl5AFwWMPR8KGN117W6l_MFScfm4x8vFiOoZel7MjtZhMrTf0HNGxhyu0kC3JK6R9joWohgI4d55yiWXUNkuyb6eCmcw9XFQqb3rNqMUr82X2mXeOoyn3wWIrQGoZbHwd074heqf8Hmu1Wu-D6fanUTWlt2LnrgUOdL-EHOH8JGZvU3wbO1mTJP0Nw_2aW8PzVCO5W3axxwAl_5cJMyc_hYUxILMzRVmbF8uWj5siK7B3rWCyVumnSC4PWeL1VNOjFz7cS8H1zbX4qPG-39pmfdDWyPA1QlLEtnsBAQdsXEqfnUN1SIBqS5_xgQwcFVEf8-GHZVV5ao0JflEbZmj-btRk6n8iFm2dSlHcMvaG1Oe3db3nCsku9EGSNaeRJav539aWD1-xR8UhtFSEg49ySEOv7Ihhv9kM3yTB-gcw9PQVH1fiWlN7IzPbS-MkuD-6ZN_N2GYn37HewBqnL3paN3wR8olLqP_xVmQNUXnux7HUhFC3ct_kflOyBvi2NKU-vRLO7xNPe5LO9Clmiza1FvrnsMTscKBLzQFUnmh1HCQ5yS7Q_x5RkgdIfG7vhHmdWguWJMa1gm0esxqjznmSYTc8drbS0";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
echo "Найдено товаров ".$vendor.": ".$list->SelectedRowsCount()."\n<br>";

$i=0; $artikuls=array();

While($obEl = $list->GetNext()){
	$i++;$j=0; $text="";
	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul")); //получаем артикул товара
    if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE']; //Создаем ассоциативный массив
	$xml_id = $obEl["XML_ID"];
	$artikul = $ob['VALUE'];
	$EKFs = load($artikul, $apiKey);

	//debug($EKFs);
	if (count($EKFs) == 0) continue;
	
	foreach ($EKFs as $EKF){
		//debug ($EKF);
		foreach ($EKF->files as $files){
			//debug($files);
			//echo $files->name."!!<br>";
			if($files -> name == "Сертификат"){
				//echo $files -> file."!!<br>";
				$text .= basename($files -> file)."\n";
				if(!file_exists($pathToLib.basename($files -> file))){
					if (copy($files -> file, $pathToLib.basename($files -> file))){
						echo "\n\rФайл ".$files -> file." успешно загружен на сервер\n";
					}
				else echo "\n\rКакая-то ошибкa скачивания файла ".$files -> file."\n";
				}
			}
		}
		
		//$text .= wFile($certificate->url, $obEl["XML_ID"], $j, $pathToLib);
		$j++;
	}
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
	//if($i > 0) break; //Полезно на время тестирования
}

function load($artikul, $apiKey){
	$curl = curl_init();

	curl_setopt_array($curl, array(
		//CURLOPT_URL => "https://ekfgroup.com/api/v1/ekf/catalog/files?vendorCode=rcbo6-1pn-1D-30-ac-av",
		CURLOPT_URL => "https://ekfgroup.com/api/v1/ekf/catalog/files?vendorCode=".$artikul,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
    		"Accept: application/json",
    		"Authorization: Bearer ".$apiKey,
    	),
	));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);
	
	$obj=json_decode($response);
	$etim= $obj-> data;
	return $etim;

if ($err)     echo "cURL Error #:" . $err; 
else return $response;

 /*
	$method="GET";                                          // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://web.se-ecatalog.ru/';                      // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = 'new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=';
	$request = $serv_addr.$serv_page.$artikul;
  
  	//$server_answer = file_get_contents('https://web.se-ecatalog.ru/new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=IMT35090');
  	$server_answer = file_get_contents($request);
	$obj=json_decode($server_answer);
	$etim=$obj->data[0]->certificates;
	return $etim;
	*/
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