<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//Авторизация в системе (получение токена по мастер ключу https://api.dkc.ru/v1/auth.access.token/5c87e5296fa0b626f8f123f00b0b987c)
ob_end_flush(); //отключить буферизацию
$vendor = "ДКС";
$arrFilter = Array(
	"PROPERTY_proizvoditel_filter"=>$vendor,
	"ACTIVE"=>"Y",
	//"XML_ID"=>"2667"
);
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
echo "Загрузка остатков на складах подключения у поставщика ".$vendor.": ".$list->SelectedRowsCount()."\n";
$token = autorize();
$i=0;
// Код материала ДКС 4405103
$ids = array();
While($obEl = $list->GetNext()){
 	$ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext();
    $artikuls[$obEl["ID"]] = $ob['VALUE'];
	$ids["ID"][$ob['VALUE']] = $obEl["ID"];
	$ids["XML_ID"][$ob['VALUE']] = $obEl["XML_ID"];
}

$counts = array(); $count = count($artikuls); $n=0; $j=0;
$countItems = count($artikuls);
foreach($artikuls as $id => $artikul){
	$j++;
	if($n == 0) $text = $artikul;
	else $text .= (",".$artikul);
	$n++;
	if($n >= 100 || $j == $countItems) {
		$n = 0;
		$stocks = load($text, $token);
        echo print_r($stock, 1);
		$stocks = $stocks->materials;//->warehouse;
		//var_dump($stocks);
		foreach($stocks as $stock){
			//var_dump($stock);
			$artukulItem = $stock->code;
			$count = 0;
			foreach($stock->warehouse as $warehouse){
				$count += $warehouse->amount;
			}
			$counts[$stock->code] = $count;
		}
	}
}
//print_r($counts);
foreach($counts as $key => $external_count){
    $ii++;
    if ($external_count >= 0 && CIBlockElement::SetPropertyValueCode($ids["ID"][$key], "vneshniy_sklad", $external_count)) echo "Изменен товар: ".$ids["XML_ID"][$key]." ".$ids["ID"][$key]." ".$external_count." шт.<br>".PHP_EOL;
    //if($ii > 10) break;
}

echo count($counts)." товаров обработано.";

function autorize(){
	$method="GET";                                                  // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://api.dkc.ru:443/v1/auth.access.token/';    // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = '5c87e5296fa0b626f8f123f00b0b987c';
	$request = $serv_addr.$serv_page;
	//echo "<a href='$request'>".$request."</a><BR>";
	$ch = curl_init($request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$obj=json_decode($response);
	$etim=$obj->access_token;
	return $etim;
}

function load($artikul, $token){
	//echo "token=".$token;
	$method="GET";                                          // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://api.dkc.ru/v1';                   // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = '/catalog/material/stock?code=';
	$request = $serv_addr.$serv_page.$artikul;
	echo $request.PHP_EOL;
	$ch = curl_init($request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessToken: '.$token, 'accept: application/json']);
	$response = curl_exec($ch);
	curl_close($ch);
	return json_decode($response);
}
?>