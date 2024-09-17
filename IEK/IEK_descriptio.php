<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo "Модуль ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"." загружен.\r\n";

function load($artikul){
	$method="GET";                                          // "POST" передача данных методом POST, "GET" методом GET
	$serv_addr = 'web.se-ecatalog.ru';                      // ip адрес или доменное имя сервера, куда шлем данные
	$serv_port = 80;                                        // номер порта
	$serv_page = 'new-api/JSON/getdata';                    // серверный скрипт принимаюший запрос
	$timelimit = 130;                                       // время ожидания ответа в сек., по умолчанию - 30 сек.

	/* передаваемые данные в формате: название переменной => значение */
	$data = array(
  'request' => $json_str,        //! ЕСЛИ НУЖНО ОТПРАВИТЬ XML, ТО ЗАМЕНЯЕМ
                                 //! НА ДРУГУЮ ПЕРЕМЕННУЮ,Т.Е. НА $xml_str
  'accessCode' => 'oZNy8Tj51SWYh1VuDWDx4MjPc5MHi7pZ',
  'commercialRef' => $artikul
   );
                               /* генерируем строку с запросом */
     $post_data_text = '';
     foreach ($data AS $key => $val)
     $post_data_text .= $key.'='.urlencode($val).'&';

                                 /* убираем последний символ & из строки $post_data_text */
     $post_data_text = substr($post_data_text, 0, -1);
                                 /* прописываем заголовки, для передачи на сервер последний заголовок должен быть обязательно пустым,
                                 так как тело запросов отделяется от заголовков
                                 пустой строкой (символом перевода каретки "\r\n") */
                                //echo $post_data_text."<br>";
             // заголовок для метода POST
     $post_headers = array(
         'POST /'.$serv_page.' HTTP/1.1',
         'Host: '.$serv_addr,
         'Content-type: application/x-www-form-urlencoded charset=utf-8',
         'Content-length: '.strlen($post_data_text),
         'Accept: */*',
         'Connection: Close',
     '');
             // заголовок для метода GET
         $get_headers = array(
            'GET /'.$serv_page.'?'.$post_data_text.' HTTP/1.1',
            'Host: '.$serv_addr,
            'Accept: */*',
            'Connection: Close',
         '');

             if ($method=="POST") {
                   $headers=$post_headers;
             }
               if ($method=="GET") {
                 $headers=$get_headers;
             }
             /* сложим элементы массива в одну переменную $headers_txt
             /* и добавим в конец каждой строки, знак "\r\n" - перевода каретки */
                     $headers_txt = '';
         foreach ($headers AS $val) {
               $headers_txt .= $val.chr(13).chr(10);
         }

               // при POST запросе в конец заголовка добавляем наши данные
               // для GET нет данной необходимости, т.к. данные уже в заголовке
          if ($method=="POST") {
                     $headers_txt = $headers_txt.$post_data_text.chr(13).chr(10).chr(13).chr(10);
         }
             // открытие сокета
             $sp = fsockopen($serv_addr, $serv_port, $errno, $errstr, $timelimit);
             // в случае ошибки, вернем ее
             if (!$sp)  exit('Error!: '.$errstr.' #'.$errno);
//            echo $headers_txt;
                   // передача HTTP заголовка
             fwrite($sp, $headers_txt);
             // если соединение, открытое fsockopen() не было закрыто сервером
             // код while(!feof($sp)) { ... } приведет к зависанию скрипта
             // в коде ниже - эта проблема решена
             $server_answer = '';
             $server_header= '';
             $start = microtime(true);
             $header_flag = 1;
             while(!feof($sp) && (microtime(true) - $start) < $timelimit) {
                if ($header_flag == 1) {
                    $content = fgets($sp, 4096);
                    if ($content === chr(13).chr(10))     $header_flag = 0;
                    else   $server_header .= $content;
                }
                else {
                 $server_answer .= fread($sp, 4096);
                }
             }
        // закрываем дескриптор $sp
         fclose($sp);
                                //для отладки, раскомментируйте строку ниже
  //                              echo $server_header."<br><br>";
	$server_answer = substr($server_answer, strpos($server_answer, "{"));
	$server_answer = substr($server_answer, 0, strrpos($server_answer, "}")+1);
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
//$etim.="</dl>";
//echo $etim;
$array = array();
$file_array = file("iek.txt");
 if(!$file_array) echo "Ошибка открытия файла iek.txt";
 else {
		for($i=0; $i < count($file_array); $i++)
		{
			$tex  = "".$file_array[$i]."";
			$pieces = explode(";", $tex);
			$array[$pieces[0]]=$pieces[1];
		}
 }	 
//print_r($array);
$ii=0;
echo "\n\rРабота с XML";
$doc = new DOMDocument;
$doc->load('Description.xml');
$xpath = new DOMXPath($doc);

echo "Начало цикла перебора\n\r";
foreach ($array as $kod => $artikul){
	  $artikul=trim($artikul);
	  $ii++;
	  $arFilter = Array("IBLOCK_ID"=>4, "CODE"=>$kod);
	  $res = CIBlockElement::GetList(Array(), $arFilter);
	  if ($ob = $res->GetNextElement()){
  		$arFields = $ob->GetFields(); // поля элемента
      //	    $arFields = $ob->TIMEST; // поля элемента
      //		echo $arFields[ID];
      //	    print_r($arFields);
		  $arProps = $ob->GetProperties(); // свойства элемента
      //    	print_r($arProps);
		  $el = new CIBlockElement;
      $products = $xpath->query("/NewDataSet/Описание[Article='".$artikul."' and Язык_Name='русский ']/Описание");
      foreach ($products as $product) $a = $product->nodeValue;	
		  echo $ii." ".$kod." ".$artikul." => ".$a."\n\r";	
		  $res = $el->Update($arFields[ID], Array("DETAIL_TEXT"=>$a));
	  }
} 
?>