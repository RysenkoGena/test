<?
# программа заменяет строку $search на $replace в базе данных $db
#
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

$search = "http://";
$replace = "https://";
$db = "b_iblock_element";

$mysqli = new mysqli("localhost", "useryugkabel", "UIV1%3)}eCc}C+v", "dbyugkabel");
if ($mysqli->connect_errno) {
    printf("Не удалось подключиться: %s\n", $mysqli->connect_error);
    exit();
}

$query = "SELECT ID, DETAIL_TEXT FROM `dbyugkabel`.`b_iblock_element` WHERE (CONVERT(`DETAIL_TEXT` USING utf8) LIKE '%".$search."%');";

if ($result = $mysqli->query($query)) {
    printf("Select вернул %d строк.\n", $result->num_rows);
    $querys = [];
    while ($actor = $result->fetch_assoc()) {
        $text = str_replace($search, $replace, $actor['DETAIL_TEXT']);

        $querys["query"][] = "UPDATE `dbyugkabel`.`b_iblock_element` SET `DETAIL_TEXT` = '".addslashes($text)."' WHERE id=".$actor["ID"].";";
        $querys["id"][] = $actor["ID"];
    }
    $result->close();
}
debug($querys["id"]);
foreach ($querys["query"] as $key => $q){
    if($result = $mysqli->query($q)){
        echo "ID ".$querys["id"][$key]." OK!<br>";
    } else{
        echo "Ошибка: " . $mysqli->error;
    }
}
