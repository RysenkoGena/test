<?PHP
//$pathToDir = "/home/bitrix/ext_www/yugkabel.ru/test/hiveos/";
//$pathToDir = __DIR__$pathToDir;
$strings = file("/var/log/hive-agent.log", FILE_IGNORE_NEW_LINES);
//if(isset($_GET["file"])) $strings = file($pathToDir.$_GET["file"]);
$count = count($strings) - 1;
if(strpos($strings[$count], ">")) $string = explode(">", $strings[$count]);
elseif(strpos($strings[$count - 1], ">"))$string = explode(">", $strings[$count - 1]);
elseif(strpos($strings[$count - 2], ">"))$string = explode(">", $strings[$count - 2]);
else die;
$date = substr($string[0], 1, strlen($string[0]) - 3);
//echo $date;
$json = substr($string[1], 1);


//s$json = $string[2];
print_r(json_decode($json, true));


$url = "https://yugkabel.ru/test/hiveos/index.php";
$curl = curl_init($url); // СЃРѕР·РґР°РµРј СЌРєР·РµРјРїР»СЏСЂ curl
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$headers = array(
'Content-Type: application/json',
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
curl_setopt($curl, CURLOPT_VERBOSE, 1);
$resultPrice = curl_exec($curl);
curl_close($curl);

echo $resultPrice;
