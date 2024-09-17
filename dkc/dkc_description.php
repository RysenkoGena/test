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
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "DETAIL_TEXT"));
echo "Количество товаров ".$vendor.": ".$list->SelectedRowsCount()."\n";
$token = autorize();
$i=0; $ids = array();
// Код материала ДКС 4405103

While($obEl = $list->GetNext()){
	if($obEl["DETAIL_TEXT"] != "") continue;
 	$ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext();
    $artikuls[$obEl["ID"]] = $ob['VALUE'];
	$ids[$obEl["ID"]] = $obEl["XML_ID"];
}
$count = count($artikuls); $n=0;
echo "Количество товаров ".$vendor." без описания: ".$count."\n";

foreach($artikuls as $id => $artikul){
	$n++;
	$description = load($artikul, $token);
	//echo $description.PHP_EOL;
	if($description == "Не найден запрашиваемый код материала." ){
		echo ($count - $i)." Товар ".$ids[$id]. " -> Не наден такой артикул у ДКС".PHP_EOL.PHP_EOL;
		continue;
	}
	if($description != "" ){
		$el = new CIBlockElement;
		$res_ = $el->Update($id, Array("DETAIL_TEXT"=>$description));
		echo ($count - $i)." Товар ".$ids[$id]. " -> ". $description.PHP_EOL.PHP_EOL;
	}
	else echo ($count - $i)." Товар ".$ids[$id]. " -> Нет описания у производителя".PHP_EOL.PHP_EOL;
	$i++;
	//if($i>10) break; //включить ограничение на количество товаров (полезно для тестирования)*/
}

echo $i. " товаров обработано.";

function autorize(){
	$method="GET";                                          // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://api.dkc.ru:443/v1/auth.access.token/';                      // ip адрес или доменное имя сервера, куда шлем данные
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
	echo "Артикул ".$artikul.PHP_EOL;
	//echo "token=".$token;
	$method="GET";                                          // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://api.dkc.ru/v1';                   // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = '/catalog/material/description?code=';
	$request = $serv_addr.$serv_page.$artikul;
	//echo $request."<br>";
	$ch = curl_init($request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessToken: '.$token, 'accept: application/json']);
	$response = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($response);
	print_r($result);
	if($result->message == "Не найден запрашиваемый код материала.") return $result->message;
	//echo $result->description->$artikul[0];
	return $result->description->$artikul[0];
}
?>