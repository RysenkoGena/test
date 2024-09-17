<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию

$j = 0; $nextPageToken = "";
do {
    $nextPageToken = requestList($nextPageToken);
	echo "Страница ".$j++."<br>";
} while ($nextPageToken);


function requestList($pageToken){
	if($pageToken != "") $textPageToken = "&page_token=".$pageToken;
	else $textPageToken = "";
	$url = "https://api.partner.market.yandex.ru/v2/campaigns/23812451/offer-mapping-entries.xml?limit=100".$textPageToken;
	echo $url."<br>";
	$curl = curl_init($url); // создаем экземпляр curl
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
		'Content-Type: application/xml',
		'Accept: application/xml',
		'Authorization: OAuth oauth_token="AQAAAAAC_m26AAe4NilqUTNK7kzQlzLotG4nCbs",oauth_client_id="83a2546b50ef4de2b997470989efa322"'
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($curl);
	curl_close($curl);

	echo "ответ ниже<br>";
    lg($result);
	$document = new SimpleXMLElement($result);
	$document -> asXML("responseList.xml");
	//debug ($document);
	if ($document->status == "OK"){
		echo "Ответ без ошибок";
		$requestUpdate = new domDocument("1.0", "utf-8"); // Создаём XML-документ версии 1.0 с кодировкой utf-8
		$root = $requestUpdate->createElement("stocks"); // Создаём корневой элемент
		$requestUpdate->appendChild($root);

		$offers = $requestUpdate->createElement("skus"); // Создаём элемент skus
		$root->appendChild($offers);

		foreach($document->result->{"offer-mapping-entries"}->{"offer-mapping-entry"} as $item){
			$ar = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => 4, "ACTIVE" => "Y", "XML_ID" => $item->offer->{"shop-sku"}), false, false, Array("ID", "TIMESTAMP_X", "PROPERTY_CENAZ", "IBLOCK_SECTION_ID"))->GetNext();
			$amount = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $ar["ID"], "STORE_ID" => 10), false, false, array("AMOUNT"))->Fetch();
			$kit = getKit($ar["PROPERTY_CENAZ_VALUE"], $ar["IBLOCK_SECTION_ID"]);
			
			if($kit){
				if($amount["AMOUNT"] < $kit) $amount = 0;
				$amount = floor($amount["AMOUNT"] / $kit);
				if($kit != 1) $amount = $kit;
			}
		else $amount = -1;

			//echo $item->offer->{"shop-sku"}."<br>";
			$sku = $requestUpdate->createElement("sku"); // Создаём элемент offerUpdatePrice
			$offers->appendChild($sku);
			$atr_sku = $requestUpdate->createAttribute('sku');
			$atr_sku->value = $item->offer->{"shop-sku"};
			$sku->appendChild($atr_sku);
			$atr_warehouse = $requestUpdate->createAttribute('warehouse-id');
			$atr_warehouse->value = 258694;
			$sku->appendChild($atr_warehouse);

			$items = $requestUpdate->createElement("items"); // Создаём элемент items
			$sku->appendChild($items);
			$item = $requestUpdate->createElement("item"); // Создаём элемент item
			$items->appendChild($item);
			$atr_count = $requestUpdate->createAttribute('count');
			$atr_count->value = $amount;
			$item->appendChild($atr_count);
			$atr_type = $requestUpdate->createAttribute('type');
			$atr_type->value = "FIT";
			$item->appendChild($atr_type);
			$atr_updated = $requestUpdate->createAttribute('updated-at');
			//$atr_updated->value = date("c", strtotime($ar["TIMESTAMP_X"]));
			$atr_updated->value = date("c");
			$item->appendChild($atr_updated);
			
		}
		//print_r(
			foreach ($document->result->paging->attributes() as $a => $b) echo "<br>".$b;
	}else echo "Ошибка в ответе 1";
    lg($requestUpdate);
	$requestUpdate->save("requestStocks".$b.".xml"); // Сохраняем полученный XML-документ в файл
	$requestXML2 = $requestUpdate->saveXML();

	debug ($requestXML2);
	$xml = $requestXML2;

	// ============================== отправка количества ==============================
	$url = "https://api.partner.market.yandex.ru/v2/campaigns/23812451/offers/stocks.xml";
	$curl = curl_init($url); // создаем экземпляр curl
	//curl_setopt($curl, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_PUT, true);
	//curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); // note the PUT here
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$headers = array(
		'Authorization: OAuth oauth_token="AQAAAAAC_m26AAe4NilqUTNK7kzQlzLotG4nCbs",oauth_client_id="83a2546b50ef4de2b997470989efa322"',
		//'Content-Type: application/xml',
		//'Accept: application/xml',
		//'X-HTTP-Method-Override: PUT'	
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
	curl_setopt($curl, CURLOPT_INFILE, fopen("requestStocks".$b.".xml", 'r'));
	curl_setopt($curl, CURLOPT_INFILESIZE, filesize("requestStocks".$b.".xml"));
	curl_setopt($curl, CURLOPT_VERBOSE, 1); 
	$result = curl_exec($curl);
	curl_close($curl);

	echo "ответ по количеству:<br>";
	debug($result);
	$document = new SimpleXMLElement($result);
	$document -> asXML("responseStocks".$b.".xml");
	//if(strlen($b) > 5)  requestList($b);
	return $b;
}

function getKit($cenaz, $IBLOCK_SECTION_ID){
	$rootCatalog = getParent($IBLOCK_SECTION_ID);
	//return $rootCatalog;
	if($rootCatalog == 1231) return 50; //кабель - бухты по 50 метров
	elseif($rootCatalog == 1342 || $rootCatalog == 1849){ //лампы и розетки
		if($cenaz >= 350) return 1;
		elseif($cenaz >= 70 && $cenaz < 350) return 5;
		elseif($cenaz >= 35 && $cenaz < 70) return 10;
		elseif($cenaz < 35) return false;
	}
	elseif($rootCatalog == 1435 || $rootCatalog == 1579 || $rootCatalog == 1716 || $rootCatalog == 2183 || $rootCatalog == 2192){ //
		if($cenaz >= 350) return 1;
		else return false;
	}
	elseif($rootCatalog == 2102){ //модульное оборудование
		if($cenaz >= 350) return 1;
		elseif($cenaz >= 115 && $cenaz < 350) return 3;
		elseif($cenaz >= 70 && $cenaz < 115) return 5;
		elseif($cenaz < 70) return false;
	}        
	return $rootCatalog;
}

function getParent($id){
$scRes = CIBlockSection::GetNavChain(
	4,
	$id,
	array("ID","DEPTH_LEVEL")
);
while($arGrp = $scRes->Fetch()){
	if ($arGrp['DEPTH_LEVEL'] == 1)	$ROOT_SECTION_ID = $arGrp['ID'];
}
return $ROOT_SECTION_ID;
}

?>