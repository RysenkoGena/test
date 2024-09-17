<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$tmpFile = $_SERVER["DOCUMENT_ROOT"]."/test/YM/log.txt";
$errorFile = $_SERVER["DOCUMENT_ROOT"]."/test/YM/error.txt";
$campaignId = "23812451";
ob_end_flush(); //отключить буферизацию
$names = array(); $categories =  array(); $vendors = array(); $vendorCode = array(); $sku = array(); $manufacturer_country = array();
getItemsBySectionId(1231); // 01. кабель
//getItemsBySectionId(1342); // 02. лампы
//getItemsBySectionId(1435); // 03. светильники
//getItemsBySectionId(1579); // 04. электрооборудование
//getItemsBySectionId(1716); // 05. эл. изделия
//getItemsBySectionId(1849); // 06. розетки
//getItemsBySectionId(2102); // 10. модулька
//getItemsBySectionId(2183); // 11. счетчики
//getItemsBySectionId(2192); // 12. щиты

$url = "https://api.partner.market.yandex.ru/v2/campaigns/".$campaignId."/offer-mapping-entries/suggestions.xml";

$dom = new domDocument("1.0", "utf-8"); // Создаём XML-документ версии 1.0 с кодировкой utf-8
$root = $dom->createElement("offer-mapping-suggestions"); // Создаём корневой элемент
$dom->appendChild($root);

$offers = $dom->createElement("offers"); // Создаём элемент offers
$root->appendChild($offers);
$prices = array(1300);
debug($names);
echo "для выгрузки годно товаров: ".count($names)."<br>";
for ($i = 0; $i < count($names); $i++) {
  $id = $i + 1; // id-пользователя
  $offer = $dom->createElement("offer"); // Создаём элемент offers
  $offers->appendChild($offer);

  $name = $dom->createElement("name", $names[$i]); // Создаём узел "name"
  //$name->setAttribute("id", $id); // Устанавливаем атрибут "id" у узла "user"
  $shop_sku = $dom->createElement("shop-sku", $sku[$i]); // Создаём узел "login" с текстом внутри
  $category = $dom->createElement("category", $categories[$i]); // Создаём узел "login" с текстом внутри
  $vendor = $dom->createElement("vendor", $vendors[$i]); // Создаём узел "vendor" с текстом внутри
  $artikul = $dom->createElement("vendor-code", $vendorCode[$i]); // Создаём узел "vendor-code" с текстом внутри
  //$stranaProizvoditel = $dom->createElement("manufacturer-country", $manufacturer_country[$i]); // Создаём узел "manufacturer_country" с текстом внутри
  $offer->appendChild($name); // Добавляем в узел "user" узел "login"
  $offer->appendChild($shop_sku); // Добавляем в узел "user" узел "sku"
  $offer->appendChild($category);// Добавляем в узел "user" узел "password"
  $offer->appendChild($vendor); // Добавляем в корневой узел "users" узел "user"
  $offer->appendChild($artikul); // Добавляем в корневой узел "users" узел "user"
}

$dom->save("request_1.xml"); // Сохраняем полученный XML-документ в файл
$xml = $dom->saveXML();

$curl = curl_init($url); // создаем экземпляр curl
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
	'Content-Type: application/xml',
	'Accept: application/xml',
	'Authorization: OAuth oauth_token="AQAAAAAC_m26AAe4NilqUTNK7kzQlzLotG4nCbs",oauth_client_id="83a2546b50ef4de2b997470989efa322"'
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
curl_setopt($curl, CURLOPT_VERBOSE, 1);
$result = curl_exec($curl);
curl_close($curl);

echo "ответ ниже<br>";
$document = new SimpleXMLElement($result);
$document -> asXML("response_1.xml");
debug ($document);
if ($document->status == "OK"){
	echo "OK";
	$requestUpdate = new domDocument("1.0", "utf-8"); // Создаём XML-документ версии 1.0 с кодировкой utf-8
	$requestUpdatePrice = new domDocument("1.0", "utf-8"); // Создаём XML-документ версии 1.0 с кодировкой utf-8

	$root = $requestUpdate->createElement("offer-mapping-entries-updates"); // Создаём корневой элемент
	$rootUpdatePrice = $requestUpdatePrice->createElement("offer-prices"); // Создаём корневой элемент

	$requestUpdate->appendChild($root);
	$requestUpdatePrice->appendChild($rootUpdatePrice);

	$offers = $requestUpdate->createElement("offer-mapping-entries"); // Создаём элемент offer-mapping-entries
	$offersUpdatePrice = $requestUpdatePrice->createElement("offers"); // Создаём элемент offers

	$root->appendChild($offers);
	$rootUpdatePrice->appendChild($offersUpdatePrice);

	foreach($document->result->offers->offer as $item){
		$offerUpdatePrice = $requestUpdatePrice->createElement("offer"); // Создаём элемент offerUpdatePrice
		$offersUpdatePrice->appendChild($offerUpdatePrice);
		$atr_sku = $requestUpdatePrice->createAttribute('id');
		$atr_sku->value = (int)$item->{"shop-sku"};
		$offerUpdatePrice->appendChild($atr_sku);

		$priceUpdatePrice = $requestUpdatePrice->createElement("price"); // Создаём элемент priceUpdate
		//$offerUpdatePrice->addAttribute('id', (int)$item->{"shop-sku"});
		$offerUpdatePrice->appendChild($priceUpdatePrice);
		$atr_currency = $requestUpdatePrice->createAttribute('currency-id');
		$atr_currency->value = 'RUR';
		$priceUpdatePrice->appendChild($atr_currency);

		$atr_value = $requestUpdatePrice->createAttribute('value');
		$atr_value->value = $products[(int)$item->{"shop-sku"}]["price"];
		$priceUpdatePrice->appendChild($atr_value);		


		$offerMapping = $requestUpdate->createElement("offer-mapping-entry"); // Создаём элемент offer-mapping-entry
		$offers->appendChild($offerMapping);

		$offer = $requestUpdate->createElement("offer"); // Создаём элемент offer
		$offerMapping->appendChild($offer);

		$name = $requestUpdate->createElement("name", $item->name); // Создаём элемент name
		$offer->appendChild($name);

		$sku = $requestUpdate->createElement("shop-sku", $item->{"shop-sku"}); // Создаём элемент shop-sku
		$offer->appendChild($sku);

		$category = $requestUpdate->createElement("category", $item->{"market-category-name"}); // Создаём элемент shop-sku
		$offer->appendChild($category);
		
		$manufacturer_countries = $requestUpdate->createElement("manufacturer-countries"); // Создаём элемент manufacturer-countries
		$offer->appendChild($manufacturer_countries);
		$manufacturerCountry = $requestUpdate->createElement("manufacturer-country", $manufacturer_country[(int)$item->{"shop-sku"}]); // Создаём элемент manufacturer-country
		$manufacturer_countries->appendChild($manufacturerCountry);

		$weightDimensions = $requestUpdate->createElement("weight-dimensions"); // Создаём элемент weight-dimensions
		$offer->appendChild($weightDimensions);
		$weight = $requestUpdate->createElement("weight", $products[(int)$item->{"shop-sku"}]["weight"]); // Создаём элемент weight
		$weightDimensions->appendChild($weight);

		$length = $requestUpdate->createElement("length", $products[(int)$item->{"shop-sku"}]["length"]); // Создаём элемент length
		$weightDimensions->appendChild($length);
		$width = $requestUpdate->createElement("width", $products[(int)$item->{"shop-sku"}]["width"]); // Создаём элемент width
		$weightDimensions->appendChild($width);
		$height = $requestUpdate->createElement("height", $products[(int)$item->{"shop-sku"}]["height"]); // Создаём элемент height
		$weightDimensions->appendChild($height);

		$urls = $requestUpdate->createElement("urls"); // Создаём элемент urls
		$offer->appendChild($urls);
		$url = $requestUpdate->createElement("url", "https://yugkabel.ru/p/".$item->{"shop-sku"}); // Создаём элемент shop-sku
		$urls->appendChild($url);

		$pictures = $requestUpdate->createElement("pictures"); // Создаём элемент pictures
		$offer->appendChild($pictures);
		$picture = $requestUpdate->createElement("picture", "https://yugkabel.ru/img/".$item->{"shop-sku"}.".jpg"); // Создаём элемент shop-sku
		$pictures->appendChild($picture);

		$vendor = $requestUpdate->createElement("vendor", $item->vendor); // Создаём элемент vendor
		$offer->appendChild($vendor);

		$vendorCode = $requestUpdate->createElement("vendor-code", $item->{"vendor-code"}); // Создаём элемент vendor-code
		$offer->appendChild($vendorCode);

		if($item->{"market-sku"} != ""){
			$mapping = $requestUpdate->createElement("mapping"); // Создаём элемент mapping
			$offerMapping->appendChild($mapping);
			$market_sku = $requestUpdate->createElement("market-sku", $item->{"market-sku"}); // Создаём элемент market-sku
			$mapping->appendChild($market_sku);
		}
		//echo $item->name."<br>";
	}
}else echo "Ошибка в ответе 1";

$requestUpdate->save("request2.xml"); // Сохраняем полученный XML-документ в файл
$requestUpdatePrice->save("priceRequest2.xml"); // Сохраняем полученный XML-документ в файл
$requestXML2 = $requestUpdate->saveXML();
$requestXMLprice = $requestUpdatePrice->saveXML();

// ==============================отправка товаров==============================
$url = "https://api.partner.market.yandex.ru/v2/campaigns/".$campaignId."/offer-mapping-entries/updates.xml";
$curl = curl_init($url); // создаем экземпляр curl
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$headers = array(
	'Content-Type: application/xml',
	'Accept: application/xml',
	'Authorization: OAuth oauth_token="AQAAAAAC_m26AAe4NilqUTNK7kzQlzLotG4nCbs",oauth_client_id="83a2546b50ef4de2b997470989efa322"'
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, $requestXML2);
curl_setopt($curl, CURLOPT_VERBOSE, 1); 
$result = curl_exec($curl);
curl_close($curl);
// ==============================отправка цен==============================
$url = "https://api.partner.market.yandex.ru/v2/campaigns/".$campaignId."/offer-prices/updates.xml";
$curl = curl_init($url); // создаем экземпляр curl
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$headers = array(
	'Content-Type: application/xml',
	'Accept: application/xml',
	'Authorization: OAuth oauth_token="AQAAAAAC_m26AAe4NilqUTNK7kzQlzLotG4nCbs",oauth_client_id="83a2546b50ef4de2b997470989efa322"'
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, $requestXMLprice);
curl_setopt($curl, CURLOPT_VERBOSE, 1); 
$resultPrice = curl_exec($curl);
curl_close($curl);

echo "ответPrice ниже:<br>";
$document = new SimpleXMLElement($resultPrice);
$document -> asXML("responsePrice.xml");
debug($resultPrice);

echo "ответ2 ниже:<br>";
debug($result);
$document = new SimpleXMLElement($result);
$document -> asXML("response2.xml");
//debug ($manufacturer_country);
//debug($products);


function getItemsBySectionId($sectionId){
	global $names, $ar, $categories, $vendors, $vendorCode, $sku, $manufacturer_country, $products;
	if($sectionId == 1231) $kit = 50; //кабель, разбиваем по 50 м
	else	$kit = 0;
	
	$arFilter = Array(
		"LOGIC" => "AND",
		"IBLOCK_ID" => 4,
		"ACTIVE"=> "Y",
		//array("LOGIC" => "OR", array("NAME" => "Кабель%"), "NAME" => "Провод%",),
		
		//"PROPERTY_EXISTS_AT_STORES" => 78621,  //78621 склад российская
		"SECTION_ID"=>$sectionId,
		"INCLUDE_SUBSECTIONS" => "Y",
		">CATALOG_STORE_AMOUNT_10" => $kit, //10 склад Российская
		//"PROPERTY_PROIZVODITEL_FILTER" => "ИЭК", //только ИЭК
	);
	if($sectionId == 1231) $arFilter[] = array("LOGIC" => "OR", array("NAME" => "Кабель%"), "NAME" => "Провод%");
  
	echo "<br><br>";
	$res =  CIBlockElement::GetList(Array(), $arFilter, false, false, Array("ID", "IBLOCK_ID","XML_ID","NAME","PROPERTY_CENAZ","PROPERTY_ARTIKUL", "PROPERTY_PROIZVODITEL_FILTER", "PROPERTY_STRANAPROIZVODITEL",
	 "PROPERTY_VES", "PROPERTY_SHIRINA", "PROPERTY_VYSOTA", "PROPERTY_DLINA"));
	echo "Грязное количество найденых элементов в группе ".$sectionId.": ".$res->result->num_rows. "<br>";

	$y = 0; $products = array();
	while($ar = $res->GetNext()) {
		$textName = "";
		$y++;
		//if($y > 10) break;
		//if($y > 40) break;
	  	if($sectionId == 1231){ // кабель
			if($ar["PROPERTY_CENAZ_VALUE"] < (350/50)) continue;
			$amount = CCatalogStoreProduct::GetList(
				array(),
				array("PRODUCT_ID" => $ar["ID"], "STORE_ID" => 10),
				false,
				false,
				array("AMOUNT")
			)->Fetch();
		  	//$amount = " Количество бухт: ".floor($amount["AMOUNT"] / 50);
		  	//$text = " за 50 метров. ".$amount;
			$text = setKit(50, true);
			$textName = " бухта 50 м";
		  	$price = round($ar["PROPERTY_CENAZ_VALUE"] * 50 * 1.6, 2, PHP_ROUND_HALF_UP);
	  	}
	  	elseif($sectionId == 1342 || $sectionId == 1849){ // если лампы и розетки
			if($ar["PROPERTY_CENAZ_VALUE"] < 35) continue;
			elseif($ar["PROPERTY_CENAZ_VALUE"] >= 350) {
		  		$text = setKit(1);
				
		  		if(!$text) continue;
			}
			elseif($ar["PROPERTY_CENAZ_VALUE"] >= 70 && $ar["PROPERTY_CENAZ_VALUE"] < 350){
		  		$text = setKit(5);
				$textName = " набор из 5 шт";
		  		if(!$text) continue;
			}
			elseif($ar["PROPERTY_CENAZ_VALUE"] >= 35 && $ar["PROPERTY_CENAZ_VALUE"] < 70){
		  		$text = setKit(10);
				  $textName = " набор из 10 шт";
		  		if(!$text) continue;
			}
	  	}
	  	elseif($sectionId == 1435 || $sectionId == 1579 || $sectionId == 1716 || $sectionId == 2183 || $sectionId == 2192){
			if($ar["PROPERTY_CENAZ_VALUE"] < 350) continue;
			else{
		  		$text = setKit(1);
		  		if(!$text) continue;
			}
	  	}
	  	elseif($sectionId == 2102){ // если модульное оборудование
			if($ar["PROPERTY_CENAZ_VALUE"] < 70) continue;
			elseif($ar["PROPERTY_CENAZ_VALUE"] >= 350) {
		  		$text = setKit(1);
		  		if(!$text) continue;
			}
  			elseif($ar["PROPERTY_CENAZ_VALUE"] >= 115 && $ar["PROPERTY_CENAZ_VALUE"] < 350){
		  		$text = setKit(3);
				$textName = " набор из 3 шт";
		  		if(!$text) continue;
			}
		
			elseif($ar["PROPERTY_CENAZ_VALUE"] >= 70 && $ar["PROPERTY_CENAZ_VALUE"] < 115){
		  		$text = setKit(5);
		  		if(!$text) continue;
			}      
	  	}
	  	else $text = "";
	  	$ar["NAME"] = str_replace(" ФИНАЛЬНАЯ РАСПРОДАЖА", "", $ar["NAME"]);
	  	if($ar["PROPERTY_ARTIKUL_VALUE"]) $productName = str_replace($ar["PROPERTY_ARTIKUL_VALUE"], "", $ar["NAME"]);
	  	else $productName = $ar["NAME"];
	  	//echo "<a href=/p/".$ar["XML_ID"].">".$productName."</a> ".$text."<br>";

		  $products[(int)$ar["XML_ID"]] = $text;
		  $names[] = $productName.$textName;

	  	//if($sectionId == 1231) debug($ar);
	  	//if($sectionId != 2102) break;
		  $db_old_groups = CIBlockElement::GetElementGroups($ar["ID"], true)->Fetch();
		  $categories[] = $db_old_groups["NAME"];
		  //echo $ar["PROPERTY_PROIZVODITEL_FILTER_VALUE"];
		  $vendors[] = $ar["PROPERTY_PROIZVODITEL_FILTER_VALUE"];
		  $vendorCode[] = $ar["PROPERTY_ARTIKUL_VALUE"];
		  $sku[] = $ar["XML_ID"];
		  $manufacturer_country[(int)$ar["XML_ID"]] = $ar["PROPERTY_STRANAPROIZVODITEL_VALUE"];
	}
}

function setKit($kit, $is_kabel = false){
	global $ar, $tmpFile, $errorFile;
	//file_put_contents($tmpFile, date("d.m.Y H:i:s")." ...\n" , FILE_APPEND);
	//debug($ar);
	$num = "";
	$amount = CCatalogStoreProduct::GetList(
	  array(),
	  array("PRODUCT_ID" => $ar["ID"], "STORE_ID" => 10),
	  false,
	  false,
	  array("AMOUNT")
	)->Fetch();
   
	if($amount["AMOUNT"] < $kit) return false;

	$amount = floor($amount["AMOUNT"] / $kit);
	if($kit > 1) $amountText = " Количество доступных наборов: ".$amount;
	if($kit == 1) $amountText = " Доступное количество: ".$amount;
	if($kit > 1){
		$text = " за набор из ".$kit." штук. ".$amountText;
		$num = " набор из ".$kit." шт.";
		if($is_kabel) $num = " бухта ".$kit." м.";
	}
	if($kit == 1)  $text = " за штуку. ".$amountText;
	$price = round($ar["PROPERTY_CENAZ_VALUE"] * $kit * 1.6, 2, PHP_ROUND_HALF_UP);
	$errorText = "";
	if($price == "" || $price <= 0) $errorText .= "нет цены ";// return false;
	//$weight = $ar["PROPERTY_VES_2_VALUE"];
	$weight = str_replace(",", ".", $ar["PROPERTY_VES_VALUE"]);
	if($is_kabel) $weight = 0.1;
	if($weight == "" || $weight <= 0) $errorText .= "нет веса "; // return false;
	$weight *= $kit;
	$length = (int)$ar["PROPERTY_DLINA_VALUE"]/10;
	if($is_kabel) $length = 40;
	if($length == "" || $length <= 0) $errorText .= "нет длины ";//return false;
	$width = (int)$ar["PROPERTY_SHIRINA_VALUE"]/10;
	if($is_kabel) $width = 40;
	if($width == "" || $width <= 0) $errorText .= "нет ширины ";//return false;
	$height = (int)$ar["PROPERTY_VYSOTA_VALUE"]/10;
	if($is_kabel) $height = 40;
	if($height == "" || $height <= 0) $errorText .= "нет высоты ";//return false;
	if($errorText != ""){
		$errorText .= "у товара ".$ar["XML_ID"]." ".$ar["NAME"];
		file_put_contents($errorFile, date("d.m.Y H:i:s")." ".$errorText."\n" , FILE_APPEND);
		return false;
	}
	//echo "weight = ".$weight;
	//$weight = $ar["PROPERTY_VES_2_VALUE"] * $kit;
	$text = $price." руб.".$text;
	$result =Array(
		"price" => $price, // цена
		"num" => $num, // количество штук в наборе
		"amount" => $amount, // актуальные остатки
		"weight" => $weight, // вес набора
		"length" => $length, // длина
		"width" => $width, // ширина
		"height" => $height, // высота
	);
	file_put_contents($tmpFile, date("d.m.Y H:i:s")." ".print_r($result, true)."\n" , FILE_APPEND);
	return $result;
}
?>