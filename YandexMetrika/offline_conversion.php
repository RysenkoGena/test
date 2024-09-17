<?PHP
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$id = "578189455";
$counter = "135067";                                                    // Укажите номер счетчика
$token = "y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4";  // Укажите OAuth-токен

$curl = curl_init("https://api-metrika.yandex.ru/management/v1/counter/$counter/offline_conversions/uploading/".$id);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $token"));


$result = curl_exec($curl);

d($result);

curl_close($curl);
