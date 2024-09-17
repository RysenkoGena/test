<?php
$_SERVER["DOCUMENT_ROOT"] = __DIR__."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$counter = "135067";            // Укажите номер счетчика
$token = "y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4";              // Укажите OAuth-токен

$url = "https://api-metrika.yandex.ru/management/v1/counter/$counter/user_params/uploadings";
echo $url."<br>";
$curl = curl_init($url);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $token"));

//sage($curl);
//var_dump($curl);
//var_dump($curl);
//var_dump(get_class_methods($curl));

$result = curl_exec($curl);

d ($result);
//echo $result;

curl_close($curl);




