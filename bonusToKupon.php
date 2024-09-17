<?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
 if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); 


 $discountValue = 150; //размер скидки в промокоде
 $discountName = "Бонус ".$discountValue." руб";
$a = CSaleDiscount::GetByID(61);
debug($a);
echo "<br>";
debug(unserialize($a["ACTIONS"]));
echo "<br>";

use Bitrix\Sale\Internals;
CModule::IncludeModule("catalog");
CModule::IncludeModule("iblock");
CModule::IncludeModule("sale");
global $APPLICATION;
//\Bitrix\Main\Loader::includeModule('catalog');
$data = CSaleDiscount::getList(array(),array("NAME"=>$discountName));
if($data->SelectedRowsCount()==0){
    //while($ob = $data->Fetch()) debug($ob);

    //$data = \Bitrix\Catalog\DiscountTable::getList(array(),array())->fetchAll();
    //DiscountTable::getList()->fetchAll();
    //debug($data);

    $Actions["CLASS_ID"] = "CondGroup";
    $Actions["DATA"]["All"] = "AND";
    $Actions["CHILDREN"][0]["CLASS_ID"] = "ActSaleBsktGrp";
    $Actions["CHILDREN"][0]["DATA"]["Type"] = "Discount";
    $Actions["CHILDREN"][0]["DATA"]["Value"] = $discountValue;
    $Actions["CHILDREN"][0]["DATA"]["Unit"] = "CurAll";
    $Actions["CHILDREN"][0]["DATA"]["Max"] = 0;
    $Actions["CHILDREN"][0]["DATA"]["All"] = "AND";
    $Actions["CHILDREN"][0]["DATA"]["True"] = "True";
    $Actions["CHILDREN"][0]["CHILDREN"] = "";
    //$Actions["CHILDREN"][0]["CHILDREN"] = array();

    $Conditions["CLASS_ID"] = "CondGroup";
    $Conditions["DATA"]["All"] = "AND";
    $Conditions["DATA"]["True"] = "True";
    $Conditions["CHILDREN"] = "";


    //Массив для создания правила
    $arFields = array(
        "LID"=>"s1",
        "NAME"=>$discountName,
        "CURRENCY"=>"RUB",
        "ACTIVE"=>"Y",
        "USER_GROUPS"=>array(2),
        //"ACTIVE_FROM"=>ConvertTimeStamp($unixStart, "FULL"),
        //"ACTIVE_TO"=>ConvertTimeStamp($unixEnd, "FULL"),
        "CONDITIONS"=>$Conditions,
        'ACTIONS' => $Actions
        );
	
    $ID = CSaleDiscount::Add($arFields); //Создаем правило корзины
    if ($ID > 0) echo "Правило '".$discountName."' создано!";
}
else {
    echo "Правило '".$discountName."' уже есть";
    while($ob = $data->Fetch()) $ID = $ob["ID"];
}

if ($ID > 0) { 	
	$codeCoupon = CatalogGenerateCoupon(); //Генерация купона
    $fields["DISCOUNT_ID"] = $ID;
	$fields["COUPON"] = $codeCoupon;
	$fields["ACTIVE"] = "Y";
    //$fields["ONE_TIME"] = "O";
	$fields["TYPE"] = 2;
	$fields["MAX_USE"] = 0;
	$dd = Internals\DiscountCouponTable::add($fields); //Создаем купон для этого правила
	if (!$dd->isSuccess())
	{
		$err = $dd->getErrorMessages();
	}else{
		echo '<br>Купон на скидку: '.$codeCoupon;
	}
}else{
	$ex = $APPLICATION->GetException();  
	echo 'Ошибка: '.$ex->GetString();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>