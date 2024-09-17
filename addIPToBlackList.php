<?PHP $_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo "Добавление в черный список<br>";

//CModule::IncludeModule('security');
$cData = new CSecurityIPRule;

//deb(get_class_methods($cData));
//$arSelectedFields = [0 => "ID"];
//$rsData = $cData->GetList(["INCL_IP"], [], []);

//$sTableID = "tbl_security_iprule_list";
//$rsData = new CAdminResult($rsData, $sTableID);

$arFilter = ["IP" => $_SERVER['REMOTE_ADDR'], "ACTIVE" => "Y"];
$rsData = $cData->GetList(["ID"], $arFilter, []);

if( $rsData->SelectedRowsCount())
    lg("Странно, такой IP уже заносили в черный список");

//while($arRes = $rsData->Fetch())    deb($arRes);

$arFields = array(
    "RULE_TYPE" => "M",
    "ACTIVE" => "Y",
    "ADMIN_SECTION" => "Y",
    "SITE_ID" => "",
    "SORT" => 500,
    "NAME" => "ANTIBOT generateSmsCode",
    "ACTIVE_FROM" => "",
    "ACTIVE_TO" => "",
    "INCL_IPS" => ["n0" => $_SERVER['REMOTE_ADDR']],
    "EXCL_IPS" => ["n0" => ""],
    "INCL_MASKS" => ["n0" => "/*"],
    "EXCL_MASKS" => ["n0" => ""]
);

    //$ID = $cData->Add($arFields);
if($ID > 0)
    lg("Добавили ". $_SERVER['REMOTE_ADDR']. " в черный список");
