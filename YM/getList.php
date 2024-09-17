<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
$names = array(); $categories =  array(); $vendors = array(); $vendorCode = array(); $sku = array(); $manufacturer_country = array();
$url = "https://api.partner.market.yandex.ru/v2/campaigns/135067/offer-mapping-entries.xml?page_token=eyJvcCI6Ij4iLCJrZXkiOiI4ODIwMiIsInNraXAiOjB9";
echo $url;

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

echo "<br>ответ ниже:<br>";
$document = new SimpleXMLElement($result);
$document -> asXML("responseList.xml");
debug ($document);

?>