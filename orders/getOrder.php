<?php
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
/*$ORDER_ID = 184717;
if (!($arOrder = CSaleOrder::GetByID($ORDER_ID)))
    echo "Заказ с кодом ".$ORDER_ID." не найден";
else{
    echo "<pre>";
    print_r($arOrder);
    echo "</pre>";
}*/

ob_end_flush(); //отключить буферизацию
$arFilter = Array("!%XML_ID"=>"-");
$rsOrder = CSaleOrder::GetList(Array('ID' => 'DESC'), $arFilter, false, false, Array()); // Array("PROPERTY_CONSIGNEE")
echo "Всего онлайн заказов: ".$rsOrder->SelectedRowsCount()."<br>";
$i=0;
while($arOrder = $rsOrder->Fetch())
{
    $orders[$arOrder["ID"]] = $arOrder["USER_ID"];
    if($i++<2)    echo $arOrder["ID"]." ".$arOrder["USER_ID"]."<br>";
    //if(++$i > 10)    break;

}

$from = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")),
    mktime(0,0,0,1,1,2024));
echo $from."<br>";
$filter = ["!UF_INN" => false, ">LAST_LOGIN" => $from];

$rsUsers = CUser::GetList([], ($order="desc"), $filter, ["SELECT" => ["UF_*"]]); // выбираем пользователей
echo "Юрлиц на сайте: ".$rsUsers->SelectedRowsCount()."<br>";
$i = 0;
while($user = $rsUsers->Fetch()){
    //if($i++ < 2)    deb($user);
    //break;
    //echo $user"[".$f_ID."] (".$f_LOGIN.") ".$f_NAME." ".$f_LAST_NAME."<br>";
    //echo $user["WORK_COMPANY"]."<br>";
    $users[$user["ID"]] = $user["WORK_COMPANY"];
    $inn[$user["ID"]] = $user["UF_INN"];
}
echo "Юрлица:"."<br>";
deb($users);

$array=[]; $i = 0;
foreach ($orders as $key => $userid){
    if(array_key_exists($userid, $users)) {
        //echo "Найдено" . "<br>";
        if(!key_exists($users[$userid]." ".$inn[$userid], $array)){
            $array[$users[$userid]." ".$inn[$userid]] = 1;
        }
        else {
            $array[$users[$userid]." ".$inn[$userid]]++;
            echo $key."<br>";
        }
        $i++;
    }
}
echo "Всего онлайн заказов от юрлиц: ".$i."<br>";
arsort($array);
deb($array);










