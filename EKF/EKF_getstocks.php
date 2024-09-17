<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию

$vendor = "EKF";
$apiKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjA2YWU1NWM4NWJhNDEwNGQ2NTExOWJkNDg3NGE1MDY4YjAzN2RiNWFmZmM0MDRjNDRlZGQ0ZmUwYmRjYjEwMjBhNjllZmUyODM3ZDgxNDkyIn0.eyJhdWQiOiIxIiwianRpIjoiMDZhZTU1Yzg1YmE0MTA0ZDY1MTE5YmQ0ODc0YTUwNjhiMDM3ZGI1YWZmYzQwNGM0NGVkZDRmZTBiZGNiMTAyMGE2OWVmZTI4MzdkODE0OTIiLCJpYXQiOjE2MzA0OTc4MzMsIm5iZiI6MTYzMDQ5NzgzMywiZXhwIjoxNzg4MjY0MjMwLCJzdWIiOiIyMzMiLCJzY29wZXMiOltdfQ.Y3uZkSkRisCNJ8848sMzdyRHMZnBpxq7Z8FjymrVKIn1o_jPulecRS_5piY3tf_EHmS0AbJV1N_LQzl5AFwWMPR8KGN117W6l_MFScfm4x8vFiOoZel7MjtZhMrTf0HNGxhyu0kC3JK6R9joWohgI4d55yiWXUNkuyb6eCmcw9XFQqb3rNqMUr82X2mXeOoyn3wWIrQGoZbHwd074heqf8Hmu1Wu-D6fanUTWlt2LnrgUOdL-EHOH8JGZvU3wbO1mTJP0Nw_2aW8PzVCO5W3axxwAl_5cJMyc_hYUxILMzRVmbF8uWj5siK7B3rWCyVumnSC4PWeL1VNOjFz7cS8H1zbX4qPG-39pmfdDWyPA1QlLEtnsBAQdsXEqfnUN1SIBqS5_xgQwcFVEf8-GHZVV5ao0JflEbZmj-btRk6n8iFm2dSlHcMvaG1Oe3db3nCsku9EGSNaeRJav539aWD1-xR8UhtFSEg49ySEOv7Ihhv9kM3yTB-gcw9PQVH1fiWlN7IzPbS-MkuD-6ZN_N2GYn37HewBqnL3paN3wR8olLqP_xVmQNUXnux7HUhFC3ct_kflOyBvi2NKU-vRLO7xNPe5LO9Clmiza1FvrnsMTscKBLzQFUnmh1HCQ5yS7Q_x5RkgdIfG7vhHmdWguWJMa1gm0esxqjznmSYTc8drbS0";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
$count = $list->SelectedRowsCount();
echo "Найдено товаров ".$vendor.": ".$count."\n<br>";

$i=0; $artikuls=array();
$counts = array();
While($obEl = $list->GetNext()){
	$i++;$j=0;
	//if($i > 5) break;
	$ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext(); //получаем артикул товара
    //$artikuls[$obEl["XML_ID"]] = $ob['VALUE']; //Создаем ассоциативный массив
	$xml_id = $obEl["XML_ID"];
	$artikul = $ob['VALUE'];
	$ids["ID"][$ob['VALUE']] = $obEl["ID"];
	$ids["XML_ID"][$ob['VALUE']] = $obEl["XML_ID"];
	$EKFs = load($artikul, $apiKey);

    //print_r($EKFs);
	if (count($EKFs) == 0) continue;
	//debug($EKFs);
	
	foreach($EKFs as $EKF){
		if($EKF->warehouse->name == "Ростов-на-Дону"){
			$counts[$EKF->vendorCode] = intval($EKF->quantity);
		}
	}
	
	$text_count = " (".$i." из ".$list->SelectedRowsCount().")";

	echo "Обрабатываем товар ".$xml_id." ".$text_count."\n";
	//if($i > 0) break; //Полезно на время тестирования
}
print_r($counts);

foreach($counts as $key => $external_count){
    $ii++;
    if ($external_count >= 0 && CIBlockElement::SetPropertyValueCode($ids["ID"][$key], "vneshniy_sklad", $external_count)) echo "Изменен товар: ".$ids["XML_ID"][$key]." ".$ids["ID"][$key]." ".$external_count." шт.<br>".PHP_EOL;
    //if($ii > 10) break;
}

function load($artikul, $apiKey){
    //echo "Отработка Load для ".$artikul.PHP_EOL;
	$curl = curl_init();
    //echo "https://ekfgroup.com/api/v1/ekf/stocks?vendor_Code=".$artikul.PHP_EOL;
	curl_setopt_array($curl, array(
		//CURLOPT_URL => "https://ekfgroup.com/api/v1/ekf/catalog/products?vendorCode=rcbo6-1pn-1D-30-ac-av",
		CURLOPT_URL => "https://ekfgroup.com/api/v1/ekf/stocks?vendor_code=".$artikul,
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
	$obj = json_decode($response);
    //d($obj);
	$etim = $obj -> data;
	if ($err) echo "cURL Error #:" . $err; 
	else return $etim;
}

function wFile($url, $artikul, $i, $pathToLib){
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