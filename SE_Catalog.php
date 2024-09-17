<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo "Модуль ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"." загружен.\r\n";

function load($artikul){
	$method="GET";                                          // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'https://web.se-ecatalog.ru/';                      // ip адрес или доменное имя сервера, куда шлем данные
	$serv_page = 'new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=';
	
	$request = $serv_addr.$serv_page.$artikul;
  
  //$server_answer = file_get_contents('https://web.se-ecatalog.ru/new-api/JSON/getdata?request=&accessCode=oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ&commercialRef=IMT35090');
  $server_answer = file_get_contents($request);
	$obj=json_decode($server_answer);
	$etim="<h1>".$obj->data[0]->etim->etim6->class->descriptionRu."</h1>";
	foreach($obj->data[0]->etim->etim6->features as $key ){
		if($key->value=="" || $key->value=="NA" || $key->value=="false" || $key->value=="UN" || $key->valueDescriptionRu=="Прочее" || $key->value=="true" ) continue;
		if($key->description!=""){
			if($key->valueDescriptionRu!="")    $etim.= "<dl><dt><div class=box>".$key->description."</div></dt><dd><div class=box>".$key->valueDescriptionRu." ".$key->unit->description_ru."</div></dd></dl>";
			else $etim.= "<dl><dt><div class=box>".$key->description."</div></dt><dd><div class=box>".$key->value." ".$key->unit->description_ru."</div></dd></dl>";
		}
	}
	return $etim;
}

$array = array();
// $file_array = file("iek.txt");
//  if(!$file_array) echo "Ошибка открытия файла iek.txt";
//  else {
// 		for($i=0; $i < count($file_array); $i++)
// 		{
// 			$tex  = "".$file_array[$i]."";
// 			$pieces = explode(";", $tex);
// 			$array[$pieces[0]]=$pieces[1];
// 		}
//  }	 
//print_r($array);
$ii=0;

$vendor = "Schneider Electric";
$arrFilter = Array("PROPERTY_proizvoditel_filter"=>$vendor, "ACTIVE"=>"Y");
$sectFilter = Array("IBLOCK_ID"=>4,$arrFilter);
$list = CIBlockElement::GetList(Array(),$sectFilter, false, false, Array("ID", "XML_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"));
echo "Найдено товаров: ".$list->SelectedRowsCount()."\n";

 $i=0; $artikuls=array();
 While($obEl = $list->GetNext()){
   $res = CIBlockElement::GetProperty(4, $obEl["ID"], "sort", "asc", array("CODE"=>"artikul", ));
     if ( $ob = $res->GetNext() ) $artikuls[$obEl["XML_ID"]] = $ob['VALUE'];
      echo $ob['VALUE']."\n\r";
      $i++;
      // if ($i>3) break;
        //$arFilter = Array("IBLOCK_ID"=>4, "CODE"=>$kod);
        $arFilter = Array("IBLOCK_ID"=>4, "ID");
     	  $res_ = CIBlockElement::GetList(Array(), $arFilter);
    	  if ($ob_ = $res_->GetNextElement()){
      		$arFields = $ob_->GetFields(); // поля элемента
     		  // $arFields = $ob_->TIMEST; // поля элемента
     		echo $arFields[ID];
        	  //  print_r($arFields);
             //	print_r($arProps);
    		  $el = new CIBlockElement;
    		  $res_ = $el->Update($obEl["ID"], Array("DETAIL_TEXT"=>load($ob['VALUE'])));
        }
 }
?>