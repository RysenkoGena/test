<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
ob_end_flush(); //отключить буферизацию
$vendor = "ИЭК";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
				
        $sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
        
        $list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));

        echo "Найдено товаров: ".$list->SelectedRowsCount()."<br>";
        $i=0; $artikuls=array();
         While($obEl = $list->GetNext()){
            $res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));
            if ($ob = $res->GetNext()) $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
         }
         ?><pre><? print_r($artikuls); ?></pre><?

$array=$artikuls;
$ii=0;
echo "\n\rРабота с XML\n\r";
$doc = new DOMDocument;
$doc->load('Description.xml');
//print_r($doc);
$xpath = new DOMXPath($doc);
//rint_r($xpath);

//$products = $xpath->query("/NewDataSet/Описание[Article='MKM14-N-12-31-Z' and Язык_Name='русский']/Описание");
//print_r($products);

  echo "Начало цикла перебора\n\r";
  foreach ($array as $kod => $artikul){
    if($artikul!=""){
	    $artikul=trim($artikul);
	    $ii++;
	    $arFilter = Array("IBLOCK_ID"=>4, "CODE"=>$kod);
	    $res = CIBlockElement::GetList(Array(), $arFilter);
	    if ($ob = $res->GetNextElement()){
		    $arFields = $ob->GetFields(); // поля элемента
		    $el = new CIBlockElement;
        $products = $xpath->query("/NewDataSet/Описание[Article='".$artikul."' and Язык_Name='русский']/Описание");
        foreach ($products as $product) $a = $product->nodeValue;	
		    echo $ii." ".$kod." ".$artikul." => ".$a."\n\r";	
        $res = $el->Update($arFields[ID], Array("DETAIL_TEXT"=>$a));
      }
	  }
  } 
?>
