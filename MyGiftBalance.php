<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?CModule::includeModule("sale");


$data = ["partnerName" => "ooo_yugkabel", "partnerPassword" => "RrVxJRJvT6Tv1Pmsp8b9D9BsfMMyNdD7"]; //запрос баланса prod
$data = ["partnerName" => "ooo_yugkabel", "partnerPassword" => "MEy55cYz9izIZ5Ic1UUDFRJmyzinm3HG"]; //запрос баланса prod
/*$data = ["requestId" => "100003", "partnerName" => "ooo_yugkabel", "partnerPassword" => "RrVxJRJvT6Tv1Pmsp8b9D9BsfMMyNdD7",
    "phoneNumber" => "9180763825",
    "sum" => 100,
    "offerAccepted" => "true",
    "productType" => "VIRTUAL_MASTER_CARD_MY_GIFT_3"
];*/ //запрос баланса test
/*$data = ["requestId" => "100004", "partnerName" => "ooo_yugkabel", "partnerPassword" => "MEy55cYz9izIZ5Ic1UUDFRJmyzinm3HG",
    "phoneNumber" => "9180763825",
    "sum" => 100,
    "offerAccepted" => "true",
    "productType" => "VIRTUAL_MASTER_CARD_MY_GIFT_6"
]; */// prod
//$data = [	"requestId" => "some_unique_request_id",	"walletId" => "ZmM2NmYxZjAtNDg2NS00MDlmLTgzZDMtYWY5ZGZjMWNiYjEy"];
	
//$data = ["partnerName" => "ooo_yugkabel", "partnerPassword" => "RrVxJRJvT6Tv1Pmsp8b9D9BsfMMyNdD7"];

$data_string = json_encode ($data, JSON_UNESCAPED_UNICODE);
//debug($data_string);
//$curl = curl_init('http://repeater.d4.dclouds.ru/partner/get_balance/');
$curl = curl_init('https://api.prostodar.ru/partner/get_balance/');
//$curl = curl_init('http://repeater.d4.dclouds.ru/create/');
//$curl = curl_init('http://api.prostodar.ru/partner/create/');
//$curl = curl_init('http://repeater.d4.dclouds.ru/partner/create/');
//$curl = curl_init('http://repeater.d4.dclouds.ru/partner/create/');

curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
// Принимаем в виде массива. (false - в виде объекта)
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
   'Content-Type: application/json',
   'Content-Length: ' . strlen($data_string))
);
//debug($curl);
$result = curl_exec($curl);
curl_close($curl);

print_r($result);



/*function load($artikul){
	$method="POST";                                                 // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'http://api.prostodar.ru/';                      // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = 'partner/get_balance/';
	$request = $serv_addr.$serv_page.$artikul;
  
  	//$server_answer = file_get_contents('https://web.se-ecatalog.ru/new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=IMT35090');
  	$server_answer = file_get_contents($request);
	$obj=json_decode($server_answer);
	$etim=$obj->data[0]->certificates;
	return $etim;
}*/




?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>



