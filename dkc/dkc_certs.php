<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo "<h1>Загрузка сертификатов DKC</h1><br>\r\n";
//Авторизация в системе (получение токена по мастер ключу https://api.dkc.ru/v1/auth.access.token/5c87e5296fa0b626f8f123f00b0b987c)
ob_end_flush(); //отключить буферизацию
$vendor = "ДКС";
$arrFilter = Array(
	"PROPERTY_proizvoditel_filter"=>$vendor,
	"ACTIVE"=>"Y",
	//"XML_ID"=>"10217"
);
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
echo "Количество товаров ".$vendor.": ".$list->SelectedRowsCount()."\n";
$token = autorize();
$i=0;
// Код материала ДКС 4405103

While($obEl = $list->GetNext()){
 	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));
    if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
}
foreach($artikuls as $xml_id => $artikul){
	echo "Товар: <a href=/p/".$xml_id." target='_blank'>".$xml_id."</a> Артикул: ".$artikul."<br>";
 	$certs = load($artikul, $token);
	$text = ""; $j=0;
	foreach ($certs as $cert){
		if($cert->type == "Сертификат соответствия" || true){
			echo "ссылка <a href='".$cert->src."'>".$cert->src."</a><br>";
			echo basename($cert->src);
			if(!file_exists("/mnt/1s.s/certificates/lib/".basename($cert->src))){
				if (copy($cert->src, "/mnt/1s.s/certificates/lib/".basename($cert->src))) {
					echo "\n\rФайл успешно загружен на сервер";
				}
				else echo "\n\rКакая-то ошибкa";
			}
		}
		$text .= basename($cert->src)."\n";
		echo $cert->type."<br>";
		$j++;
		if($j > 50) {
			$text="";
			break;
		}
	}
	echo "Количество документов: ". $j;
	if($text != "") file_put_contents("/mnt/1s.s/certificates/txt/".$xml_id.".txt", $text);
	else unlink("/mnt/1s.s/certificates/txt/".$xml_id.".txt");
	echo "<br><hr>";
 	$i++;
	//if($i>10) break; //включить ограничение на количество товаров (полезно для тестирования)
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
	//echo "token=".$token;
	$method="GET";                                          						// "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://api.dkc.ru/v1';                    // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = '/catalog/material/certificates?code=';
	$request = $serv_addr.$serv_page.$artikul;
	echo $request."<br>";
	$ch = curl_init($request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessToken: '.$token, 'accept: application/json']);
	$response = curl_exec($ch);
	curl_close($ch);
	return json_decode($response);
}
?>