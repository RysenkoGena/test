<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
$productId = 242009; // ID товара, для которого нужно установить единицу измерения
$measureCode = 1; // Код единицы измерения
echo $productId;

$updateElements = CIBlockElement::GetList(
    Array("ID" => "ASC"),
    //Array("IBLOCK_ID" => 4,">PROPERTY_kratnost" => 1),
    Array("IBLOCK_ID" => 4, "ID" => $productId),
    false,
    false,
    Array(
        'ID',
        'NAME',
        'XML_ID',
        'PROPERTY_EXISTS_AT_STORES',
    )
);

$i = $updateElements->SelectedRowsCount();

while ($arFields = $updateElements->GetNext()) {
    $i--;
    echo "<pre>";
    print_r($arFields);
    echo "</pre>";


    break;
    $prop = ["kod1c" => $arFields['XML_ID']];
    //echo $i ." " .$arFields["XML_ID"]." ".$arFields["NAME"]." ".$arFields["ID"].PHP_EOL;
    echo $i.PHP_EOL;
    $el = new CIBlockElement;
    $a = $el->Update($arFields['ID'], ["PROPERTY_VALUES" => ["kod1c" => $arFields['XML_ID']]]);


}
