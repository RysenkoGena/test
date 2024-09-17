<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//echo "Модуль ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"." загружен.\r\n";
function ostatki($id){
	$obStoreProduct = CCatalogStoreProduct::GetList(array("STORE_ID" => "ASC"),array("PRODUCT_ID" => $id),false,false,array("ID", "STORE_ID", "AMOUNT", "STORE_NAME"));
	$q='';
	while($arStoreProduct = $obStoreProduct -> Fetch())	$q .= ','.$arStoreProduct["AMOUNT"];
	$q .= ',"'.date("Y-m-d").'")';
	return $q;
}
function cenaz($id){
	$db_props = CIBlockElement::GetProperty(4, $id, array("sort" => "asc"), Array("CODE" => "cenaz"));
	if($ar_props = $db_props -> Fetch())	return  floatval($ar_props['VALUE']);
	else return 999;
}
 $conn = new mysqli("portal.yugkabel.ru", "portal", "portal", "ostatki", 3301);
 if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);

echo "Очищаем старые записи за сегоднешний день если они есть...";
$query = "DELETE FROM products WHERE d='".date("Y-m-d")."';";
//echo "\n".$query;

$result = $conn->query($query);

if($result) echo $conn -> affected_rows." OK\n\r";
else echo "Ошибка".$conn->errno;

//echo "\n\rНачало цикла перебора\n\r";

$arFilter = Array("IBLOCK_ID" =>4, "ACTIVE" => "Y");
$res = CIBlockElement::GetList(Array(), $arFilter);
echo "Количество элементов из Bitrix = ". $res->SelectedRowsCount()."\n\r";
$query = "INSERT INTO products(code,s0,s1,s2,s3,s4,s5,s6,s7,s8,d) VALUES ";
$query_price = "INSERT INTO price(code, name,price) VALUES ";

$ii=0;
while($sect = $res -> GetNext()){
	if($ii!=0) {$query .= ","; $query_price .= ",";}
	$ii++;
	$query .= '('.$sect["XML_ID"];
	$query_price .= '('.$sect["XML_ID"];
//	echo $sect["XML_ID"];
	$query .= ostatki($sect["ID"]);
	$query_price .= ',"'.$sect["NAME"].'",'.cenaz($sect["ID"]).')';
	$query3 = 'INSERT INTO price(code,name,price) VALUES ('.$sect["XML_ID"].',"'.$sect["NAME"].'",'.cenaz($sect["ID"]).');';

}
$query .= ';';
if ($ob = $res->GetNextElement()){
	$arFields = $ob->GetFields();
	$arProps =  $ob->GetProperties();
}

  $result = $conn->query($query);
//$result2 =  $conn->query($query_price);
if($result){
 echo  "Добавлено в БД элементов ".$conn->affected_rows ."\n\r";
}else{
    die('Error : ('. $conn->errno .') '. $conn->error);
}
//if($result2) print 'Таблица price обновлена';
//  echo "Количество строк:". $result->num_rows;
  $conn->close();
echo "Конец\n\r";
?>