<?php
/*$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию*/
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<?
//$document = new Document('https://www.sports.ru/football/club/zenit/calendar/', true);


include_once "simple_html_dom.php";
$url = "https://www.sports.ru/football/club/zenit/calendar/";
echo "<br>".$url,"<br>";

//print_r($_SERVER);
function curlGetPage($url, $referer = "https://google.com"){
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

$page = curlGetPage("mail.ru");

$html = str_get_html($page);

var_dump($html);
foreach ($html->find('.matches-img')  as $item){
    echo $item;
}


//$file = file_get_contents($url);
//echo $file;

/*
$pq = phpQuery::newDocument($file);

print_r($pq);*/

?>
</body>
</html>