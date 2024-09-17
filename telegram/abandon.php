<?php
$queues["8600"] = "8-800-7007400";
$queues["8606"] = "8-800-7007400";
$queues["8607"] = "8-800-7007400";
$queues["8608"] = "8-800-7007400";
$queues["8609"] = "8-800-7007400";
$queues["8610"] = "8-800-7007400";
$queues["8601"] = "200101 Элтэк";
$queues["8602"] = "200202 Онежская";
$queues["8603"] = "2008110 Запад";
$queues["8604"] = "2008111 Российская";
$queues["8605"] = "2008112 Интернет Магазин";
$queues["555"] = "555 Сухарев Г.";
$queues["597"] = "597 Российская 252";

$dezhurstvo = [
    1 => "Онежская",
    2 => "Дзержинского",
    3 => "Российская",
    0 => "КП",
];


$today = date("d.m.Y");
$pathToDir = __DIR__."/../../../asterisk/";
$dir = opendir($pathToDir);
echo $pathToDir.PHP_EOL;
if($dir) {
    $strs = [];
    while ($el = readdir($dir)) {
        if(date("d.m.Y", filemtime($pathToDir . $el)) != $today) continue;
        if ($el == "SIP_Error.log") {
            unlink($pathToDir . 'SIP_Error.log');
            sendTMessage("SIP ERROR");
        }
        if (strpos($el, "queue_log") === false) continue;
        $strs = array_merge($strs, file($pathToDir.$el));
    }
}
else  echo "ошибка открытия каталога ".$pathToDir.PHP_EOL;

foreach ($strs as $str){
    $unixData =  explode("|", $str)[0];
    if(date("d.m.Y",$unixData) != $today) continue;
    $strings[] = $str;  //получаем только сегодняшние строки
}
print_r($strings);
echo "Всего строк: ".count($strings).PHP_EOL;
$numIncoming = 0; $otvetil = []; $abandons = []; $line = []; $clientNumber = []; $timeEvents = [];
$waiting = array(); //время ожидания ответа клиенту
$operators = array(); //массив сотрудников
$duration = array(); // длительность разговора
foreach($strings as $string){
    $items = explode("|", $string);
    //if($items[2] == $queue){
    $line[$items[1]] = $queues[$items[2]]; // определили линию звонка (очередь)
    //print_r($line);
    $queue = $items[2];
        if( $items[4] == "ENTERQUEUE"){
            $clientNumber[$items[1]] = $items[6];
            $timeEvents[$items[1]] = $items[0];
            $numIncoming++;
        }
       elseif($items[4] == "CONNECT"){
            $otvetil[$items[3]][] = $items[1];
            $waiting[$items[1]] = $items[5];
            if(!array_key_exists($items[3], $operators)) $operators[$items[3]] = 0;
            $operators[$items[3]]++;
            //$clientNumber[$items[1]] = $items[6];
        }
        elseif($items[4] == "ABANDON"){
            echo "ABANDON!".PHP_EOL.$items[1].PHP_EOL;
            //if($items[7] < 6) continue; //не учитывать спам звонки
            $abandons[] =  $items[1];
            $waiting[$items[1]] = trim($items[7]);
        }
        elseif($items[4] == "COMPLETECALLER" || $items[4] == "COMPLETEAGENT"){
            $duration[$items[1]] = $items[6];
        }
    //}
}
    $arrAbandos = file(__DIR__ . '/abandos.txt',FILE_IGNORE_NEW_LINES);
    //debug($arrAbandos);
    foreach($abandons as $abandon){
        echo date("H:i:s", $timeEvents[$abandon])." ".$clientNumber[$abandon]." ждал ответа ".trim($waiting[$abandon])." сек.".PHP_EOL;
        if(!in_array($abandon,$arrAbandos)){ // проверяем не появился ли новый пропущеный звонок
            file_put_contents(__DIR__ . '/abandos.txt', $abandon.PHP_EOL, FILE_APPEND);
            echo "есть пропущенный!! ".$line[$abandon];
            if($waiting[$abandon] > 1) {
                if (strlen($clientNumber[$abandon]) == 11 && substr($clientNumber[$abandon], 0, 1) == "8")
                    $clientNumber[$abandon] = "+7" . substr($clientNumber[$abandon], 1, 10); // преобразовать номер звонившего из 89184455667 в +79184455667
                if (strlen($clientNumber[$abandon]) == 13 && substr($clientNumber[$abandon], 0, 2) == "00")
                    $clientNumber[$abandon] = "+7" . substr($clientNumber[$abandon], 3, 10); // преобразовать номер звонившего из 89184455667 в +79184455667
                $text = "Пропущен звонок на номер " . $line[$abandon] . PHP_EOL . date("H:i:s", $timeEvents[$abandon]) . " " . $clientNumber[$abandon] . " ждал ответа " . $waiting[$abandon] . " сек.\n";
                $text .= "Дежурный филиал: ".$dezhurstvo[date("W")%4];
                sendTMessage($text, $queue);
            }
        }
    }
    //debug($abandons);
//file_put_contents(__DIR__ . '/abandosLogs.txt', date("Y-m-d H:i:s ")." Cron! ".PHP_EOL, FILE_APPEND);

    function sendTMessage($text, $queue = ""){
        file_put_contents(__DIR__ . '/abandosLogs.txt', date("Y-m-d H:i:s ")." Отправка сообщения ".print_r($queue,1)." ".print_r($text, 1).PHP_EOL, FILE_APPEND);
        echo "Отправка сообщения ". $text. " ". $queue;
        if(strpos($text, "RROR")){ //ищем ERROR
            $chats = array(
                'Gena' => 1569407398,
            );
        }elseif($queue == 8601){ //Элтэк
            $chats = array(
                'Gena' => 1569407398,
                //'Lobas' => 903296204,
                'Peshkov' => 1057631050,
                'Eliseev' => 5292809379,
                'Yaroslav' => 1224740956,
            );
        }elseif($queue == 8602){ // Онежская
            $chats = array(
                'Gena' => 1569407398,
                //'Lobas' => 903296204,
                'Peshkov' => 1057631050,
                'Eldin' => 594159557,
                'Zaur' => 678579656,
                'Yaroslav' => 1224740956,
            );
        }elseif($queue == 8603){ // Запад
            $chats = array(
                'Gena' => 1569407398,
                //'Lobas' => 903296204,
                'Peshkov' => 1057631050,
                'Eldin' => 594159557,
                'Kolodochka' => 1299027654,
                'Yaroslav' => 1224740956,
                'Zaur' => 678579656,
                'Zapad' => 6837636583,
            );
        }elseif($queue == 8604 || $queue == 597){ // Российская
            $chats = array(
                'Gena' => 1569407398,
                //'Lobas' => 903296204,
                'Peshkov' => 1057631050,
                'Savelyev' => 1021482259,
                'Yaroslav' => 1224740956,
            );
        }elseif($queue == 8605){ // Интернет Магазин
            $chats = array(
                'Gena' => 1569407398,
                //'Lobas' => 903296204,
                'Peshkov' => 1057631050,
                'Kolodochka' => 1299027654,
                'Zaur' => 678579656,
                'Yaroslav' => 1224740956,
            );
        }else {
            $chats = array(
                'Gena' => 1569407398,
                //'Lobas' => 903296204,
                'Peshkov' => 1057631050,
                'Zaur' => 678579656,
                'Savelyev' => 1021482259,
                //'Dron123' => 1049467010, // Пономарев
                'Kolodochka' => 1299027654, //Колодочка
                'Eldin' => 594159557,
                'Ochichenko' => 1242037573,
                'Yaroslav' => 1224740956,
                //'Petrovich755' => 1117429075 // Скоробогатский
                //'Vanzha' => 854742634,
                //'Potseluev' => 1822653540,

            );
        }
        echo "отправка сообщения: ".$text.PHP_EOL;
        foreach($chats as $chat){
            file_put_contents(__DIR__ . '/abandosLogs.txt', date("Y-m-d H:i:s ")." Очередь ".print_r($queue,1)." ".print_r($chat, 1).PHP_EOL.
                print_r($text, 1).PHP_EOL, FILE_APPEND);
            $response = array(
                'chat_id' => $chat,
                'text' => $text
            );

            $token = '5145589614:AAGsVuSeZrXnjpHmJvL0PA-lSuDFalqsupU';

            $ch = curl_init('https://api.telegram.org/bot' . $token . '/sendMessage');

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_exec($ch);
            curl_close($ch);
        }
    }