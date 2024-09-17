<?
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush(); //отключить буферизацию
if (CModule::IncludeModule("sale")):

   $arFilter = Array(
      "ID" => 89536,
      );
   $rsSales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter);
      //echo count($rsSales->Fetch());
      $i=0;
   while ($arSales = $rsSales->Fetch())
   {
      //echo "<pre>";
      //print_r($arSales);
      //echo "</pre>";
      if(++$i > 10) break;
   }
endif;

$basketActual = CSaleBasket::GetList(Array(),Array("ORDER_ID"=> 89937));

while ($arItems = $basketActual->GetNext()){
    $arBasketItems[] = $arItems;
    echo $arItems["PRODUCT_ID"].";".$arItems["PRICE"].";".$arItems["QUANTITY"].PHP_EOL;
}
//print_r($arBasketItems);

/*function getCheckLink($order_id){
    global $DB; $link;
    if($order_id > 16104)    $order = \Bitrix\Sale\Order::load($order_id); //старая нумерация шла до 16104 потом пошла новая с 47886
    else{
        $order =  CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), array("XML_ID"=>$order_id));
        $arOrder = $order->GetNext();
        $order = \Bitrix\Sale\Order::load($arOrder["ID"]);
        $order_id = $arOrder["ID"];
    }
    d($order);

    //file_put_contents("/home/bitrix/ext_www/yugkabel.ru/inc/temp.txt", print_r($order, true));
    $propertyCollection = $order->getPropertyCollection();
    $somePropValue = $propertyCollection->getItemByOrderPropertyId(3); //id поля PHONE
    $phone = $somePropValue->getValue();
    $cashbox = \Bitrix\Sale\Cashbox\Manager::getObjectById(3); //id кассы
    $res = $DB->Query("SELECT LINK_PARAMS FROM b_sale_cashbox_check WHERE ORDER_ID = ".$order_id);
    //file_put_contents("/home/bitrix/ext_www/yugkabel.ru/inc/temp.txt", print_r("SELECT LINK_PARAMS FROM b_sale_cashbox_check WHERE ORDER_ID = ".$order_id, true));
    while ($row = $res->Fetch()){
        $arrLink['LINK_PARAMS'] = unserialize($row['LINK_PARAMS']);
        $a = ($arrLink['LINK_PARAMS']);
        break;
    }
    if($arrLink['LINK_PARAMS'])   $link = $cashbox->getCheckLink($arrLink['LINK_PARAMS']);
    return $link;
*/