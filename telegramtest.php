<?
header('Content-Type: text/html; charset=utf-8');

require_once("vendor/autoload.php");
//file_put_contents("t.txt", print_r($_REQUEST, true), FILE_APPEND);
$token = "2033947858:AAEadC4Nvpb4GACXPktHXiIhpgV0NPHc-TM";
$bot = new \TelegramBot\Api\Client($token);

$chatId = -1002141433805;
?>
<pre>
<? print_r($bot);

//$members = $bot->getChatMembers($chatId);
//print_r($members);
echo 123;

?>
</pre>




//$bot->sendMessage(1569407398, $text);
