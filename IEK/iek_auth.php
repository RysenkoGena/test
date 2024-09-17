<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
ob_end_flush(); //отключить буферизацию
$vendor = "ИЭК";

$username='169-20180718-113601-229';
$password=':o8IaX_1a0;D8Gh;';
$URL='https://lk.iek.ru/api/residues/json/?sku=SVA30-3-0250';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$URL);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
$result=curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
curl_close ($ch);

$text = json_decode($result);
var_dump($text);
$she = $text->shopItems[0]->residues->{"47585e53-0113-11e0-8255-003048d2334c"};
$che = $text->shopItems[0]->residues->{"238cf439-2a81-11ec-a958-00155d04ac08"};
$ya =  $text->shopItems[0]->residues->{"aeef2063-c1e7-11d9-b0d7-00001a1a02c3"};


echo ($she + $che + $ya).PHP_EOL;
echo $status_code.PHP_EOL;
?>
