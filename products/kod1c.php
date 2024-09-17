<?PHP
//echo 123;
use Bitrix\Catalog\ProductTable;
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
$productId = 130497; // ID товара, для которого нужно установить единицу измерения
$measureCode = 1; // Код единицы измерения
//echo "Код товара: ".$productId;

$updateElements = CIBlockElement::GetList(
    Array("ID" => "ASC"),
    //Array("IBLOCK_ID" => 4,">PROPERTY_kratnost" => 1),
    Array("IBLOCK_ID" => 4, "ACTIVE"=>"Y", "=PROPERTY_PROIZVODITEL_VALUE" => false),
    false,
    false,
    Array(
        'ID',
        'NAME',
        'XML_ID',
        'PROPERTY_EXISTS_AT_STORES',
        'PROPERTY_PROIZVODITEL',
        'QUANTITY_TRACE',
    )
);

$i = $updateElements->SelectedRowsCount();
echo PHP_EOL.$i."<br>";
//die();
while ($arFields = $updateElements->GetNext()) {
    $i--;

    //deb($arFields);

    /*$product = ProductTable::getById($productId)->fetchObject();
    debug(get_class_methods($product));
    $product->setField('QUANTITY_TRACE', 'Y');
    $a = $product->save();
    print_r($a);
    break;*/
    //$prop = ["kod1c" => $arFields['XML_ID']];
    echo $i ." " .$arFields["XML_ID"]." ".$arFields["NAME"]." ".$arFields["ID"]."<br>".PHP_EOL;
    //echo $i.PHP_EOL;
        //deb(get_class_methods($el));
    //$a = CCatalogProduct::Update($arFields['ID'], ['QUANTITY_TRACE'=>"Y"]);
    //deb($a);
    //break;

}
