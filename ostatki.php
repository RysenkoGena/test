<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo "Модуль ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"." загружен.\r\n";
function ostatki($id){
	$obStoreProduct = CCatalogStoreProduct::GetList(
		array("STORE_ID" => "ASC"),
		array("PRODUCT_ID" => $id),
		false,
		false,
		array("ID", "STORE_ID", "AMOUNT", "STORE_NAME")
	);
	$q='';
	while($arStoreProduct = $obStoreProduct -> Fetch()){
		echo "<td>".$arStoreProduct["AMOUNT"];
		$q .= ','.$arStoreProduct["AMOUNT"];
	}
	$q .= ',"'.date("Y-m-d").'")';
	return $q;
}
function cenaz($id){
	$db_props = CIBlockElement::GetProperty(4, $id, array("sort" => "asc"), Array("CODE" => "cenaz"));
	if($ar_props = $db_props -> Fetch()){
		return  floatval($ar_props['VALUE']);
	}
	else return 999;
}

echo "\n\r<br>Начало цикла перебора\n\r";

$arFilter = Array("IBLOCK_ID" =>4, "ACTIVE" => "Y");
$res = CIBlockElement::GetList(Array(), $arFilter);
echo "Количество элементов = ". $res->SelectedRowsCount();
echo "<table>";
$query = "INSERT INTO products(code,s0,s1,s2,s3,s4,s5,s6,s7,s8,d) VALUES ";
$query_price = "INSERT INTO price(code, name,price) VALUES ";

 $conn = new mysqli("portal.yugkabel.ru", "portal", "portal", "ostatki", 3301);
  if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);

$ii=0;
while($sect = $res -> GetNext()){
	if($ii!=0) {$query .= ","; $query_price .= ",";}
	$ii++;
	$query .= '('.$sect["XML_ID"];
	$query_price .= '('.$sect["XML_ID"];
	echo "<tr><td>";
	echo $sect["XML_ID"];
	$query .= ostatki($sect["ID"]);
	$query_price .= ',"'.$sect["NAME"].'",'.cenaz($sect["ID"]).')';
	echo "<td>".cenaz($sect["ID"]);
	$query3 = 'INSERT INTO price(code,name,price) VALUES ('.$sect["XML_ID"].',"'.$sect["NAME"].'",'.cenaz($sect["ID"]).');';

}
$query .= ';';
echo "</table>";
//echo $query."<br>";
//echo $query_price."<br>";
if ($ob = $res->GetNextElement()){
	$arFields = $ob->GetFields();
	$arProps =  $ob->GetProperties();
}

  $result = $conn->query($query);
//$result2 =  $conn->query($query_price);
if($result){
    print 'Success! Total ' .$mysqli->affected_rows .' rows added.<br />';
}else{
    die('Error : ('. $mysqli->errno .') '. $mysqli->error);
}
//if($result2) print 'Таблица price обновлена';

//  echo "Количество строк:". $result->num_rows;

  $conn->close();
?>