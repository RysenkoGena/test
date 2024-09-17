<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
global $USER;
//d($USER->GetParam("LOGIN"));

$filter = [
    "ACTIVE" => "Y",
    //"ID" => 24195
];

$rsUsers = CUser::GetList(
    ($by = "LAST_LOGIN"),
    ($order = "desc"),
    $filter,
    //array("!UF_USER_ASKARON_CLIENT_CODE_YM" => false),
    array("SELECT" => array("LAST_LOGIN", "UF_BAL_NA_KARTU", "UF_ID", "UF_BAL_NA_KUPON", "UF_KUPON", "UF_KUPON_FROM_1C", "UF_USER_ASKARON_CLIENT_CODE_YM"))
);
$count = $rsUsers->SelectedRowsCount();
echo "<h1>100 последних авторизованных клиентов на сайте и их user_id</h1>";
$i = 0; $j = 0;
echo "<table border='1' cellpadding='0' cellspacing='0' ><tr><th>№<th>ФИО<th>Дата авторизаци<th>yandex_id</tr>";
while ($user = $rsUsers->Fetch()) {
    $groups = CUser::GetUserGroup($user["ID"]);

    $textGroup = ""; $emptyGroup15 = true;
    foreach ($groups as $group) {
        if($group == 15)
            $emptyGroup15 = false;
        $textGroup .= $group . " ";
    }


    if($emptyGroup15) {
        $arGroups = CUser::GetUserGroup($user["ID"]);
        $arGroups[] = 15;
        CUser::SetUserGroup($user["ID"], $arGroups);
        //lg($user);
        echo "<tr><td style='padding:5px 5px 5px 5px;'>" . ++$i . "<td>" . $user["LAST_NAME"] . " " . $user["NAME"] . " ";
        echo "<td>" . $user["LAST_LOGIN"] . "<td>" . $user["UF_USER_ASKARON_CLIENT_CODE_YM"][0];
        echo "<td>" . $textGroup;
    }
    if(isset($user["UF_USER_ASKARON_CLIENT_CODE_YM"][0])) $j++;
    //if($i >= 100) break;
}
echo "</table><b>всего: ".$i.", с user_id: ".$j."</b><br>";
//echo "Всего активных пользователей на сайте:". $count;
//debug ($user);
//$autoTimeZone = trim($USER->GetParam("AUTO_TIME_ZONE"));
echo "<br><br>";
