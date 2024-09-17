<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule("sale");
include_once(GetLangFileName("/home/bitrix/ext_www/yugkabel.ru/bitrix/php_interface/include/sale_payment/sbr", "/payment.php"));
include_once("/home/bitrix/ext_www/yugkabel.ru/bitrix/php_interface/include/sale_payment/sbr/classes/psbankProtocol.class.php");

$orderId = 47906;
// Для управления кастомными ID заказов
if(!function_exists('byId')){
	function byId($orderId){
		$arOrder = CSaleOrder::GetByID($orderId);
		if(!$arOrder) $arOrder = CSaleOrder::GetList(Array(),Array("ACCOUNT_NUMBER"=>$orderId))->GetNext();
		return $arOrder;
	}
}

$arOrder = byId(IntVal($orderId));
CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);

$MERCHANT = CSalePaySystemAction::GetParamValue("PS_MERCHANT");
$KEY = CSalePaySystemAction::GetParamValue("PS_KEY");
//echo $KEY;
echo " https://securepayments.sberbank.ru/payment/rest/register.do?userName=yugkabel-api&password=x:ikpkkwdrapi&orderNumber=47906&amount=100&returnUrl=http://yugkabel.ru&description=OplataZaZakazNomer5<br>" ;
echo " https://securepayments.sberbank.ru/payment/rest/getOrderStatusExtended.do?userName=yugkabel-api&password=x:ikpkkwdrapi&orderNumber=47906" ;

?>
