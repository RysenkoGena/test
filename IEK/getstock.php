<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
ob_end_flush(); //отключить буферизацию
$vendor = "ИЭК";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4, $arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
echo "Найдено товаров: ".$list->SelectedRowsCount()."<br>";
$artikuls = array(); $ids = array();
        While($obEl = $list->GetNext()){
            $ob = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul"))->GetNext();
            $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
            $ids["ID"][trim($ob['VALUE'])] = $obEl["ID"];
            $ids["XML_ID"][trim($ob['VALUE'])] = $obEl["XML_ID"];
         }
?><pre><? //print_r($artikuls); ?></pre><?



//$string = implode(",", $artikuls);
//load($string);

echo PHP_EOL.$string.PHP_EOL;

echo "Начало цикла перебора\n\r";
$ii=0;
$stocks = array();
foreach ($artikuls as $kod => $artikul){
    if($artikul != ""){
	    $artikul=trim($artikul);
        if($ii == 0) $text = $artikul;
        else $text .= ("," . $artikul);
	    echo $ii++." ".$kod.PHP_EOL;
	    //echo " ".load($artikul).PHP_EOL;
        if($ii >= 100){
            $ii = 0;
            $stocks = $stocks + load($text);
        }
	}
}


  echo "text=".PHP_EOL.$text;
  //print_r($stocks);
  echo PHP_EOL.count($stocks);
  $ii = 0;
  foreach($stocks as $key => $external_count){
    $ii++;
    if ($external_count >= 0 && CIBlockElement::SetPropertyValueCode($ids["ID"][$key], "vneshniy_sklad", $external_count)) echo "Изменен товар: ".$ids["XML_ID"][$key]." ".$key." ".$external_count." шт.<br>".PHP_EOL;
    //if($ii > 10) break;
  }
    
function load($artikul){
    $username='169-20180718-113601-229';
    $password=':o8IaX_1a0;D8Gh;';
    $URL='https://lk.iek.ru/api/residues/json/?sku='; 
    //$URL='https://lk.iek.ru/api/residues/json/?sku=SVA30-3-0250,BKP10-6-K01';
    //echo $URL.$artikul.PHP_EOL;
    //echo $artikul;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$URL.$artikul);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $result=curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
    curl_close ($ch);
    $text = json_decode($result);

    //var_dump($text->shopItems);
    $stocks = array();

    foreach($text->shopItems as $item){
        $artikul = $item->sku;
        $she = $item->residues->{"47585e53-0113-11e0-8255-003048d2334c"};
        $che = $item->residues->{"238cf439-2a81-11ec-a958-00155d04ac08"};
        $ya =  $item->residues->{"aeef2063-c1e7-11d9-b0d7-00001a1a02c3"};
        //echo ($she + $che + $ya).PHP_EOL;
        $stocks[$artikul] = ($she + $che + $ya);
    }
    return $stocks;
}
?>
