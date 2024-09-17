<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
$headers = getallheaders();
//print_r($headers);
$token = "OAuth y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4";


if ($headers["Authorization"] == $token || $USER->IsAdmin()) {

header("content-type:text/xml; charset=UTF-8");

//echo $string;

    $rsUsers = CUser::GetList(
        ($by = "UF_ID"),
        ($order = "asc"),
        array("!UF_USER_ASKARON_CLIENT_CODE_YM" => false),
        array("SELECT" => array("UF_BAL_NA_KARTU", "UF_ID", "UF_BAL_NA_KUPON", "UF_KUPON", "UF_KUPON_FROM_1C", "UF_USER_ASKARON_CLIENT_CODE_YM"))
    );
    $count = $rsUsers->SelectedRowsCount();

//while($arUser = $rsUsers->Fetch()) {
//$rsUsers = CUser::GetList(array('sort' => 'asc'), array(),     array(   "SELECT" => array("UF_ID",),));
//$i = 0;
    $string = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<xml>
    <КоличествЗаписей>$count</КоличествЗаписей>
</xml>
XML;
    $clients = simplexml_load_string($string);
//print_r($clientXML->Клиенты);
//$clients = $clientXML->Клиенты;
//d($clients);

    while ($user = $rsUsers->Fetch()) {

        $client = $clients->addChild("Клиент");
        $client->XML_ID = $user['UF_ID'];
        $client->Фамилия = $user['LAST_NAME'];
        $client->Имя = $user['NAME'];
        $client->Мобильный = $user['PERSONAL_PHONE'];
        $client->Email = $user['EMAIL'];
        if (is_array($user['UF_USER_ASKARON_CLIENT_CODE_YM'])) {
            $text = "";
            foreach ($user['UF_USER_ASKARON_CLIENT_CODE_YM'] as $YM_Code) {
                $text .= $YM_Code . ",";
            }
            $text = substr($text, 0, -1);
            $client->YM_CODE = $text;
        } else   $client->YM_CODE = $user['UF_USER_ASKARON_CLIENT_CODE_YM'];

        /*if($user["ID"] == 10000) {
            d($user);
            break;
        }*/
        //echo "<tr><td>".++$i. "<td> ".PHP_EOL;
        //echo $user["UF_ID"].PHP_EOL;
    }

    $clientXML = simplexml_load_string($clients->asXML());
    echo($clientXML->asXML());
}
else echo "<a href='/auth'>Авторизуйтесь!</a>";