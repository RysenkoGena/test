<?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); 

$text = file_get_contents("queue.txt");
$strings = file("queue.txt");
//print_r($strings);
//echo "количество строк: ".count($strings)."<br>";
$date = explode("|", $strings[0]);

$numIncoming = 0; $events = array(); $otvetil = array(); $abandons = array(); $clientNumber = array(); $timeEvents = array();
$waiting = array(); //время ожидания ответа клиенту
foreach($strings as $string){
    $items = explode("|", $string);
    if($items[2] == 8600){
        //echo "<br>строка ".$items[2];
        if( $items[4] == "ENTERQUEUE"){
            $events[] = $items[1];
            $clientNumber[$items[1]] = $items[6];
            $timeEvents[$items[1]] = $items[0];

            //echo date("H:i:s",$items[0])." входящий с ".$items[6]."<br>";
            $numIncoming++;
        }
        elseif($items[4] == "CONNECT"){
            $events[$items[2]]["CONNECT"] = $items[3];
            $otvetil[$items[3]][] = $items[1];
        }
        elseif($items[4] == "ABANDON"){
           $abandons[] =  $items[1];
           $waiting[$items[1]] = $items[7];
        }
    }

}

echo "<h1>ОТЧЕТ по звонкам на 8-800-700-74-00 за ".date("d.m.Y",$date[0])."<br><br></h1>";
echo "<b>Всего поступило звонков: </b>".$numIncoming."<br><br>";
echo "<b>Отвеченных: </b>". ($numIncoming - count($abandons))."<br>";
foreach($otvetil as $agent => $event) echo $agent.": ".count($event)."<br>";
echo "<br><b>Не дождались ответа: </b>".count($abandons)."<br>";
foreach($abandons as $abandon) echo date("H:i:s", $timeEvents[$abandon])." ".$clientNumber[$abandon]." ждал ответа ".$waiting[$abandon]." секунд<br>";
//debug($otvetil);

//debug ($strings);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>