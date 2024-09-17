<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo "<h1>Загрузка изображений  DKC</h1><br>\r\n";
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
echo "Количество товаров ".$vendor.": ".$list->SelectedRowsCount()."\n";
$token = autorize();
$i=0;
// Код материала ДКС 4405103

While($obEl = $list->GetNext()){
 	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));
    if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
}
$count = count($artikuls); $n=0;
foreach($artikuls as $xml_id => $artikul){
	$n++;
	echo "Товар: <a href=/p/".$xml_id." target='_blank'>".$xml_id."</a> Артикул: ".$artikul."<br>";
 	$certs = load($artikul, $token);
	$text = ""; $j=0;
	foreach ($certs as $cert){
		//if($cert->type == "thumbnail_url" || true){
			echo "ссылка <a href='".$cert->thumbnail_url."'>".$cert->thumbnail_url."</a><br>";
			echo basename($cert->thumbnail_url);
			//if(!file_exists("/mnt/1s.s/images/lib/".basename($cert->thumbnail_url))){
			//if(!file_exists("/mnt/1s.s/images/".$xml_id.".jpg")){
			if(!file_exists("/var/newImages/".$vendor."/".$xml_id.".jpg")){
				//if (copy($cert->thumbnail_url, "/mnt/1s.s/images/lib/".basename($cert->thumbnail_url))) {
				//if (copy($cert->thumbnail_url, "/mnt/1s.s/images/".$xml_id.".jpg")) {
				if (copy($cert->thumbnail_url, $xml_id.".jpg")) {
					echo "\n\rФайл успешно загружен на сервер";
				}
				else echo "\n\rКакая-то ошибкa";
			}
		//}
		$k = 0;
		foreach ($cert->additional_images as $additional_images){
			$k++;
			if(!file_exists("/var/newImages/".$vendor."/".$xml_id."_".$k.".jpg")){
				if (copy($additional_images, $xml_id."_".$k.".jpg"))	echo "\n\rФайл успешно загружен на сервер\n";
			}
			echo $additional_images."\n";
		}
		$text .= basename($cert->src)."\n";
		echo $cert->type."<br>";
		$j++;
		if($j > 50) {
			$text="";
			break;
		}
	}
	echo "Количество дополнительных картинок: ". $k."\n";
	echo "\n".$n." из ".$count."\n";
	//if($text != "") file_put_contents("/mnt/1s.s/certificates/txt/".$xml_id.".txt", $text);
	//else unlink("/mnt/1s.s/certificates/txt/".$xml_id.".txt");
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
	$serv_page = '/catalog/material/?code=';
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