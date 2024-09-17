<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//$token = "y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4";              // Укажите OAuth-токен
echo "123";
$ch = curl_init('https://login.yandex.ru/info');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array('format' => 'json'));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$info = curl_exec($ch);
curl_close($ch);

$info = json_decode($info, true);
print_r($info);


echo "123";

