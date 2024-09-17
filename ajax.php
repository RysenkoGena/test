<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if(isset($_POST["a"]) && $_POST["a"]){
    
\Bitrix\Main\Mail\Event::send(array(
    "EVENT_NAME" => "BLOG_YOU_TO_BLOG", 
    "LID" => "s1", 
    "C_FIELDS" => array( 
        "EMAIL" => "rysenko@yugkabel.ru",
        "NAME" =>   "Пупкин Пуп",
        "BLOG_ADR" =>   "<br>Клиент: ".$_POST["fio"]."<br>Email: ".$_POST["email"]."<br>Мобильный: ".$_POST["mobile"]."<br>Товар ".$_POST["kod"]." ".$_POST["productName"] //Текст письма
    ), 
));
$_SESSION["zapros"][$_POST["kod"]] = (int) time();
echo "Запрос отправлен";
}
else {
    echo "False";
?>
<pre> <? print_r($_SESSION); ?></pre><?
    }


?>