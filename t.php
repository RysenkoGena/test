<?
header('Content-Type: text/html; charset=utf-8');

require_once("vendor/autoload.php");
echo "отправка";
$filePath = "/var/log/hive-agent.log";
echo "Строк в файле ".$filePath.": ". sizeof (file ($filePath))."\n";

define ('FILENAME',"/var/log/hive-agent.log");             // имя файла для чтения
define ('MAX_LEN', 5000);                                  // максимальная длина одной строки
 
if ($handle=fopen($filePath, "r"))                          // открываем файл
{
    //echo "Файл открыт. \n";
    fseek($handle, -MAX_LEN, SEEK_END);                    // установим указатель чтения на максимальную длину строки от конца файла
    $log = fread($handle, MAX_LEN);                        // прочитаем данные от указателя до конца файла
    $startPos = strrpos($log, "> ")+1;
    $endPos = strrpos($log, "}}}") + 3;
    fclose($handle);                                                // закроем файл
    $json=substr($log, $startPos, $endPos-$startPos);        // найдем позицию последнего вхождения символа "конца_строки" и выберем подстроку начиная со следующего символа
    //echo "Последняя строка: ".$json."\n";                                    // это и будет искомая последняя строка из файла
    $arr = json_decode($json, true);
    //print_r($arr["params"]["temp"]);
    $text = "Температуры карт: ";
    foreach($arr["params"]["temp"] as $temp)  {
        $text .= " ";
        $text .= $temp ;
    }
    echo $text;
    //$bot->sendMessage($text, "текст сообщения");
}
else echo "Ошибка чтения файла.\n";


//file_put_contents("t.txt", print_r($_REQUEST, true), FILE_APPEND);
$token = "2033947858:AAEadC4Nvpb4GACXPktHXiIhpgV0NPHc-TM";
$bot = new \TelegramBot\Api\Client($token);
/*
// обязательное. Запуск бота
$bot->command('start', function ($message) use ($bot) {
    $answer = 'Добро пожаловать!';
    $bot->sendMessage($message->getChat()->getId(), $answer);
    //$bot->sendMessage(1569407398, "текст сообщения");
});

// помощ
$bot->command('help', function ($message) use ($bot) {
    $answer = 'Команды:
/help - помощ';
    $bot->sendMessage($message->getChat()->getId(), $answer);
});

// запускаем обработку
$bot->run();
*/
//$bot->sendMessage(1569407398, "текст сообщения");


/*

*/
$bot->sendMessage(1569407398, $text);
?>