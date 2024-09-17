<?php
$pathToDir = __DIR__."/../../../asterisk/";
echo PHP_EOL.$pathToDir.PHP_EOL     ;
$dir = opendir($pathToDir);
if($dir) {
    $strs = [];
    while ($el = readdir($dir)) {
        //echo $el.PHP_EOL;
        if($el != "nightlog.txt") continue;
        $fileContent = file($pathToDir.$el);
        if(unlink($pathToDir.$el)) file_put_contents("log.txt", date("Y-m-d H:i:s")." Файл ".$el." удален".PHP_EOL);
        break;
    }
}
else  echo "ошибка открытия каталога ".$pathToDir.PHP_EOL;

if(count($fileContent)) {
    $text = "Пропущенные звонки за прошлую ночь:".PHP_EOL;
    foreach ($fileContent as $string) {
        $item = explode("\t", $string);
        //print_r($items);
        //foreach ($items as $item){
            $text .= $item[0]." ".$item[1].PHP_EOL;
        //}
    }
}

//echo $text;
sendTMessage($text);

function sendTMessage($text, $queue = ""){
    file_put_contents(__DIR__ . '/abandosLogs.txt', date("Y-m-d H:i:s ")." Отправка сообщения ".print_r($queue,1)." ".print_r($text, 1).PHP_EOL, FILE_APPEND);
    echo "Отправка сообщения ". $text. " ". $queue;
    if(strpos($text, "RROR") || strpos($text, "прошлую ночь")){ //ищем ERROR
        $chats = array(
            'Gena' => 1569407398,
            'Yaroslav' => 1224740956,
            'Peshkov' => 1057631050,
            'Zaur' => 678579656,
            'Eliseev' => 5292809379,
            'Eldin' => 594159557,
            'Savelyev' => 1021482259,
            'Kolodochka' => 1299027654, //Колодочка
            'Ochichenko' => 1242037573,
            'Tereshenko' => 6837636583, //Терещенко
        );
    }elseif($queue == 8601){ //Элтэк
        $chats = array(
            'Gena' => 1569407398,
            //'Lobas' => 903296204,
            'Peshkov' => 1057631050,
            'Eliseev' => 5292809379,
        );
    }elseif($queue == 8602){ // Онежская
        $chats = array(
            'Gena' => 1569407398,
            //'Lobas' => 903296204,
            'Peshkov' => 1057631050,
            'Eldin' => 594159557,
            'Zaur' => 678579656,
        );
    }elseif($queue == 8603){ // Запад
        $chats = array(
            'Gena' => 1569407398,
            //'Lobas' => 903296204,
            'Peshkov' => 1057631050,
            'Eldin' => 594159557,
            'Kolodochka' => 1299027654,
        );
    }elseif($queue == 8604 || $queue == 597){ // Российская
        $chats = array(
            'Gena' => 1569407398,
            //'Lobas' => 903296204,
            'Peshkov' => 1057631050,
            'Savelyev' => 1021482259,
        );
    }elseif($queue == 8605){ // Интернет Магазин
        $chats = array(
            'Gena' => 1569407398,
            //'Lobas' => 903296204,
            'Peshkov' => 1057631050,
            'Kolodochka' => 1299027654,
            'Zaur' => 678579656,
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
            //'Petrovich755' => 1117429075 // Скоробогатский
            'Yaroslav' => 1224740956,
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