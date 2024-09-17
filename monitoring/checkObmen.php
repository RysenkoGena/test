<?
echo "Start".PHP_EOL;
$pathToDir = "/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/upload/";
$dir = opendir($pathToDir);
$files = array();
$count = 0;
while($el = readdir($dir)){
    $count++;
}
if($count > 200) sendTMessage("Похоже обмен завис, в очереди ".$count." файлов!");
file_put_contents(__DIR__ . '/log.txt', "OK".PHP_EOL);
echo "Finish".PHP_EOL;

function sendTMessage($text){
    if(strpos($text, "Похоже обмен завис")){ //ищем ERROR
        $chats = array(
            'Gena' => 1569407398,
        );
    }

    elseif(strpos($text, "RROR")){ //ищем ERROR
        $chats = array(
            'Gena' => 1569407398,
        );
    }

    else {
        $chats = array(
            'Gena' => 1569407398,
        );
    }

    echo "отправка сообщения: ".$text."\n";
    foreach($chats as $chat){
        
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
