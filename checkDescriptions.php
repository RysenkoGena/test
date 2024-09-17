<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();
echo "<h1>Отчет по заполненности описаний товаров</h1>";
$arrFilter = Array("ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "DETAIL_TEXT"));
$count = $list->SelectedRowsCount();
echo "Всего товаров на сайте: ".$count."<br>\n";

$artikuls=array(); $i = 0; $j = 0; $counts = array(); $neZapolneno = [];
While($obEl = $list->GetNext()){
  //$i++;
  if($obEl["DETAIL_TEXT"] != ""){
    //echo $obEl["DETAIL_TEXT"].PHP_EOL;
    $i++;
    $ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"proizvoditel_filter"))->GetNext();
    $counts[$ob["VALUE"]]++;
    //print_r($ob["VALUE"]);
  }
  else{
    $j++;
    $ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"proizvoditel_filter"))->GetNext();
    $neZapolneno[$ob["VALUE"]]++;
  }
 }

 echo "Описания заполнены у ".$i." товаров<br>";
 echo "Описания не заполнены у ".$j." товаров<br>";
 ?><table><tr><th>Заполнено<th>Не заполнено<tr>
    <td>
 <?debug($counts);?>
    <td valign=top>
  <?debug($neZapolneno);?>
</table>

?>