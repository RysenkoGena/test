<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
ob_end_flush(); //отключить буферизацию
$vendor = ["ИЭК", "ONI", "ITK", "GENERICA"];
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
debug($vendor);
echo "Найдено товаров: ".$list->SelectedRowsCount()."<br><br>";
$artikuls = array(); $ids = array();
        While($obEl = $list->GetNext()){
            $ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext();
            $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
            $ids["ID"][trim($ob['VALUE'])] = $obEl["ID"];
            $ids["XML_ID"][trim($ob['VALUE'])] = $obEl["XML_ID"];
         }
//d($artikuls);



//$string = implode(",", $artikuls);
//load($string);

echo PHP_EOL.$string.PHP_EOL;


$ii=0;
$stocks = array();
echo "<table>";
foreach ($artikuls as $kod => $artikul){
    if($artikul != ""){
	    $artikul=trim($artikul);
        echo "<tr><td>".$kod."<td>".$artikul."<td>https://yugkabel.ru/products/".$kod."/";
	}
}
echo "</table>";
?>
