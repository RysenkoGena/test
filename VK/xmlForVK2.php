<?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); 
$products = array(30190,29845,142199,11545,82889,76408,80953,80950,91463,84881,80116,84890,84905,19926,18094,116987,16740,16566,98524,97846,89238,16441,41936,
41403,17645,72923,72925,36648,36696,71175,72495,118661,70193,70975);
$yml_catalog = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><yml_catalog/>');
$yml_catalog->addAttribute('date', date("Y-m-d H:i"));
 //$yml_catalog = $xml->addChild('yml_catalog');
$shop = $yml_catalog->addChild('shop');
$name = $shop->addChild('name', "ЮгКабель");

$url = $shop->addChild('url', "http://yugkabel.ru");

$currency = $shop->addChild('currency');
$rate = $currency->addAttribute('rate', 1);
$id = $currency->addAttribute('id', 'RUB');

$categories = $shop->addChild('categories');

$offers = $shop->addChild('offers');
$arrFilter = Array("ACTIVE"=>"Y"); //выбрать все активные торвары
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter, "XML_ID"=>$products);
$list = CIBlockElement::GetList(Array(), $sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PREVIEW_PICTURE", "PROPERTY_PROIZVODITEL_FILTER", "DETAIL_TEXT"));
//$listForSections = $list;
$sections = array();
/*While($obEl = $list->GetNext()){
  $sections[] = $obEl["IBLOCK_SECTION_ID"];
}*/
$i=0;
//$artikuls=array();

While($obEl = $list->GetNext()){
  //if(!in_array($obEl["IBLOCK_SECTION_ID"], $sections["sectionID"])){
      //$sections["sectionID"][] = $obEl["IBLOCK_SECTION_ID"];
      $parent = getParent($obEl["IBLOCK_SECTION_ID"]);
      if(!is_array($sections["parentID"]) || !in_array($parent["XML_ID"], $sections["parentID"])){
        $sections["parentID"][] = $parent["XML_ID"];
      
        $sectionName = CIBlockSection::GetList(array(), array("ID"=>$parent["ID"]), array("NAME"))->GetNext();
        $sectionName = $sectionName["NAME"];
        //$sectionName = strtolower(substr($sectionName, 4, strlen($sectionName)-4));
        $sectionName = upFirstLetter($sectionName);
        $category = $categories->addChild('category', $sectionName);
        $id_category = $category->addAttribute('id', trim($parent["XML_ID"]));
      }
  //}
	$i++;
  //if($i>10) break;
	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));
  $price=CPrice::GetBasePrice($obEl["ID"]);
  if ( $ob = $res->GetNext() ){
      $obEl["DETAIL_TEXT"] = str_replace("&nbsp;", " ",$obEl["DETAIL_TEXT"]);
      $offer = $offers->addChild('offer');
      $id = $offer->addAttribute('id', $obEl["XML_ID"]);
      $available = $offer->addAttribute('available', 'true');
      $price = $offer->addChild('price', $price["PRICE"]);
      $currencyId = $offer->addChild('currencyId', "RUR");
      $categoryId = $offer->addChild('categoryId', trim($parent["XML_ID"]));
      $url = $offer->addChild('url', "https://yugkabel.ru/products/".$obEl["XML_ID"]."/");
      $name = $offer->addChild('name', $obEl["NAME"]);
      $description = mb_strtolower($obEl["DETAIL_TEXT"]);
      //$description = $obEl["DETAIL_TEXT"];
      //$description = str_replace("&QUOT", "&quot", $description);
      $description = mb_substr($description, 0, 250);
      //$description = mb_strtoupper($description);
      $name = $offer->addChild('description', $description);
      $picture = $offer->addChild('picture', "https://yugkabel.ru/img2/".getSubDirImg($obEl["XML_ID"])."/".$obEl["XML_ID"].".jpg");
      $firm = $offer->addChild('vendor', $obEl["PROPERTY_PROIZVODITEL_FILTER_VALUE"]);
      if($ob['VALUE'] !="") $vendorCode = $offer->addChild('vendorCode', $ob['VALUE']);
      //echo $obEl["XML_ID"]."<br>\n";
    }
    $artikuls[] = $obEl["XML_ID"];
}

Header('Content-type: text/xml');
  
print($yml_catalog->asXML());

function getParent($section_id){
  $scRes = CIBlockSection::GetNavChain(4, $section_id, array("ID", "XML_ID", "DEPTH_LEVEL"));
  while($arGrp = $scRes->Fetch()){
      if ($arGrp['DEPTH_LEVEL'] == 1){
        return $arGrp;
        $ROOT_SECTION_ID["ID"] = $arGrp['ID'];
        $ROOT_SECTION_ID["XML_ID"] = $arGrp['XML_ID'];
      }
  }
  return $ROOT_SECTION_ID;
}
function upFirstLetter($str, $encoding = 'UTF-8')
{
    return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding)
    . mb_substr($str, 1, null, $encoding);
}

function getSubDirImg($xml_id){
  if(strlen($xml_id) <= 3) return "0000";
  else {
    return substr($xml_id, 0, -3)."000";
  }
}

?>