<?php include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

echo "Отправка почтового сообщения.<br>";
$to = "rysenko@yugkabel.ru";
$subject = "Промокод";
$message = "";


/*print_r($_POST);
echo "<br>---------------<br>";
print_r($_FILES);
echo "<br>---------------<br>";*/

?>
<!--<form method="post" enctype="multipart/form-data" action="/inc/ajax/recivePromoCode.php">-->
<form method="post" enctype="multipart/form-data" >
	<input name=text>
	<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
	<input name=file type=file><br>
	<input type=submit>
</form>

<?


if(isset($_POST["text"]) && $_POST["text"] != ""){
	$message = "Сообщение от клиента: ".$_POST["text"];
	if(isset($_FILES["file"]) && $_FILES["file"]["size"] != 0){
		if(mailWithAttach($to, $subject, $message, $headers, $_FILES["file"]) === true) echo "Спасибо. Ваш ответ принят.";
		else echo "Ошибка отправления данных";
	}
	else{
        echo "попытка отправки";

		if(mail2($to, $subject, $message, $headers) === true) echo "Спасибо. Ваш ответ принят.";
		else echo "Ошибка отправления данных2";
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
