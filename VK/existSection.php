<?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
 if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$yml_catalog = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><yml_catalog/>');
 //$yml_catalog->addAttribute('encoding', 'UTF-8');
$yml_catalog->addAttribute('date', date("Y-m-d H:i"));
 //$yml_catalog = $xml->addChild('yml_catalog');
$shop = $yml_catalog->addChild('shop');
$name = $shop->addChild('name', "ЮгКабель");
$url = $shop->addChild('url', "https://yugkabel.ru");

//$currency = $shop->addChild('currency');
//$rate = $currency->addAttribute('rate', 1);
//$id = $currency->addAttribute('id', 'RUB');

$categories = $shop->addChild('categories');
$offers = $shop->addChild('offers');

if(isset($_GET["section"]) && $_GET["section"] != ""){
    lg($_GET["section"]);
    $arFilter = array('IBLOCK_ID' => 4, "CODE" => $_GET["section"]);
    $arSelect = array('IBLOCK_ID', 'ID', 'NAME', 'SECTION_PAGE_URL', 'DETAIL_PICTURE');
    $rsSections = CIBlockSection::GetList([], $arFilter, true, $arSelect);
    //lg($rsSections->SelectedRowsCount());
    while ($arSection = $rsSections->GetNext())
        $sectionId = $arSection["ID"];
    //lg($sectionId);
        //echo htmlspecialcharsbx($arSection['NAME']).' LEFT_MARGIN: '.$arSection['LEFT_MARGIN'].' RIGHT_MARGIN: '.$arSection['RIGHT_MARGIN'].'<br>';
}

$arrFilter = Array("ACTIVE"=>"Y", "PROPERTY_nalichie_na_sklade" => "Да", "SECTION_ID" => $sectionId, "INCLUDE_SUBSECTIONS" => "Y"); //выбрать все активные торвары
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);
$list = CIBlockElement::GetList(Array(), $sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PREVIEW_PICTURE", "PROPERTY_PROIZVODITEL_FILTER"));
lg($list->SelectedRowsCount());

/*While($obEl = $list->GetNext()){
  $sections[] = $obEl["IBLOCK_SECTION_ID"];
}*/
//$artikuls=array();
$sections = array(); $i=0;
While($obEl = $list->GetNext()){
      //if(!in_array($obEl["IBLOCK_SECTION_ID"], $sections["sectionID"])){
      //$sections["sectionID"][] = $obEl["IBLOCK_SECTION_ID"];
      $parent = getParent($obEl["IBLOCK_SECTION_ID"]);
      $parent["XML_ID"] = trim($parent["XML_ID"]);
      if($parent["XML_ID"] == "8997") continue; // прочее
      if(!is_array($sections["parentID"]) || !in_array($parent["XML_ID"], $sections["parentID"])){
        $sections["parentID"][] = $parent["XML_ID"];
        $sectionName = CIBlockSection::GetList(array(), array("ID"=>$parent["ID"]), array("NAME"))->GetNext();
        //$sectionName = strtolower(substr($sectionName["NAME"], 4, strlen($sectionName["NAME"])-4));
        $sectionName = strtolower($sectionName["NAME"]);
        //$sectionName = upFirstLetter($sectionName);
        //$sectionName = iconv('utf-8', 'windows-1251', $sectionName);
        //$sectionName = mb_convert_encoding($sectionName,"UTF-8","Windows-1251"); 
        $category = $categories->addChild('category', $sectionName);
        $id_category = $category->addAttribute('id', trim($parent["XML_ID"]));
      }
     
  //}
	$i++;
  //if($i>10) break;
	$res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"));
  $price=CPrice::GetBasePrice($obEl["ID"]);
  if ( $ob = $res->GetNext() ){
      $offer = $offers->addChild('offer');
      $id = $offer->addAttribute('id', $obEl["XML_ID"]);
      $available = $offer->addAttribute('available', 'true');
      $price = $offer->addChild('price', $price["PRICE"]);
      $currencyId = $offer->addChild('currencyId', "RUR");
      $categoryId = $offer->addChild('categoryId', $parent["XML_ID"]);
      $url = $offer->addChild('url', "https://yugkabel.ru/products/".$obEl["XML_ID"]."/");
      $name = $offer->addChild('name', $obEl["NAME"]);
      $picture = $offer->addChild('picture', "https://yugkabel.ru/img2/".getSubDirImg($obEl["XML_ID"])."/".$obEl["XML_ID"].".jpg");
      $firm = $offer->addChild('vendor', $obEl["PROPERTY_PROIZVODITEL_FILTER_VALUE"]);
      if($ob['VALUE'] !="") $vendorCode = $offer->addChild('model', $ob['VALUE']);
      if($ob['VALUE'] !="") $vendorCode = $offer->addChild('vendorCode', $ob['VALUE']);
      //echo $obEl["XML_ID"]."<br>\n";
    }
    $artikuls[] = $obEl["XML_ID"];
}
//echo $i;
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
function upFirstLetter($str, $encoding = 'UTF-8'){
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