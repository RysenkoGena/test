<?
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");


$good = CIBlockElement::GetByID(130166);
if($ar_res = $good->GetNext())
    $good = CCatalogProduct::GetByID(130166);

d($good);
//if($good['QUANTITY'] < $num) die("error Доступно не более ".$good['QUANTITY']);