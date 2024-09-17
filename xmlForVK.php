    <?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

 $yml_catalog = new SimpleXMLElement('<yml_catalog/>');
 $yml_catalog->addAttribute('date', date("Y-m-d H:i"));
 //$yml_catalog = $xml->addChild('yml_catalog');
 $shop = $yml_catalog->addChild('shop');
$name = $shop->addChild('name', "ЮгКабель");
$url = $shop->addChild('url', "http://yugkabel.ru");
 $offers = $shop->addChild('offers');
$arrFilter = Array("ACTIVE"=>"Y"); //выбрать все активные торвары
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PREVIEW_PICTURE"));

$i=0;
//$artikuls=array();

While($obEl = $list->GetNext()){
	$i++;
	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));

    if ( $ob = $res->GetNext() ){
      $offer = $offers->addChild('offer');
      $id = $offer->addAttribute('id', $obEl["XML_ID"]);
      $available = $offer->addAttribute('available', 'true');
      $url = $offer->addChild('url', "https://yugkabel.ru/products/".$obEl["XML_ID"]."/");
      $name = $offer->addChild('name', $obEl["NAME"]);
      $picture = $offer->addChild('picture', "https://yugkabel.ru".CFile::GetPath($obEl['PREVIEW_PICTURE'])); 
      if($ob['VALUE'] !="") $vendorCode = $offer->addChild('vendorCode', $ob['VALUE']);
      //echo $obEl["XML_ID"]."<br>\n";
    }
    $artikuls[] = $obEl["XML_ID"];
}

Header('Content-type: text/xml');
  
print($yml_catalog->asXML());
?>



