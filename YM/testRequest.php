<?
//echo "Начало запроса!";

$requestJSONprice = '{
    "warehouseId": 2,
    "skus":
    [
      "70975",
      "55558",
      "73617"
    ]
  }';

$url = "https://yugkabel.ru/market/api/stocks/?auth-token=2D000001F47F58B3";
//$url = "https://yugkabel.ru:443/market/api/stocks";
//$url = "https://mail.ru";
$curl = curl_init($url); // создаем экземпляр curl
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$headers = array(
	'Content-Type: application/json',
	//'Authorization: OAuth oauth_token="AQAAAAAC_m26AAe4NilqUTNK7kzQlzLotG4nCbs",oauth_client_id="83a2546b50ef4de2b997470989efa322"'
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, $requestJSONprice);
curl_setopt($curl, CURLOPT_VERBOSE, 1); 
$resultPrice = curl_exec($curl);
curl_close($curl);

echo $resultPrice;