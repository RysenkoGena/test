<? IncludeModuleLangFile(__FILE__);
ini_set("mbstring.func_overload","2");
ini_set("mbstring.internal_encoding","UTF-8");
use Bitrix\Sale;
CModule::includeModule("iblock");
CModule::includeModule("catalog");
CModule::includeModule("sale");

class CIExchange{
	public function ImportGoods($startTime=0){
		global $tmpfile;
		global $tmpImg;

		$processfile = false;
		$modulePath = self::getModulePath();
		$CATALOG_ID = self::getGoodId();

		$progressFile = $modulePath."/progress/progress.xml";
		$tmpFile = $modulePath."/tmp.txt";
		$tmpImg =  $modulePath."/tmpImg.txt";
		//file_put_contents($tmpFile, "начало ImportGoods\n", FILE_APPEND);
		$tmpFileUsers_ = $_SERVER['DOCUMENT_ROOT']."inc/users/";
		$passElements = (int) file_get_contents($progressFile); // получаем обрабатываемого элемента в файле

		// try to find in progress
		$dir = opendir($modulePath."/progress/"); $fileTime = 0;
		while($el = readdir($dir)){									//Получаем путь самого раннего файла в переменную $processfile
			if($el != "." && $el != ".." && is_file($modulePath."/progress/".$el) && self::is_xml($modulePath."/progress/".$el)){
				file_put_contents($tmpFile, date("Y-m-d H:i:s")."есть файл в progress! "."\n", FILE_APPEND);
				$fileTimePre = filectime($modulePath."/progress/".$el); //получить дату файла
				if($fileTime == 0){
					$processfile = $modulePath."/progress/".$el;
					$fileTime = filectime($processfile);
				}
				elseif($fileTime > $fileTimePre){
					$processfile = $modulePath."/progress/".$el;
					$fileTime = $fileTimePre;
				}
			}
		}
		closedir($dir);
		
		$fileTime = 0;
		// try to find in upload
		if(!$processfile){ //если в каталоге PROGRESS нет обрабатываемого файла, перемещаем туда очередной файл из UPLOAD
			$pathUpload = "/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/upload/";
			$dir = opendir($pathUpload);
			while($el = readdir($dir)){
				if($el != "." && $el != ".." && is_file($modulePath."/upload/".$el)){
					if(strpos($el, "ord") === 0){ // Заказы должны обрабатываться в первую очередь
						$el1 = $el;
						break;
					}
					if(strpos($el, "Users") === 0){ // Пользователи должны обрабатываться во вторую очередь
						$el1 = $el;
						break;
					}
					$fileTimePre = filectime($pathUpload.$el); // В последнюю очередь обрабатываем самый старый файл с товарами
					if($fileTime == 0){
						$processfile = $pathUpload.$el;
						$fileTime = filectime($processfile);
						$el1 = $el;
					}
					else{
						if($fileTime > $fileTimePre){
							$processfile = $pathUpload.$el;
							$fileTime = $fileTimePre;
							$el1 = $el;
						}
					}
				}
			}
			closedir($dir);
			if($fileTime > 0) file_put_contents($modulePath."/actualTimeExchange.txt", date("d.m.Y H:i:s", $fileTime)); // информация для отображения актуальной даты
			if(is_file($pathUpload.$el1)) if(rename($pathUpload.$el1, $modulePath."/progress/".$el1))	$processfile = $modulePath."/progress/".$el1;
			file_put_contents($modulePath."/date_start.xml", date("d.m.Y H:i:s"));
		}
		if($processfile){
			$sTime = time();
			$xml = simplexml_load_file($processfile);
			if($progressFile){
				//file_put_contents($tmpFile, "Обмен\n", FILE_APPEND);										//-------- Начало обработки заказов===============================
				for($ordern=$passElements; $ordern<sizeof($xml->заказ); $ordern++){ //Перебор заказов в файле
					$order = $xml->заказ[$ordern];
					$id = trim((string)$order->ID);
					file_put_contents($tmpFile, date("Y-m-d H:i:s")." Смотрим заказ №".$id."\n", FILE_APPEND);

					if(strlen($id) < 8){
						//file_put_contents($tmpFile, "Короткий номер: ".$id."\n", FILE_APPEND);
						//$arOrder = CSaleOrder::GetByID($id);
						if($id < 47866)	$rOrder =  CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), array("XML_ID"=>$id)); //новая нумерация заказов до 03.03.21 (...№16104)
						if($id >= 47866)	$rOrder =  CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), array("ID"=>$id)); //новая нумерация заказов с 12.03.21 (№48043...)47866
					}
					else{
						//file_put_contents($tmpFile, "else\n", FILE_APPEND);
						$rOrder =  CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), array("XML_ID"=>$id));
						//$rOrder =  CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), array("ID"=>$id)); //устарел!
					}
					$sklad = (string)$order->Склад;
					while($arOrder = $rOrder->Fetch()){ //Заказ на сайте найден - обновим его свойства (Создание нового заказа ниже)
						file_put_contents($tmpFile, print_r($arOrder, true), FILE_APPEND);
						$nomer = $order->НомерНакладной;

						$arFields = Array();
						if($order->Статус){//Изменим статус заказа на новый, присланный из 1с
							$arFields["STATUS_ID"] = (string)$order->Статус;
							$arFields["EMP_STATUS_ID"] = 1;
						}

						if($order->Оплачено=="Y" || $order->Статус == "F"){
							if($order->Оплачено){	//Изменим статус оплаты из 1с
								$arFields["PAYED"] = (string)$order->Оплачено;
							}
							if($order->ДатаОплаты){	//Изменим дату оплаты из 1с
								$arFields["DATE_PAYED"] = (string)$order->ДатаОплаты;
							}
							if($order->СуммаОплаты){//Сумма оплаты из 1с
								$arFields["PRICE"] = (string)$order->СуммаОплаты;
								$arFields["SUM_PAID"] = (string)$order->СуммаОплаты;
							}
						}
						if($order->Дата)	$arFields["DATE_INSERT"] = (string)$order->Дата;

						if((string)$order->Отменен){	//Изменим статус отмены заказа из 1с
							$arFields["CANCELED"] = (string)$order->Отменен;
							$arFields["EMP_CANCELED_ID"] = 1;
						}

						if($order->ДатаОтменены){	//Изменим дата отмены заказа из 1с
							$arFields["DATE_CANCELED"] = (string)$order->ДатаОтменены;
						}
						if($order->ПричинаОтменены){//Причина отмены заказа из 1с
							$arFields["REASON_CANCELED"] = (string)$order->ПричинаОтменены;
						}
						if($order->РазрешенаДоставка){//Разрешена ли доставка заказа из 1с
							$arFields["ALLOW_DELIVERY"] = (string)$order->РазрешенаДоставка;
						}

						if($order->СпособОплаты){			//Изменение способа оплаты из 1с
							$arFields["PAY_SYSTEM_ID"] = (string)$order->СпособОплаты;
						}
						if($order->ДоставкаСамовывоз=="Y"){	//Изменение способы доставки из 1с (самовывоз)
							$arFields["DELIVERY_ID"] = "2";
							$arFields["PRICE_DELIVERY"] = "0";
						}else{
							$arFields["DELIVERY_ID"] = "16";
							$arFields["PRICE_DELIVERY"] = $order->Ценадоставки;
						}
						if($order->ТранспортнаяКомпания){	//Изменение способы доставки из 1с
							$arFields["DELIVERY_ID"] = (string)$order->ТранспортнаяКомпания;
						}
						if($order->ИнформацияПоЗаказу){		//Информация по заказу из 1с
							$arFields["ADDITIONAL_INFO"] = (string)$order->ИнформацияПоЗаказу;
						}
						if($order->СкидкаПоПромоКоду){		//Информация по заказу из 1с
							//$arFields["ADDITIONAL_INFO"] = (string)$order->ИнформацияПоЗаказу;
						}
						if($order->ПолнаяСтоимостьЗаказа){	//Информация по заказу из 1с
							$arFields["PRICE"] = (string)$order->ПолнаяСтоимостьЗаказа;
						}
					   	if($nomer!="" && !is_object($nomer) && $arOrder['TRACKING_NUMBER']==""){
					   		$arFields['TRACKING_NUMBER'] = $nomer;
					   	}

					  	$or_sales = $arOrder;//CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), Array("XML_ID"=>$id))->Fetch();
					  	
						if($order->СписанныйБонус){			//СписанныйБонус
							$prop_id = '24';
							if (CModule::IncludeModule('sale')) {
							    if ($arOrderProps = CSaleOrderProps::GetByID($prop_id)) {
							      $db_vals = CSaleOrderPropsValue::GetList(array(), array('ORDER_ID' => $or_sales["ID"], 'ORDER_PROPS_ID' => $arOrderProps['ID']));
							      if ($arVals = $db_vals -> Fetch()) {
							        CSaleOrderPropsValue::Update($arVals['ID'], array(
							          'ORDER_PROPS_ID' => $arVals['ORDER_PROPS_ID'],
							          'ORDER_ID' => $arVals['ORDER_ID'],
							          'VALUE' => (string)$order->СписанныйБонус,
							        ));
							      } else {
							        CSaleOrderPropsValue::Add(array(
							          'ORDER_PROPS_ID' => $arOrderProps['ID'],
							          'ORDER_ID' => $_POST["order"],
							          'VALUE' => (string)$order->СписанныйБонус,
							        ));
							      }
							    }
							}
							$arFields["ADDITIONAL_INFO"] = (string)$order->СписанныйБонус;
						}
					  	if($or_sales["ID"]) $bas_id = $or_sales["ID"];
					   	// Собираем то, что сейчас есть в корзине
					   	$orderArr = Array();
					   	$basketActual = CSaleBasket::GetList(Array(),Array("ORDER_ID"=>$bas_id));
					   	while($el = $basketActual->GetNext()){
							//file_put_contents($tmpFile, print_r($el, true), FILE_APPEND);
					   		$orderArr[$el['PRODUCT_ID']] = $el['ID'];
					   	}
					   	$delArr = $orderArr;

					   	// Сравниваем с товарами
						if($order->Статус=='N' || $order->Статус=='NS' || $order->Статус=='OS' || $order->Статус=='RS' || $order->Статус=='F'){
							//file_put_contents($tmpFile, "Статус: ".$order->Статус."\n", FILE_APPEND);
							$orderPrice = 0;
							if($order->Товары->Товар){
							   	foreach($order->Товары->Товар as $ordergood){
									$orderPrice += (float)$ordergood->СуммаСНДС;
									if((int)$ordergood->Код == 2460) continue; //не добавлять в список товаров доставку
							   		$arr1 = self::getElsByCode($ordergood->Код);
							   		$pid = $arr1[0]['ID'];
									//file_put_contents($tmpFile, "Товар: ".$pid."\n", FILE_APPEND);
							   		if($pid){
							   			$good = CIBlockElement::GetById($pid)->GetNext();
							   			unset($delArr[$pid]);
							   			
							   			if(isset($orderArr[$pid])){// Если товар был в корзине
											//file_put_contents($tmpFile, "Меняем позиции! товар был в корзине. Status=".$orderArr['STATUS_ID']."\n", FILE_APPEND);
							   				$bid = $orderArr[$pid];// Пометили, что он был и в обмене
							   				//unset($orderArr[$pid]);
							   				if(($order->Статус == 'N' || $order->Статус == 'RS') ){
								   				CSaleBasket::Update($bid,Array(
								   					"QUANTITY"=>(float) $ordergood->Количество,
								   					"PRICE"=>(float) $ordergood->Цена,
								   					"PRODUCT_XML_ID" => (string) $ordergood->Код,
								   				));
							   				}
							   			}
										else{
											//file_put_contents($tmpFile, "Меняем позиции! Товара не было в корзине\n", FILE_APPEND);
											$pict = CFile::GetPath($good['PREVIEW_PICTURE']);
							   				$arField = array(
											    "PRODUCT_ID" => $pid,
											    "PRODUCT_XML_ID" =>  $ordergood->Код,								
											    "PRICE"=>(float) $ordergood->Цена,
											    "CURRENCY" => "RUB",
											    "QUANTITY"=>(float) $ordergood->Количество,
											    "LID" => "s1",
											    "DELAY" => "N",
											    "ORDER_ID" => $bas_id,
											    "CAN_BUY" => "Y",
											    "NAME" => $good['NAME'],
											    "PREVIEW_PICTURE" => $pict,
											    "DETAIL_PAGE_URL" => $good['DETAIL_PAGE_URL']
											);
											$bid1 = Add2BasketByProductID($pid);
											CSaleBasket::Update($bid1, $arField);	

											CSaleBasket::Update($bid1,Array(
							   					"QUANTITY"=>(float) $ordergood->Количество,
							   					"PRICE"=>(float) $ordergood->Цена,
							   					"PRODUCT_XML_ID" => (string) $ordergood->Код,
							   				));
										}   			
							   		}else{
										//Добавляем товар в корзину
										$iel = new CIBlockElement;
										$arr = Array(
											"IBLOCK_ID"=>self::getGoodId(), // = 4
											"NAME"=>$ordergood->Наименование,
											"CODE"=>self::translit($ordergood->Наименование),
											"ACTIVE"=>"N",
										);
										$gid = $iel->Add($arr);
										print $iel->LAST_ERROR;
										self::setQuantity($gid, $ordergood->Количество);
										self::setPrice($gid, $ordergood->Цена);

						   				$arField = array(
										    "PRODUCT_ID" => $gid,
										    "PRODUCT_XML_ID" =>(string)  $ordergood->Код,								
										    "PRICE"=>(float) $ordergood->Цена,
										    "CURRENCY" => "RUB",
										    "QUANTITY"=>(float) $ordergood->Количество,
										    "LID" => "s1",
										    "DELAY" => "N",
										    "ORDER_ID" => $bas_id,
										    "CAN_BUY" => "Y",
										    "NAME" => (string)  $ordergood->Наименование,
										);
										$bid1 = CSaleBasket::Add($arField);	
							   			unset($delArr[$pid]);
							   		}
							   	}
							   	foreach ($delArr as $key => $value) {
							   	 	CSaleBasket::Delete($value);
							   	}
							}
						}
					   	$arFields["USER_ID"] = $arOrder["USER_ID"];
				   		$ord = new CSaleOrder;
				   		$upd = "";
						//file_put_contents($tmpFile, print_r($arFields, true)."\n", FILE_APPEND);
				   		foreach($arFields as $key=>$val){
				   			if(preg_match("#DATE#", $key)) $val = date("Y-m-d H:i:s", strtotime($val));
				   			$upd .= ", ".$key."='".$val."'";
				   		}
				   		$upd = substr($upd, 1, strlen($upd));
				   		$query = "UPDATE b_sale_order SET ".$upd." WHERE ID=".$bas_id; //Обновляем заказы через SQL?? ДА!
						//file_put_contents($tmpFile, $query."\n", FILE_APPEND);  
				   		global $DB;
				   		$DB->Query($query);

						   $ordr = \Bitrix\Sale\Order::load($bas_id);					// обновление дополнительных свойств заказов
						   $collection = $ordr -> getPropertyCollection();
						   //$propertyValue = $collection -> getItemByOrderPropertyId('23'); //свойство '23' = "склад выдачи"
						   //$r = $propertyValue->setField('VALUE', (string)$order->Склад);
						   $propertyValue = $collection -> getItemByOrderPropertyId('27'); //свойство '27' = "начисленный бонус"
						   $r = $propertyValue->setField('VALUE', (string)$order->НачисленныйБонус);
						   $ordr->save();
						   file_put_contents($tmpFile, "Сумма заказа посчитанная из товаров в файле: ".$orderPrice."\n", FILE_APPEND);
						   $fileLinkPayment = $_SERVER['DOCUMENT_ROOT']."/bitrix/php_interface/include/sale_payment/sbr_eltek/links/".$arOrder['ID'].".txt";
						   if($orderPrice != $arOrder["PRICE"] && file_exists($fileLinkPayment)) unlink($fileLinkPayment);
				   	}

				   	if($rOrder->SelectedRowsCount() < 1){ //Заказа на сайте нет - создадим его (изменение старого заказа выше)
						file_put_contents($tmpFile, date("Y-m-d H:i:s")." Создаем заказ ".$id." для клиента ".(string)$order->IDпользователя."\n", FILE_APPEND);
				   		$User = CUser::GetList(($by="id"),($sort="desc"),Array("UF_ID"=>(string)$order->IDпользователя))->GetNext();
						if($User["ID"]){
							$userId = $User["ID"];
							file_put_contents($tmpFile, date("Y-m-d H:i:s")." Клиент есть: (id) ".$userId."\n", FILE_APPEND);
							$FUSER_ID = CSaleUser::GetList(array('USER_ID' => $userId)); //получаем FUSER_ID, если покупатель для данного пользователя существует
							if(!$FUSER_ID['ID']){ // если покупателя нет - создаем его
								   $FUSER_ID = CSaleUser::_Add(array("USER_ID" => $userId));
								   file_put_contents($tmpFile, date("Y-m-d H:i:s")." Покупателя нет для клиента с id ".$userId." ".(string)$order->IDпользователя.". Cоздадим! FUSER_ID = ".$FUSER_ID."\n", FILE_APPEND);
							}
							$FUSER_ID = $FUSER_ID['ID'];
							file_put_contents($tmpFile, date("Y-m-d H:i:s")." FUSER_ID = ".$FUSER_ID."\n", FILE_APPEND);
							if($order->ДоставкаСамовывоз=="Y"){	//Изменение способа доставки из 1с (самовывоз)
								$delid = "2";
							}
							elseif((string)$order->ТранспортнаяКомпания){
								$delid = (string)$order->ТранспортнаяКомпания;
							}
							else	$delid = "2";

							//Создаем заказ
							$order_id = CSaleOrder::Add( 
							 array(
							   "LID" => "s1",
							   "DATE_INSERT"=> (string)$order->Дата,
							   "USER_ID" => $userId,
							   "PERSON_TYPE_ID" => 1,
							   "XML_ID"=>(string)$order->ID,
							   //"XML_ID"=>(string)$order->XML_ID,
							   "PAYED" =>"Y",
								//"PS_STATUS"=>"Y",
								//"SUM_PAID"=>(string)$order->СуммаОплаты,
							   "DATE_PAYED" =>(string)$order->ДатаОплаты,
							   "CANCELED" =>(string)$order->Отменен,
							   "STATUS_ID" =>(string)$order->Статус,
							   "CURRENCY" => "RUB",
							   "PAY_SYSTEM_ID" =>(string)$order->СпособОплаты,
								//"PS_SUM" =>(string)$order->СуммаОплаты,
								//"PS_CURRENCY"=>"RUB",
							   "DELIVERY_ID" => $delid,
							   "EXTERNAL_ORDER"=>"Y",
							   "ADDITIONAL_INFO" =>(string)$order->XML_ID
								)
							);
							file_put_contents($tmpFile, date("Y-m-d H:i:s")." Тут заказ должен быть создан. Номер заказа: ".$order_id."\n", FILE_APPEND);
							$ordr = \Bitrix\Sale\Order::load($order_id);	// добавляем свойство "склад выдачи"
							$collection = $ordr -> getPropertyCollection();
							
							$propertyValue = $collection -> getItemByOrderPropertyId('23'); //свойство '23' = "склад выдачи"
							$r = $propertyValue->setField('VALUE', (string)$order->Склад);
							
							$propertyValue = $collection -> getItemByOrderPropertyId('27'); //свойство '27' = "начисленный бонус"
							$r = $propertyValue->setField('VALUE', (string)$order->НачисленныйБонус);
							//file_put_contents($tmpFile, date("Y-m-d H:i:s")." Сохраням заказ: (id) ".$order_id."\n", FILE_APPEND);
							$ordr->save();
							$basketActual = CSaleBasket::GetList(Array(),Array("FUSER_ID" => $FUSER_ID, "ORDER_ID" => "NULL", "LID" => SITE_ID), false, false, array("ID"));
							while($el = $basketActual->GetNext()){
								CSaleBasket::Delete($el['ID']); // Тут необходимо очистить корзину покупателя, так как если она изначально не пуста к офлайн заказу добавятся лишние товары
								 // потом надо бы вернуть удаленные товары обрато в корзину, чтоб клиент не заругал что мы трогали его корзину без его ведома, но я это пока не умею
							}
							foreach($order->Товары->Товар as $prod){ //Добавляем товары в заказ
								$ss = CIBlockElement::GetList(Array(),Array("IBLOCK_ID" => $CATALOG_ID, "XML_ID"=>(string)$prod->Код))->GetNext();
								if($ss["ID"]){ // товар на сайте найден
							            $product = array(//Добавляем товар в заказ
								            'PRODUCT_ID' => $ss["ID"],
											'PRODUCT_XML_ID' => (string)$prod->Код,
								            'PRICE' => (string)$prod->Цена,
											'CURRENCY' => 'RUB',
											'QUANTITY' => (string)$prod->Количество,
											'LID' => 's1',
											'NAME' =>  $ss["NAME"],
								    		'ORDER_ID' => $order_id,
											'DETAIL_PAGE_URL' => $ss["DETAIL_PAGE_URL"],
										);
							            CSaleBasket::Add($product);
										//file_put_contents($tmpFile, date("Y-m-d H:i:s")." Добавляем не новый товар в корзину: ".$ss["NAME"]."\n", FILE_APPEND);
								}
							    else { //Товара на сайте нет - создадим его на сайте
									$iel = new CIBlockElement;
									$arr = Array(
										"IBLOCK_ID" => self::getGoodId(), // = 4
										"NAME" => (string)$prod->Наименование,
										"ACTIVE"=>"N",
									);
									$gid = $good = $iel->Add($arr);
									self::setQuantity($gid, $prod->Количество);
									self::setPrice($gid, $prod->Цена);
								    $product = array(	//Добавляем товар в заказ
					            		'PRODUCT_ID' =>(string)$gid,
						            	'PRICE' =>(string)$prod->Цена,
						            	'CURRENCY' => 'RUB',
						            	'QUANTITY' =>(string)$prod->Количество,
						            	'LID' => 's1',
						            	'NAME' =>(string)$prod->Наименование,
						            	'ORDER_ID' => $order_id,
									);
							        CSaleBasket::Add($product);		//file_put_contents($tmpFile, date("Y-m-d H:i:s")." Добавляем прям новый товар в корзину: ".(string)$prod->Наименование."\n", FILE_APPEND);
						        }
								CSaleBasket::OrderBasket($order_id, $FUSER_ID, 's1');//Связываем корзину и товары
							}
							if((string)$order->Оплачено == "Y"){ //добавляем к заказу
                            	$ordr = \Bitrix\Sale\Order::load($order_id);
								$paymentCollection = $ordr->getPaymentCollection();
                             	foreach ($paymentCollection as $payment) {
                                 	if(!$payment->isPaid())    	$payment->setFields(['PAID' => 'Y']);
									//file_put_contents($tmpFile, date("Y-m-d H:i:s")." Снова сохраням заказ: (id) ".$order_id."\n", FILE_APPEND);
                                 	$ordr->save();
                             	}
							}
						}
						else file_put_contents($tmpFile, date("Y-m-d H:i:s")." Пользователь не найден с таким UF_ID \n", FILE_APPEND);
				   	}
					$passElements ++; 	// increment progress

					// save progress
					if(time() >= $sTime+self::getTimeout()){
						file_put_contents($progressFile, $passElements);
						return "CIExchange::ImportGoods();";
					}
				}
				foreach($xml->Подразделения->Подразделение as $podraz){ //адреса выдачи (склады)
					$store = CCatalogStore::GetList(array("TITLE"), array("XML_ID"=>$podraz->Ид))->GetNext();
					if(!$store["TITLE"]){
						$arStore = Array("TITLE" => $podraz->Наименование,"ACTIVE" => "Y","ADDRESS" => $podraz->Наименование,"DESCRIPTION" => "","XML_ID" => $podraz->Ид);
						$ID = CCatalogStore::Add($arStore);
					}
				}
				//file_put_contents($progressorder, "finish");												//-------- Конец обработки заказов

				foreach($xml->клиент as $users){															//----------Пользователи=============================================
					global $USER;
					$uid = (string)$users->ID;
					$user = false;
					if($uid != "СоздатьНового"){
						$user 	=		    CUser::GetList(($by="id"),($sort="desc"),Array("XML_ID"=>$uid),array("SELECT"=>array("UF_BAL")))->GetNext();
						if(!$user) $user = 	CUser::GetList(($by="id"),($sort="desc"),Array("UF_ID"=> $uid),array("SELECT"=>array("UF_BAL")))->GetNext();
					}
					if(!$user){ //создадим нового пользователя
						if($uid != "" && $uid != "СоздатьНового") file_put_contents($tmpFile, date("r")."Не найден код пользователя: ".$uid."\n", FILE_APPEND);
						if($uid == "СоздатьНового") file_put_contents($tmpFile, date("r")."Создаем нового пользователя: \n", FILE_APPEND);
						$newUser = new CUser;
						$arFields = Array(
						  "NAME"              => $users->Имя,
						  "LAST_NAME"         => $users->Фамилия,
						  "SECOND_NAME"		  => $users->Отчество,
						  "EMAIL"             => $users->EMail,
						  "LOGIN"             => $users->EMail,
						  "PERSONAL_PHONE"	  => (string)$users->Мобильный, 
						  "LID"               => "s1",
						  "ACTIVE"            => "Y",
						  //"GROUP_ID"          => array(10,11),
						  "PASSWORD"          => (string)$users->Пароль,
						  "CONFIRM_PASSWORD"  => (string)$users->Пароль,
						  "UF_ID"			=> (string)$users->Мобильный
						);
						$ID = $newUser->Add($arFields);
						if (intval($ID) > 0){
							//echo "2".$ID;
							$sms_text='Вы зарегистрированы на сайте https://yugkabel.ru Ваш логин: '.$users->Мобильный.' Пароль (не сообщайте никому): '. $users->Пароль;
							file_put_contents($tmpFile, print_r("Пользователь ".$users->Фамилия." ".$users->Имя." успешно добавлен. Текст СМС ".$sms_text, true)."\n", FILE_APPEND);
							$user = CUser::GetList(($by="id"),($sort="desc"),Array("ID"=> $ID),array("SELECT"=>array("UF_BAL")))->GetNext();
							//Тут нужно отправить СМС клиенту с его логином и паролем
							 
							$sms= new BEESMS('krd_ugkabel1','9654711008');
							$sms->post_message($sms_text, "+7".(string)$users->Мобильный, 'YUGKABEL');
						}
						else	file_put_contents($tmpFile, date("d.m.Y H:i:s")." Ошибка создания пользователя: ".$newUser->LAST_ERROR."\n", FILE_APPEND);
					}
					//file_put_contents($tmpFile, "\nпуть1: ".$tmpFileUsers_, FILE_APPEND);
					if($user){
						$userup = new CUser;
						//$uprop = array();
						if((string)$users->КоличествоАктивированныхБонусов){
							$uprop["UF_BAL"]=(string)$users->КоличествоАктивированныхБонусов;
						}else	$uprop["UF_BAL"] = 0;

						if((string)$users->КоличествоНачисленныхБонусов){
							$uprop["UF_NAL"]=(string)$users->КоличествоНачисленныхБонусов;
						}
						else	$uprop["UF_NAL"] = 0;
						
						if((string)$users->ПроцентСкидки){
							$uprop["UF_SALE"]=round((string)$users->ПроцентСкидки);
						}
						else	$uprop["UF_SALE"] = 0;
						if((string)$users->ПроцентНаценки){
							$uprop["UF_NACENKA"]=round((string)$users->ПроцентНаценки);
						}
						else	$uprop["UF_NACENKA"] = "";
						if((string)$users->ПроцентСписанияБонуса){
							$uprop["UF_BPERS"]=round((string)$users->ПроцентСписанияБонуса);
						}
						if((int)$users->БаллыНаКарту == 0){
							$uprop["UF_BAL_NA_KARTU"] = 0;
							//file_put_contents($tmpFile, "Пришло сообщение о баллах на карту ".$users->БаллыНаКарту."\n", FILE_APPEND);
						}
						if($users->ПоследняяДатаКарты != "")
							$uprop["UF_LAST_CARD_DATA"] = $users->ПоследняяДатаКарты;
						//else file_put_contents($tmpFile, date("d.m.Y H:i:s")."НЕ Пришло сообщение о баллах на карту ".$users->БаллыНаКарту."\n", FILE_APPEND);
						if($users->БаллыНаПромокод == 0)	$uprop["UF_BAL_NA_KUPON"] = 0;
						if($users->ПромокодИз1С != "")	$uprop["UF_KUPON_FROM_1C"] = $users->ПромокодИз1С;
						$userup->Update($user[ID], $uprop);

						if(round((string)$user["UF_SALE"])!==round((string)$users->ПроцентСкидки)){
							if((string)$users->ПроцентСкидки){
								$pers = round((string)$users->ПроцентСкидки);
								CUser::SetUserGroup($user["ID"],array(3));
								if($pers>"0"){
									$rsGroup= CGroup::GetList(($by="c_sort"), ($order="desc"), Array("STRING_ID"=>"GROUP_".$pers))->GetNext();
									if($rsGroup)	$NEW_GROUP_ID = $rsGroup['ID'];
									else{
										$group = new CGroup;
										$arFields = Array(
											"ACTIVE"       => "Y",
											"C_SORT"       => 100,
											"NAME"         => "Скидка ".$pers."%",
											"STRING_ID"      => "GROUP_".$pers
										);
										$NEW_GROUP_ID = $group->Add($arFields);
									}
									if($NEW_GROUP_ID){
										$discount = CSaleDiscount::GetList(Array(),Array("XML_ID"=>"g_disc_".$pers))->GetNext();
										if(!$discount && (int) $discount <= 10){
											$arFields = Array(
											    "LID" => "s1",
											    "NAME" => "Скидка ".$pers,
											    "ACTIVE" => "Y",
											    "SORT" => "100",
											    "PRIORITY" => "1",
											    "LAST_DISCOUNT" => 'Y',
											    "XML_ID" => "g_disc_".$pers,
											    "CONDITIONS" => Array(
										            "CLASS_ID" => "CondGroup",
										            "DATA" => Array(
										                "All" => "AND",
										                "True" => "True",
										            ),
										            "CHILDREN" => Array(),
											    ),
												"ACTIONS" => Array(
											        "CLASS_ID" => "CondGroup",
											        "DATA" => Array(
										                "All" => 'AND'
											        ),
											        "CHILDREN" => Array(
											            "0" => Array(
											                "CLASS_ID" => "ActSaleBsktGrp",
											                "DATA" => Array(
										                        "Type" => "Discount",
										                        "Value" => $pers,
										                        "Unit" => "Perc",
										                        "All" => "AND",
										                    ),
											                "CHILDREN" => Array(
												                0=>Array(
													               	"CLASS_ID"=>"CondIBProp:4:61",
												                	"DATA"=>Array(
												                   		"logic"=>"Not",
												                   		"value"=>"1",
												                   	),
												                )
											                ),    
											            ),
											        ),
											    ),
												"USER_GROUPS" => Array(
											        "0" => $NEW_GROUP_ID,
											    )
											);
											$ID = CSaleDiscount::Add($arFields);
										}
									}
									$code_g = 'GROUP_'.$pers;
									$arGroups = CUser::GetUserGroup($user["ID"]);
									$rsGroup = CGroup::GetList(($by="c_sort"), ($order="desc"), Array("STRING_ID" => "GROUP_%"));
									while($gr = $rsGroup->GetNext()){
										foreach($arGroups as $i=>$n) if($n==$gr['ID']) unset($arGroups[$i]);
									}
									$rsGroup1 = CGroup::GetList(($by="c_sort"), ($order="desc"), Array("STRING_ID" => $code_g))->GetNext();
									if($rsGroup1["ID"]){	
										$arGroups[] = $rsGroup1["ID"];
										CUser::SetUserGroup($user["ID"], $arGroups);
									}
								}
							}
						}
						$text = "<table border=0 style='padding:10px;'>"; 
						foreach($users -> БонусыИстория -> БонусИстория  as $History){
							//$text_ahref = ""; $textAfrefEnd="";
							//if((int)$History->БонусНомерТипаДвижения > 47865) {$text_ahref = "<a href=/auth/?ID=".$History->БонусНомерТипаДвижения.">"; $textAfrefEnd = "</a>";}
							$text .= "<tr>";$text .= "<td><span style=''>".date("d.m.Y",strtotime($History->БонусДата))."</span>\n";
							if($History->БонусВидДвижения == "Приход") $text .= "<td align=right><span style='color: #22aa22; font-weight: 600;padding:20px;'>+ ".$History->БонусСумма."</span>";
							if($History->БонусВидДвижения == "Расход") $text .= "<td align=right><span style='color: #aa2222; font-weight: 600;padding:20px;'>- ".$History->БонусСумма."</span>";
							if($History->БонусТипДвижения == "Заказ") $text .= "<td>".$text_ahref."за заказ <span style='font-weight:600; padding:20px;'>№".$History->БонусНомерТипаДвижения."</span>".$textAfrefEnd;
							
							if($History->БонусТипДвижения == "КорректировкаБонуса" && $History->БонусВидДвижения == "Расход" && $History->БонусКомментарийКорректировки == "Автоматическое списание бонуса") $text .= "<td> Сгорание бонусов";
							elseif($History->БонусТипДвижения == "КорректировкаБонуса" && $History->БонусВидДвижения == "Расход") $text .= "<td>".$History->БонусКомментарийКорректировки;
							if($History->БонусТипДвижения == "КорректировкаБонуса" && $History->БонусВидДвижения == "Приход" && $History->БонусНомерТипаДвижения=="000000001") $text .= "<td>Перенос бонусов с прошлых периодов";
							elseif($History->БонусТипДвижения == "КорректировкаБонуса" && $History->БонусВидДвижения == "Приход") $text .= "<td>".$History->БонусКомментарийКорректировки;
							if($History->БонусДатаСписания != "" && strtotime($History->БонусДатаСписания) > strtotime(date("d.m.Y H:i:s"))) $text .= " (сгорят ".date("d.m.Y",strtotime($History->БонусДатаСписания)).")";
						}
						$text .= "</table>";
						file_put_contents($tmpFileUsers_.$uid.".php", $text."\n");
					}
				}
				
				foreach($xml->Разделы->Раздел as $section){													// Разделы sections =============================
					//file_put_contents($tmpFile, "Обрабатываются разделы!".date(), FILE_APPEND);
					$sectObj = new CIBlockSection;
					$parent = self::getSectByCode($section->КодРодителя);
					$sectArr = Array(
						"IBLOCK_ID"=>$CATALOG_ID,
						"ACTIVE"=>"Y",
						"NAME"=>$section->Название,
						"XML_ID"=>$section->Код
					);
					if(trim($sectArr["XML_ID"])=="8997"){
					     $sectArr["ACTIVE"]="N"; //Раздел "ПРОЧЕЕ" сразу делать неактивным, ломает левое меню
					     //file_put_contents($tmpFile, date("d.m.Y H:i:s")."попался раздел ПРОЧЕЕ!".date(), FILE_APPEND);
					}
					if($parent) $sectArr['IBLOCK_SECTION_ID'] = $parent['ID'];
					
					$sect = self::getSectByCode($section->Код);
					if(!$sect){
						//$code = self::translit($sectArr['NAME']);
						$code = trim($sectArr['XML_ID']);
						$sectnum = CIBlockSection::GetList(Array(),Array("IBLOCK_ID"=>$CATALOG_ID,"CODE"=>$code))->SelectedRowsCount();
						if($sectnum > 0) $code = $code . $sectnum;
						$sectArr['CODE'] = trim($sectArr['XML_ID']);
						$id = $sectObj->Add($sectArr);
						//if(!$id) print $sectObj->LAST_ERROR;
					}else				$sectObj->Update($sect['ID'], $sectArr);
				}
			}
			else file_put_contents($tmpFile, date("d.m.Y H:i:s")." Ошибка. Не XML файл!2", FILE_APPEND);

			if($startTime == 0) $startTime = time();
			$iel = new CIBlockElement;
			for($goodn = $passElements; $goodn < sizeof($xml->Товары->Товар); $goodn++){					//---------Обработка товаров========================
				$good = $xml->Товары->Товар[$goodn];
				//if((int)$good->Код == 2460)	continue;
				//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Пишем товар ".(int)$good->Код." из папки ".(int)$good->Раздел."\n", FILE_APPEND);
				$goodObj = new CIBlockElement;
				$parent = self::getSectByCode($good->Раздел);
				file_put_contents($tmpFile, date("d.m.Y H:i:s")." Смотрим торвар ".(int)$good->Код." из папки ".(int)$good->Раздел."\n", FILE_APPEND);

				//file_put_contents($tmpFile, "!!!!!!!!!!!".$good->Раздел."\n", FILE_APPEND);
				//if((int)$parent['ID'] == 8997) $active = "N";
				if((int)$good->Раздел == 8997) $active = "N"; //если из раздела прочее
				else $active = "Y";
				
				$goodArr = Array(
					"IBLOCK_ID"=>$CATALOG_ID,
					"NAME"=>$good->Название,
					"XML_ID"=>trim($good->Код),
					"IBLOCK_SECTION_ID"=>$parent['ID'],
					"ACTIVE"=>$active,
				);

				$els = self::getElsByCode(trim($good->Код)); //ищем, если ли на сайте товар с таким XML_ID
				//file_put_contents($tmpFile, date("d.m.Y H:i:s")." массив результата поиска товара els: ".print_r($els, true)."\n", FILE_APPEND);
				if(sizeof($els) < 1){
					file_put_contents($tmpFile, date("d.m.Y H:i:s")." Товара нет, создадим ".(int)$good->Код."\n", FILE_APPEND);
					$code = self::translit($goodArr['NAME']);
					$goodArr['CODE'] = trim($goodArr['XML_ID']);
					$id = $goodObj->Add($goodArr);
					//file_put_contents($tmpFile, date("d.m.Y H:i:s")." массив создания товара i: ".print_r($id, true)."\n", FILE_APPEND);
					$els = Array(Array("ID"=>$id));
					//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Массив els тут имеет длину ".count($els)."\n", FILE_APPEND);
				}
				foreach($els as $el){
					self::processGood($el, $good, $CATALOG_ID, $processfile); //запускаем функцию обработки товаров
					// перегрузим элемент для фасета и разных радостей
					$tempID = $el['ID'];
					$errorUpdate = $iel->Update($tempID, Array());
					//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Перегрузили товар для разных разностей ".$el["XML_ID"]." (".$el['ID'].") ".$errorUpdate."\n", FILE_APPEND);
					$errorUpdate = $iel->Update($tempID, Array()); //вот такой глюк, нужно два раза перегружать товар иначе в фильтр не попадает он
					//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Повторно перезагружаю товар без NEW jbject в цикле ".$el["XML_ID"]." (".$el['ID'].") ".$errorUpdate."\n", FILE_APPEND);
					$passElements ++; // increment progress
					file_put_contents($progressFile, $passElements); // save progress
					if(time() >= $startTime+self::getTimeout()){
						file_put_contents($progressFile, $passElements);
						return "CIExchange::ImportGoods();";
					}
				}
			}
			unlink($progressFile);																			// ---------finish process================================

			$dir = opendir($modulePath."/progress/");														//Читаем очередь progress
			while($el = readdir($dir)){
				if($el != "." && $el != ".." && is_file($modulePath."/progress/".$el)){
					$file_arr = file($modulePath."/progress/".$el);
					$lines = count($file_arr);
					if(strpos($el, "ord_") !== false) $pathProcessed = "/processed/orders/"; 		//если это заказ
					elseif (strpos($el, "Users") !== false) $pathProcessed = "/processed/Users/"; 	//если это клиент
					else $pathProcessed = "/processed/prod/"; 										//если это товар
					copy($modulePath."/progress/".$el, $modulePath.$pathProcessed.$el);
					unlink($modulePath."/progress/".$el);
					//activator(); //по-моему это тут не нужно
					if(false && $lines > "1300000") { // деактивировать все товары если идет полная выгрузка (яотключил ее)
						$date=file_get_contents($modulePath."/date_start.xml");
						$res = CIBlockElement::GetList(false,array("IBLOCK_ID"=>self::getGoodId(),"<TIMESTAMP_X"=>$date),false,false,false);
						while($Element=$res->GetNext()){
							$goodObj = new CIBlockElement;
							$goodObj->Update($Element['ID'], array("ACTIVE"=>"N"));
						}
					}
				}
			}
			closedir($dir);

			//if(time() <  $startTime+self::getTimeout())	CIExchange::ImportGoods($startTime);

			if(time() <= $startTime+self::getTimeout()) CIExchange::ImportGoods($startTime);
			return "CIExchange::ImportGoods();";
		}
		else	return "CIExchange::ImportGoods();";
	}

	public function sizeof($arr){
		$res = sizeof($arr);
		if($res < 1 && isset($arr[0])) $res = 1;
		return $res;
	}
	
	public function processGood($el, $good, $CATALOG_ID, $processfile){
		$modulePath = self::getModulePath();
		$tmpFile = self::getModulePath()."/tmp.txt";
		$id = $el['ID'];
		file_put_contents($tmpFile, date("d.m.Y H:i:s")." Товар обрабатывается ".$id."\n" , FILE_APPEND);
		
		$goodObj = new CIBlockElement;
		
		// Обработаем разделы - особо актуально, когда их много
		$sectsArr = Array();
		foreach($good->Раздел as $s1){
			$s2 = self::getSectByCode($s1);
			if($s2) $sectsArr[] = $s2['ID'];
		}
		if(self::sizeof($sectsArr) > 0)		$goodObj->SetElementSection($id,$sectsArr);
		
		$setArr = Array(); 
		$kodes = Array("Weee", "Срок оформления", "Срок подтверждения акцепта", "Срок поставки", "Срок производства", "Мин. удерживающая нагрузка стойкость к растяжению, Н", "Мин. удерживающая нагрузка стойкость к растяжению",
		"Тип подключения силовой электрич. цепи", "Тип подключения силовой электрич. цепи", "Номин. продолжительный ток Iu, А", "Номин продолжительный ток Iu А", "Напряжение согл. EN 60309-2", "Напряжение согл EN 60309-2",
		"Подходит для лотка шириной мм", "Подходит для лотка шириной, мм", "Номин ток утечки А", "Номин ток утечки, А", "Тип электрич соединения 1", "Тип электрич. соединения 1", "Тип электрич соединения 2", "Тип электрич. соединения 2",
		"Световой поток, лм.",
		);
		//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Дошли до обработки свойств товара ".$el["XML_ID"]."\n" , FILE_APPEND);
		foreach($good->Свойства->Свойство as $prop){ 	//===========================================Обработка свойств товаров=========================================================
			$temp = trim($prop->Код);
			$valueProperty = trim($prop->Значение);
			//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Дошли до обработки свойств ".$temp." со значением ".$valueProperty."\n" , FILE_APPEND);
			$nedlafiltra = trim($prop->НеДляФильтра);
			if(strpos($temp, ":")) continue; // пропускать значения свойств с ":"
			if($temp != 'Описание' && strpos($valueProperty, "/")) continue; // пропускать свойства с "/"
			if($temp != 'Описание' && strpos($valueProperty, ":")) continue; // пропускать свойства с ":"
			if(strlen($temp) > 30) continue; // пропускать слишком длинные свойства
			
			if(in_array($temp, $kodes)) continue;

			$valueProperty = trim($prop->Значение);
			if($id == 125045) file_put_contents($tmpFile, "ТУТ этот товар2. Не для фильтра = \n".$nedlafiltra , FILE_APPEND);
			if((string)$prop->НеДляФильтра == "ИСТИНА"){
				file_put_contents($tmpFile, "Не для фильтра - ИСТИНА\n" , FILE_APPEND);
				$priznak = 777;
				$valueProperty = "";
			}
			
			if($temp == 'Описание'){
				file_put_contents($tmpFile, "Есть описание ".$valueProperty."\n" , FILE_APPEND);	
				$desc_el = new CIBlockElement;
				$desc_el->Update($id,Array("DETAIL_TEXT" => $valueProperty));
				continue;
			}
			if(strlen($valueProperty) > 240) continue;
			
			//file_put_contents($tmpFile, "Смотрим свойство ".$valueProperty." " , FILE_APPEND);
			//$temp=trim($temp); 			//if ($temp=="Описание") echo $temp."\n\r";
			if(!isset($propObj)) $propObj = new CIBlockProperty;
			$property = $propObj->GetList(Array(),Array("IBLOCK_ID"=>$CATALOG_ID,"XML_ID"=>$temp))->GetNext();
			$property_code = str_replace("-","_",self::translit($temp));
			$code_ = $propObj->GetList(Array(),Array("IBLOCK_ID"=>$CATALOG_ID,"CODE"=>$property_code))->GetNext();
			if(!$property && !$code_){ // Если свойство не найдено в базе - создадим его.
				//continue; // пока пропустим его
				$property_id = $propObj->Add(Array(
					"IBLOCK_ID"=>$CATALOG_ID,
					"XML_ID"=>$temp,
					"NAME"=>$temp,
					"CODE"=>$property_code,
					"TYPE"=>(is_int($prop->Значение) || is_float($prop->Значение)) ? "N" : "S",
				));
			}else{
				$property_id = $property['ID'];
				$property_code = $property['CODE'];
			}
			
			//$val = $prop->Значение;
			if(strlen($valueProperty) < 1 ) $valueProperty = "";
			if($prop->Код == 'Размер' && (int) $prop->Значение > 0) {
				$string1=preg_replace("#[\,]#",".",$valueProperty);
				$pos = strpos($string1, '.');
				$str1=substr($string1, 0, $pos+1);
				$str2=substr($string1, $pos+1);
				$string2=preg_replace("#[\.]#","",$str2);
				$string3=$str1.$string2;
				$valueProperty = preg_replace("#[^0-9\.]#","",$string3);
			}
			$setArr[$property_id] = (string) $valueProperty; //наполняем массив свойств товаров
			$setArrCode[$property_code] = (string) $valueProperty; //наполняем массив свойств товаров
			if($prop->Код=='Вес' && (int) $prop->Значение > 0){  self::setWeight($id,$prop->Значение);}
		}
		// process price
		$priced=(string)$good->Цена;
		$price=preg_replace("/[^0-9],./", '', $priced);

		$iel = new CIBlockElement; 		// Убить товар без цены
		if((float)$price > 0){
			//self::setPrice($id,$price);
			self::setPrice1($id,$good->Цена);
		}
		else	$iel->Update($id,Array("ACTIVE"=>"N"));
		
		//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Начинаем смотреть остатки\n", FILE_APPEND);
		$map = [4 => 78615,	5 => 78616,	6 => 78617,	7 => 78618,	8 => 78619,	9 => 78620,	10 => 78621, 11 => 78622, 12 => 78623, 13 =>113823];
								//Остаток на складе
			$prop_vals = array();
			$totalAmount=0;
			foreach($good->КоличествоНаСкладах->КоличествоНаСкладе as $countstore){
				$storem = CCatalogStore::GetList(array("TITLE"), array("XML_ID"=>(string)$countstore->ИдСклада))->GetNext();
				if($storem["TITLE"]){
					$intOfferID = $id;
					$quantityValue = (string)$countstore->Количество;
					$quantityValueInt  =     $countstore->Количество;
					if($quantityValueInt == "") $quantityValue = 0;
					$totalAmount += $countstore->Количество;

				    // Выбираем текущую инфу по товару для складе
				    $rsStore = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' => $intOfferID, 'STORE_ID' => $storem['ID']), false, false, array('*'))->Fetch();
				    if ($rsStore) { // Если данный товар есть на складе
				        //file_put_contents($tmpFile, "Tovar ".$id. " est na sayte, nado - ".$quantityValueInt."\n", FILE_APPEND);
						$arFields = Array(
				            "PRODUCT_ID" => $id,
				            "STORE_ID" => $storem['ID'],
				            "AMOUNT" => $quantityValue,
				        );
				        CCatalogStoreProduct::Update($rsStore['ID'], $arFields); // Обновляем количество товара на складе
				    } else { // Если его на складе нет
						//file_put_contents($tmpFile, "tovar ".$id. " net na sayte, nado -".$quantityValue."\n", FILE_APPEND);
				        $arFields = Array(
				            "PRODUCT_ID" => $id,
				            "STORE_ID" => $storem['ID'],
			    	        "AMOUNT" => $quantityValue,
				        );
					    CCatalogStoreProduct::Add($arFields); // Добавляем к-во товара на складе
					}
					if($quantityValueInt > 0) $prop_vals[] = $map[$storem['ID']];
				}
			}
			file_put_contents($tmpFile, "tovar ".$id. " \n".print_r($prop_vals, true), FILE_APPEND);
			CIBlockElement::SetPropertyValues($id, 4, $prop_vals, 'EXISTS_AT_STORES'); // установка свойства "Наличие на складах"

		self::setQuantity($id,$totalAmount);

		$setArr["file"] = basename((string) $processfile); // последний файл выгрузки

		$plist = CIBlockElement::GetProperty("4", $id, Array(), Array());
		while($pel = $plist->GetNext()){
			//file_put_contents($tmpFile, date("d.m.Y H:i:s")." ".print_r($pel, true)."\n" , FILE_APPEND);
	 		//не обновлять некоторые свойства товаров при обмене, если в файле не было этого свойства (рейтинг, хит продаж, фото, описание, старая цена, внешний склад)
			if($pel['NAME']!=="ХИТ" && $pel['NAME']!=="Старая цена" && $pel['NAME']!=="Рейтинг" && $pel['ID']!=="Наличие на складах" && $pel['NAME']!=="Внешний склад"){
				if(!isset($setArr[$pel['ID']])) $setArr[$pel['ID']] = false;			//остальные значение свойств обнулить
			}
		}
		//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Начинаем пытаться записывать новые свойства товару".print_r($setArr, true)."\n" , FILE_APPEND);
		CIBlockElement::SetPropertyValuesEx($id,$CATALOG_ID,$setArr); //Записать новые свойства товару
		
		//file_put_contents($tmpFile, date("d.m.Y H:i:s")." setArr[aktivnost] = ".$setArrCode["aktivnost"]." el['ACTIVE'] = ".$el['ACTIVE']."\n" , FILE_APPEND);
		if($setArrCode["aktivnost"] == 'N' && $el['ACTIVE']=='Y'){
			$iel->Update($id, Array("ACTIVE"=>"N"));
			//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Деактивируем товар\n" , FILE_APPEND);
			//}elseif($el['ACTIVE']=='N' && $totalAmount > 0){
		}elseif($setArrCode["aktivnost"]=='Y' && $el['ACTIVE']=='N'){
			$iel->Update($id, Array("ACTIVE"=>"Y"));
			//file_put_contents($tmpFile, date("d.m.Y H:i:s")." Активируем товар\n" , FILE_APPEND);
		}

		// process images
		$filesArr = Array(); $fileStruct = Array();	$tmpImg =  $modulePath."/tmpImg.txt"; $kods = array();
		$imgn = 0; $pathDir = $modulePath."/upload/newImages/"; $newPathDir = $modulePath."/upload/images/";
		file_put_contents($tmpFile, date("d.m.Y H:i:s")." Подошли к изображениям товаров\n" , FILE_APPEND);
		$dir = opendir($pathDir);
		while($FileImg = readdir($dir)){ //проверяем, нет ли новых картинок для загрузки
			if($FileImg != "." && $FileImg != ".."){
				if((filectime($pathDir.$FileImg) + 15) > time()){
					$filesArr = Array();
					break; //дождемся полной загрузки всех файлов на FTP
				}
				$filesArr[] = $FileImg;
			}
		}
		closedir($dir);
		if (!empty($filesArr)) file_put_contents($tmpImg, date("Y-m-d H:i:s")." Массив всех файлов ".print_r($filesArr, true)."\n", FILE_APPEND);
		
		foreach($filesArr as $FileImg){
			$pos = strpos($FileImg, ".jpg");
			if($pos){
				//file_put_contents($tmpImg, date("Y-m-d H:i:s")." Найден валидный файл картинки для товара  ".$pos."\n", FILE_APPEND);
				$kodTovara = substr($FileImg, 0, -4);
				if(!strpos($kodTovara, "_")){
					$fileStruct[$kodTovara][] = $FileImg;
				}else{
					$offset = strlen($kodTovara) - strripos($kodTovara, "_");
					
					$kodTovara = substr($kodTovara, 0, -$offset); 
					//file_put_contents($tmpImg, date("Y-m-d H:i:s")." Код товара => ".$kodTovara.PHP_EOL, FILE_APPEND);
					$fileStruct[$kodTovara][] = $FileImg;
				}
				if(!in_array($kodTovara, $kods)) $kods[] = $kodTovara;
			}else unlink($pathDir.$FileImg); //удалить невалидный файл
		}
		
		//if (!empty($fileStruct))		file_put_contents($tmpImg, date("Y-m-d H:i:s")." Массив fileStruct ".print_r($fileStruct, true)."\n", FILE_APPEND);
		
		$imgArr = Array(); $file2remove = array();
		foreach($fileStruct as $kod=>$y){
			$kodTovara = $kod;
			$subCatalog = (strlen($kod) < 4) ? "0000/" : substr($kod, 0, -3) . "000/";
			$sss = CIBlockElement::GetList(Array(),Array("IBLOCK_ID" => $CATALOG_ID, "XML_ID"=>$kod))->GetNext();
			if(isset($sss["ID"]) && $sss["ID"] != ""){
				$propvals_todel = CIBlockElement::GetProperty($CATALOG_ID, $sss["ID"], Array(), Array("CODE"=>"photo"));
				file_put_contents($tmpImg, date("Y-m-d H:i:s")." ID товара ".$sss["ID"]." Код товара ".$kod."\n", FILE_APPEND);
				foreach($fileStruct[$kod] as $i => $path){
					$file = $pathDir.$path;
					file_put_contents($tmpImg, date("Y-m-d H:i:s")." Путь к файлу: ".$pathDir.$path."\n", FILE_APPEND);
					if(is_file($pathDir.$path)){
						$file2remove[] = $path;
						file_put_contents($tmpImg, date("Y-m-d H:i:s")." Это нормальный файл: ".$pathDir.$path."\n", FILE_APPEND);
						if($imgn == 0){
							file_put_contents($tmpImg, date("Y-m-d H:i:s")." Удаляем файлы по маске ".$newPathDir.$subCatalog.$kodTovara.".jpg и ".$newPathDir.$subCatalog.$kodTovara."_*.jpg!\n", FILE_APPEND);
							file_put_contents($tmpImg, date("Y-m-d H:i:s")." Массив с именами файлов ".print_r(array_merge(glob($newPathDir.$subCatalog.$kodTovara."_*.jpg"),glob($newPathDir.$subCatalog.$kodTovara.".jpg")), true).PHP_EOL, FILE_APPEND);
							$oldImages = array_merge(glob($newPathDir.$subCatalog.$kodTovara."_*.jpg"), glob($newPathDir.$subCatalog.$kodTovara.".jpg"));
							if(count($oldImages)){
									file_put_contents($tmpImg, date("Y-m-d H:i:s")." Попытка удаления1 ".print_r($oldImages, true)."\n", FILE_APPEND);
									$resultUnlink = array_map("unlink", $oldImages);
							}
							//file_put_contents($tmpImg, date("Y-m-d H:i:s")." Результат удаления1 ".print_r($resultUnlink, true)."\n", FILE_APPEND);
							//$oldImages = glob($newPathDir.$kodTovara."_*.jpg");
							//if(count($oldImages))	$resultUnlink = array_map("unlink", $oldImages);
							//file_put_contents($tmpImg, date("Y-m-d H:i:s")." Результат удаления2 ".print_r($resultUnlink, true)."\n", FILE_APPEND);
							$img = CFile::MakeFileArray($pathDir.$path);
							file_put_contents($tmpImg, date("Y-m-d H:i:s")." метод MakeFileArray пройден  \n", FILE_APPEND);
							$img['del'] = "Y";
							//file_put_contents($tmpImg, date("Y-m-d H:i:s")." Массив img= ".print_r($img, true)."  \n", FILE_APPEND);
							$goodObj->Update($sss["ID"], Array("DETAIL_PICTURE"=>$img, "PREVIEW_PICTURE"=>$img));
							file_put_contents($tmpImg, date("Y-m-d H:i:s")." Обновлен елемент \n", FILE_APPEND);

						}
						else{
							$img = CFile::MakeFileArray($pathDir.$path);
							$img['del'] = "Y";
							$imgArr[] = $img;
						}
						$imgn++;
					}
				}
			}
			else {
				file_put_contents($tmpImg, date("Y-m-d H:i:s")." товар не из основного каталога: ".$kod."\n", FILE_APPEND);
				foreach($fileStruct[$kod] as $i => $path){
					$file = $pathDir.$path;
					//file_put_contents($tmpImg, date("Y-m-d H:i:s")." Путь к файлу: ".$pathDir.$path."\n", FILE_APPEND);
					if(is_file($pathDir.$path))	$file2remove[] = $path;
				}				
			}
			break;
		}

		//if (!empty($imgArr))		file_put_contents($tmpImg, date("Y-m-d H:i:s")." Массив imgArr: ".print_r($imgArr, true)."\n", FILE_APPEND);
		//file_put_contents($tmpImg, date("Y-m-d H:i:s")." это элемент".print_r($propvals_todel, true)."\n", FILE_APPEND);
		if(is_object($propvals_todel)){
			$arDel = array();
			$arFile = ["MODULE_ID" => "iblock", "del"=> "Y"];
			while($propval_todel = $propvals_todel -> GetNext()){
				$vid = $propval_todel['PROPERTY_VALUE_ID'];
				$arDel[$vid] = Array("VALUE"=>$arFile); // наполняем массив для удаления старых картинок товара
				//CIBlockElement::SetPropertyValueCode($sss["ID"], "photo", Array ($vid => Array("VALUE"=>$arFile) ) ); // удалить старые картинки товару
				file_put_contents($tmpImg, date("Y-m-d H:i:s")." Удаляем картинку ".$vid." для товара ".$kodTovara." ".print_r($arFile, true)."\n", FILE_APPEND);
			}
			CIBlockElement::SetPropertyValueCode($sss["ID"], "photo", $arDel ); // удалить старые картинки товару
			file_put_contents($tmpImg, date("Y-m-d H:i:s")." Удаляем картинку ".print_r($arDel, true)." для товара ".$kodTovara." "."\n", FILE_APPEND);
		}
		if(sizeof($imgArr) > 0)	{
			file_put_contents($tmpImg, date("d.m.Y H:i:s")." Записываем изображения для товара ".$kodTovara." количество новых дополнительных картинок: ".count($imgArr)."\n\n" , FILE_APPEND);
			file_put_contents($tmpImg, date("d.m.Y H:i:s")." Список файлов: ".print_r($imgArr, true)."\n\n" , FILE_APPEND);
			CIBlockElement::SetPropertyValues($sss["ID"],$CATALOG_ID,$imgArr,"photo"); // сохранить картинки товару
		}
		
		//foreach($filesArr as $FileImg){
		foreach($file2remove as $FileImg){
			if(!strpos($FileImg, "_")) $subCatalog = (strlen($FileImg) < 8) ? "0000/" : substr($FileImg, 0, -7) . "000/";
			else {
				$offset = strlen($FileImg) - strripos($FileImg, "_") + 3;
				$subCatalog = (strripos($FileImg, "_") < 4) ? "0000/" : substr($FileImg, 0, -$offset) . "000/";
				//$subCatalog = substr($FileImg, 0, -$offset) . "000/";
			}

			file_put_contents($tmpImg, date("Y-m-d H:i:s")." перемещаем файл в каталог ".$newPathDir.$subCatalog.$FileImg."\n", FILE_APPEND);
			if(!is_dir($newPathDir.$subCatalog)){
				file_put_contents($tmpImg, date("Y-m-d H:i:s")." Каталог не существует ".$newPathDir.$subCatalog."\n", FILE_APPEND);
				mkdir($newPathDir.$subCatalog, 0777);
			}
			if(!rename($pathDir.$FileImg, $newPathDir.$subCatalog.$FileImg)) file_put_contents($tmpImg, date("d.m.Y H:i:s")." Ошибка переноса файла".PHP_EOL , FILE_APPEND);
			//unlink($pathDir.$FileImg);
		}
		file_put_contents($tmpFile, date("d.m.Y H:i:s")." Конец обработки товара\n\n" , FILE_APPEND);
		if(unhtmlentities($el['NAME']) != unhtmlentities($good->Название)) $goodObj->Update($id,Array("NAME"=>unhtmlentities($good->Название))); //Название товара обновить, если требуется
	}
	// ======================================================= Конец обработки файла XML ===========================================================================
	public function ExportUsers(){
		$string = <<<XML
<?xml version="1.0" encoding="UTF-8"?> 
<Покупатели>
</Покупатели>
XML;
	$userXML = simplexml_load_string($string);
	$filter = Array("ACTIVE"=>"Y", "UF_BAL_NA_KARTU"=> "%");
	$filter = Array("ACTIVE"=>"Y", Array("LOGIC" => "OR", "UF_BAL_NA_KARTU"=> "%", ">UF_BAL_NA_KUPON" => 0));
	$rsUsers = CUser::GetList(($by="id"), ($order="desc"), $filter, Array("SELECT" => array("UF_BAL_NA_KARTU", "UF_ID", "UF_BAL_NA_KUPON", "UF_KUPON", "UF_KUPON_FROM_1C")));
	
	while($arUser = $rsUsers->Fetch()) {
		$user = $userXML->addChild("Покупатель");
		$user->IDпользователя = $arUser['UF_ID'];
		$user->Имя = $arUser['NAME'];
		$user->Мобильный = $arUser['PERSONAL_PHONE'];
		$user->БаллыНаКарту = $arUser['UF_BAL_NA_KARTU'];
		$user->БаллыНаПромокод = $arUser['UF_BAL_NA_KUPON'];
		$user->ПромокодДля1С = $arUser['UF_KUPON'];
		$user->ПромокодИз1С = $arUser['UF_KUPON_FROM_1C'];
	}
	$userXML = simplexml_load_string($userXML->asXML());
	return $userXML->asXML();
}

//===CheckUser=====
	public function CheckUser($UF_ID="", $MOBILE="", $EMAIL=""){
		$string = <<<XML
<?xml version="1.0" encoding="UTF-8"?> 
<Покупатели>
</Покупатели>
XML;
	$userXML = simplexml_load_string($string);
	if($MOBILE != "") $filter = Array("ACTIVE"=>"Y", "PERSONAL_PHONE"=> $MOBILE);
	elseif($EMAIL != "") $filter = Array("ACTIVE"=>"Y", "EMAIL"=> $EMAIL);
	else $filter = Array("ACTIVE"=>"Y", "UF_ID"=> $UF_ID);
	$rsUsers = CUser::GetList(($by="id"), ($order="desc"), $filter, Array("SELECT" => array("UF_BAL_NA_KARTU", "UF_ID")));
	
	while($arUser = $rsUsers->Fetch()) {
		$user = $userXML->addChild("Покупатель");
		$user->IDпользователя = $arUser['UF_ID'];
		$user->Фамилия = $arUser['LAST_NAME'];
		$user->Имя = $arUser['NAME'];
		$user->Отчество = $arUser['SECOND_NAME'];
		$user->EMAIL = $arUser['EMAIL'];
		$user->Имя = $arUser['NAME'];
		$user->Мобильный = $arUser['PERSONAL_PHONE'];
	}
	$userXML = simplexml_load_string($userXML->asXML());
	return $userXML->asXML();
	}
	//===End of CheckUser=====

	public function ExportOrders(){
		$string = <<<XML
<?xml version="1.0" encoding="UTF-8"?> 
<заказы>
</заказы>
XML;
		$orderXML = simplexml_load_string($string);
		$cond = Array();
		if($_GET['date']!="") $cond['>=DATE_INSERT'] = date("d.m.Y 00:00:00",strtotime($_GET['date']));
		if($_GET['to']!="") $cond['<=DATE_INSERT'] = date("d.m.Y 23:59:59",strtotime($_GET['to']));
		if($_GET['status']!="" && $_GET['status']!='CANCEL') $cond['STATUS_ID'] = $_GET['status'];
		elseif($_GET['status']=='CANCEL') $cond['CANCELED'] = "Y";

		if($_GET['ID']!="") $cond['ID'] = $_GET['ID']; 
		if($_GET['XML_ID']!="") $cond['XML_ID'] = $_GET['XML_ID']; 
		if($_GET['user']!="") {
			$rsUsers = CUser::GetList(($by="personal_country"), ($order="asc"), array("UF_ID"=>$_GET['user']))->NavNext(true, "f_");
			$cond['USER_ID'] = $rsUsers['ID']; 
		}
		//if($cond['STATUS_ID']=="N"){$cond["CANCELED"]="N";}
		if($cond['STATUS_ID']=="N"){$cond['>DATE_INSERT'] = "01.07.2019 23:59:59";}
		$count["STATUS_ID"] = N;
		$count["!XML_ID"] = false;

		$orders = CSaleOrder::GetList(Array("ID"=>"DESC"),$cond, false, Array("nTopCount"=>50));
		$xmlArr = Array();
		while($orderArr = $orders->GetNext()){
			$order_id = Sale\Order::load($orderArr['ID']);
			$priceDelivery = $order_id->getShipmentCollection()->getPriceDelivery(); //получить стоимость доставки
			//file_put_contents("/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/order_tmp.txt", print_r($order_id->getShipmentCollection()->getPriceDelivery(), true));
			if($orderArr['XML_ID']=="") continue;
			$xmlArr[$orderArr['XML_ID']] ++;
			if($xmlArr[$orderArr['XML_ID']] > 1) continue;

			//$arStatus = CSaleStatus::GetByID($orderArr["STATUS_ID"]);
			$propvals = CSaleOrderPropsValue::GetList(Array(),Array("ORDER_ID"=>$orderArr['ID']), false, false, Array("*"));
			//file_put_contents("/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/order_tmp.txt", print_r($propvals, true));
			$propValArr = Array();
			while($propval = $propvals->GetNext()){
				$propValArr[$propval['CODE']] = $propval['VALUE'];
			}
			file_put_contents("/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/tmpPropVal.txt", date("F j, Y, g:i a")." №".$orderArr['ID']."\n".print_r($propValArr, true), FILE_APPEND);
			if($orderArr['PAYED']=="Y") {
				if (!empty($orderArr['PS_SUM'])) $payedsum=$orderArr['PS_SUM'];
				elseif ($propValArr['PAYT']>1) $payedsum=$orderArr['PRICE'];
				else $payedsum=$orderArr['PRICE_DELIVERY'];
				$payedsum=$orderArr['PRICE']; // так же логично
			}else{
				$payedsum = 0;
			}
			$city=CSaleLocation::GetByID($propValArr['city']);
			$basket = CSaleBasket::GetList(Array(),Array("ORDER_ID"=>$orderArr['ID']));

			$ok=1;
			while($goodArr = $basket->GetNext()){
			$User = CUser::GetByID($orderArr["USER_ID"])->Fetch();
				$arSelect = Array("ID", "XML_ID", "PROPERTY_ARTIKUL");
				$arFilter = Array("IBLOCK_ID"=>4, "ID"=>$goodArr['PRODUCT_ID']);
				$prod = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect)->GetNext();
			}
			if ($ok==1) {

				if($propValArr["ur"]){$ur="Юр. лицо";} else {$ur="Физ. лицо";}
				if($orderArr["DELIVERY_ID"]="2" && $orderArr['PRICE_DELIVERY']==0){$sm="Y";}else{$sm="";}

				if($orderArr["DELIVERY_ID"]!="2")
				{
					$arDeliv = CSaleDelivery::GetByID($orderArr["DELIVERY_ID"]);
					$tp = $arDeliv["NAME"].' - '.$orderArr["PRICE_DELIVERY"];
				}else{$tp="";}

				$fio = transliterate($propValArr["fio"],2)." ".transliterate($propValArr["name"],2)." ".transliterate($propValArr["sern"],2);
				$name = transliterate($propValArr["name"],2);
				$last = transliterate($propValArr["fio"],2);
				$sern = transliterate($propValArr["sern"],2);
				$phone = $propValArr["phone"];
				$reg = $city['REGION_NAME_LANG'];
				$citys = transliterate($city['CITY_NAME_LANG'],2);
				$mailu = $propValArr["mail"];

				if(strlen($fio)<3){
					$fio = $User["NAME"]." ".$User["LAST_NAME"]." ".$User["SECOND_NAME"];
					$name = $User["NAME"];
					$sern = $User["LAST_NAME"];
					$last = $User["SECOND_NAME"];	
				}
				if(!$phone){$phone=$User["PERSONAL_PHONE"];}
				if(!$reg){$reg = $User["PERSONAL_STATE"];}
				if(!$citys){$citys = $User["PERSONAL_CITY"];}
				if(!$mailu){$mailu = $User["EMAIL"];}

				//echo"<pre>"; var_dump($User); echo"</pre>"; die();
				$id_order = CSaleOrder::GetByID($orderArr["ID"]);

				if($User["UF_ID"]){$userid = $User["UF_ID"];}
				else{

					$user = new CUser;
					$user->Update($User["ID"], array("UF_ID"=>"2".$User[ID]));
									//var_dump($User["ID"]); die();
					$userid = "2".$User["ID"];
				}

				// Если заказ выполнен, то он уж точно оплачен
				if($orderArr["STATUS_ID"]=='F'){
					$orderArr["PAYED"] = 'Y';
					$orderArr["SUM_PAID"] = $orderArr['PRICE'];
				}

				// Скидка по промо коду - очень непросто вычисляется для нашего сайта
				$basket = CSaleBasket::GetList(Array(),Array("ORDER_ID"=>$orderArr['ID']));
				$countprice = 0;
				$arGoods = Array();
				while($goodArr = $basket->GetNext()){
					$price = getprice($goodArr['PRODUCT_ID'], 1, CUser::GetUserGroup($orderArr['USER_ID']));
					$priceA = CIBlockElement::GetProperty(self::getGoodId(), $goodArr['PRODUCT_ID'], Array(), Array("CODE"=>"cenaakcii1"))->GetNext();
					if($priceA['VALUE']!="" && $priceA['VALUE'] < $price) $price = $priceA['VALUE'];
					//var_dump($price); die();
					$countprice += $price * $goodArr['QUANTITY'];
					//$goodArr['PRICE'] = $price;
					//if($price>0 || $price ){ $goodArr['PRICE'] = $price;}
					$arGoods[] = $goodArr;
				}


				$couponList = \Bitrix\Sale\Internals\OrderCouponsTable::getList(array(
					'select' => array('COUPON'),
					'filter' => array('=ORDER_ID' => $orderArr['ID'])
				));
				while ($coupon = $couponList->fetch()){
					$promokod = $coupon['COUPON'];
				}

				//file_put_contents("/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/order_tmp.txt", print_r($arGoods, true));
				$orderArr["DISCOUNT_VALUE"] = (int) round($countprice - $orderArr["PRICE"] + $orderArr['PRICE_DELIVERY'], 2);
				if($orderArr["DISCOUNT_VALUE"] > 510) $orderArr["DISCOUNT_VALUE"] = 0;
				if($orderArr["DISCOUNT_VALUE"] < 490) $orderArr["DISCOUNT_VALUE"] = 0;

				$order = $orderXML->addChild("заказ");
				//$order->ID = $orderArr['ID'];
				$order->ID = $orderArr['XML_ID'];
				//$order->XML_ID = $orderArr['XML_ID'];
				$order->IDпользователя = $userid;
				$order->Дата = $orderArr['DATE_INSERT'];
				$order->Email = $mailu;
				if($orderArr["PAY_SYSTEM_ID"]=="9") $ur="Юр. лицо";
				$order->ФизЮр = $ur;

				$order->ПолноеНаименованиеОрганизации = unhtmlentities($propValArr["org"]);
				$order->ФИОполучателя = trim($fio);
				$order->ИмяПолучателя = $name;
				$order->ОтчествоПолучателя = $sern;
				$order->ФамилияПолучателя = $last;
				$order->МобильныйПолучателя = $phone;
				$order->ИНН = $propValArr["inn"];
				$order->КПП = $propValArr["kpp"];
				$order->ЮридическийАдрес = $propValArr["ur"];
				$order->ОбластьПолучателя = $reg;
				$order->ГородПолучателя = $citys;
				$order->АдресПолучателя = $propValArr["adr"]." ".$propValArr["home"]." ".$propValArr["corp"]." ".$propValArr["st"]." ".$propValArr["lit"]." ".$propValArr["kv"]." ".$propValArr["of"];
				$order->УлицаПолучателя = $propValArr["adr"];
				$order->НомерДомаПолучателя = $propValArr['home'];
				$order->КорпусПолучателя = $propValArr['corp'];
				$order->КвартираПолучателя = $propValArr['kv'];
				$order->ОфисПолучателя = $propValArr['of'];
				$order->ПочтовыйИндексПолучателя = $propValArr["index"];
				$order->СписанныйБонус = $propValArr["bonus"];
				$order->Оплачено = $orderArr['PAYED'];
				$order->ДатаОплаты = $orderArr['DATE_PAYED'];
				$order->Отменен =  $orderArr['CANCELED'];
				$order->ДатаОтмены =  $orderArr['DATE_CANCELED'];
				$order->ПричинаОтмены =  $orderArr['REASON_CANCELED'];
				$order->Промокод = $promokod;
				$order->СкидкаПоПромоКоду = $orderArr["DISCOUNT_VALUE"];
				$order->Склад = $propValArr["sklad"];
				if($orderArr['PRICE'] < $countprice){
					// это если Саша уже вычел бонус
					//$order->СуммаОплаты = $orderArr['PRICE'];
					$order->СуммаОплаты = $id_order['PS_SUM'];
					$order->ПолнаяСтоимостьЗаказа = $orderArr['PRICE']; 
				}else{
					//$order->СуммаОплаты = $payedsum-$propValArr['bonus'];
					$order->СуммаОплаты = $id_order['PS_SUM'];
					$order->ПолнаяСтоимостьЗаказа = $orderArr['PRICE'] - $propValArr["bonus"]; // Саша попросил отсюда вычесть бонус. Я с ним не согласен, но давайте потестим
				}
				//$order->Ценадоставки = $orderArr['PRICE_DELIVERY'];
				$order->Ценадоставки =$priceDelivery;
				$order->СпособОплаты = $orderArr["PAY_SYSTEM_ID"];
				$order->ДоставкаСамовывоз = $sm;
				if($propValArr["IPOL_OZON_PVZ"] != "") $order->ПунктВыдачиЗаказаOzon = $propValArr["IPOL_OZON_PVZ"];
				$order->ПунктВыдачиЗаказаOzon = $propValArr["IPOL_OZON_DELIVERY_VARIANT"];
				$order->ТранспортнаяКомпания = $tp;
				$order->РазрешенаДоставка = $orderArr["ALLOW_DELIVERY"];
				$order->ИнфОДоставке = $orderArr["ALLOW_DELIVERY"];
				$order->ИнформацияПоЗаказу = $orderArr["ADDITIONAL_INFO"];
				$order->ДопОписание = $orderArr["USER_DESCRIPTION"];
				$order->НомерТранзакции = $id_order["PS_STATUS_CODE"];
				$order->Плательщик = $id_order["PS_STATUS_DESCRIPTION"];
				$order->Статус = $orderArr["STATUS_ID"];

				$goods = $order->addChild("Товары");
				$inn = 0;
				$basket = CSaleBasket::GetList(Array(),Array("ORDER_ID"=>$orderArr['ID']));

				$loopProtect = Array();
				foreach($arGoods as $goodArr){
					//file_put_contents("/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/tmp.txt", print_r($goodArr, true), FILE_APPEND);
					if(in_array($goodArr['PRODUCT_ID'], $loopProtect)) continue;
					$loopProtect[] = $goodArr['PRODUCT_ID'];
					$inn++;
					$prod = GetIBlockElement($goodArr['PRODUCT_ID']);
					$base_price = CPrice::GetBasePrice($goodArr['PRODUCT_ID']);
					$good = $goods->addChild('Товар');
					//$good->Код = $prod['XML_ID'];
					$good->Код = $goodArr['PRODUCT_XML_ID'];
					$good->ID = $prod['ID'];
					$good->Наименование = str_replace("×","x",$goodArr["NAME"]);
					$good->Количество = $goodArr['QUANTITY'];
					$good->БазоваяЦена = $base_price["PRICE"];
					$good->Цена = $goodArr["PRICE"];
				}
				if($priceDelivery > 0){ //добавляем строку доставка
					$good = $goods->addChild('Товар');
					$good->Код = 2460;
					$good->Наименование = "Доставка груза";
					$good->Количество = 1;
					$good->Цена = $priceDelivery;
				}
			}
		}
		$orderXML = simplexml_load_string($orderXML->asXML());
		return $orderXML->asXML();
	}

	public function setQuantity($id,$quantity){
		if(CModule::IncludeModule("catalog")){
			$prodObj = new CCatalogProduct;
			$prod = $prodObj->GetByID($id);
			if($prod)	$prodObj->Update($id,Array("QUANTITY"=>$quantity, "QUANTITY_TRACE"=>"N","CAN_BUY_ZERO"=>"N"));
			else		$prodObj->Add(Array("QUANTITY"=>$quantity,"ID"=>$id, "QUANTITY_TRACE"=>"N","CAN_BUY_ZERO"=>"N"));
		}
		else	return false;
	}

	protected function setWeight($id,$weight){
		if(CModule::IncludeModule("catalog")){
			$prodObj = new CCatalogProduct;
			$prod = $prodObj->GetByID($id);
			if($prod)	$prodObj->Update($id,Array("WEIGHT"=>$weight));
			else		$prodObj->Add(Array("WEIGHT"=>$weight,"ID"=>$id));
		}
		else	return false;
	}

	public function setPrice1($id,$price, $from=false, $to=false){
		$PRODUCT_ID = $id;
		$PRICE_TYPE_ID = self::getPriceType();
		// <Цена КоличествоОт="1" КоличествоДо="1000">1000</Цена>
		$setArr = Array();
		$tsenaot = 0;
		if(sizeof($price)==0){
			return false;
		}elseif(sizeof($price) == 1){
			$setArr[] = Array((float)$price, 0, 0);
		}else{
			foreach($price as $price1){
				// максмальное число 1с передает как 999999 а надо как false
				$to = (int)$price1['КоличествоДо'];
				if($to==999999) $to = false;
				// минимальное число пересекается с предыдущим - надо исключить
				$from = (int)$price1['КоличествоОт'];
				if($tsenaot==0) $tsenaot = $from;
				if($from > 0 && $from == $prevto) $from ++;
				// если число начинается не с 0, то сделаем с 0
				if($from > 0 && sizeof($setArr)==0) $from = 0;
				$setArr[] = Array((float)$price1, $from, $to);
				$prevto = $to;
			}
		}

		if(sizeof($setArr) >= 1){
			// грохнем все цены
			$res = CPrice::GetList(array(),array("PRODUCT_ID" => $PRODUCT_ID,"CATALOG_GROUP_ID" => $PRICE_TYPE_ID));
			while($pr = $res->GetNext())	CPrice::Delete($pr['ID']);
		}
		$lastto = 0;
		foreach($setArr as $n=>$priceArr){
			$price = $priceArr[0];
			$from = $priceArr[1];
			$to = $priceArr[2];
			//if($to > $lastto) $to = $lastto+1;
			$arFields = Array(
			    "PRODUCT_ID" => $PRODUCT_ID,
			    "CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
			    "PRICE" => $price,
			    "CURRENCY" => "RUB",
			    "QUANTITY_FROM" => $from,
			    "QUANTITY_TO" => $to,
			);
			//$lastto = $to;
			if($n==0) $arFields['QUANTITY_FROM'] = false;
			if($n==sizeof($setArr)-1) $arFields['QUANTITY_TO'] = false;
			CPrice::Add($arFields);
		}
		CIBlockElement::SetPropertyValueCode($id, "tsenaot", $tsenaot);
	}

	public function setPrice($id,$price){
		$PRODUCT_ID = $id;
		$PRICE_TYPE_ID = self::getPriceType();

		$arFields = Array(
		    "PRODUCT_ID" => $PRODUCT_ID,
		    "CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
		    "PRICE" => $price,
		    "CURRENCY" => "RUB",
		);

		$res = CPrice::GetList(
	        array(),
	        array(
	                "PRODUCT_ID" => $PRODUCT_ID,
	                "CATALOG_GROUP_ID" => $PRICE_TYPE_ID
	            )
	    );

		if ($arr = $res->Fetch()){
			CIBlockElement::SetPropertyValueCode($id, "archprice", $arr['PRICE']);
		    CPrice::Update($arr["ID"], $arFields);
		}
		else{
		    CPrice::Add($arFields);
		}
	}

	protected function translit($code){
		return ib_translit($code);
	}
	protected function getSectByCode($code, $proba=false){
		if($code=="") return false;
		//		if($proba) $prefix = self::probaPrefix($proba);
		if(CModule::IncludeModule("iblock")){
			$CATALOG_ID = self::getGoodId(); // =4
			$sect =            CIBlockSection::GetList(Array(),Array("IBLOCK_ID"=>$CATALOG_ID,"XML_ID"=>$prefix.$code))->GetNext();
			if(!$sect) $sect = CIBlockSection::GetList(Array(),Array("IBLOCK_ID"=>$CATALOG_ID,"NAME"=>$prefix.$code))->GetNext();
			if(!$sect && $proba){
				$sect = 	   CIBlockSection::GetList(Array(),Array("IBLOCK_ID"=>$CATALOG_ID,"XML_ID"=>$code))->GetNext();
				$parent =      CIBlockSection::GetList(Array(),Array("IBLOCK_ID"=>$CATALOG_ID,"XML_ID"=>$prefix))->GetNext();
				unset($sect['ID']);
				$new = Array();
				$new['IBLOCK_ID'] = $sect['IBLOCK_ID'];
				$new['NAME'] = $sect['NAME'];
				$new['XML_ID'] = $prefix.$sect['XML_ID'];
				$new['IBLOCK_SECTION_ID'] = $parent['ID'];
				$new['CODE'] = $prefix.$sect['CODE'];
				$s_o = new CIBlockSection;
				$new['ID'] = $s_o->Add($new);
				return $new;
			}
			return $sect;
		}
		else	return false;
	}

	protected function getElByCode($code){
		if(CModule::IncludeModule("iblock")){
			$CATALOG_ID = self::getGoodId();
			$sect = CIBlockElement::GetList(Array(),Array("IBLOCK_ID"=>$CATALOG_ID,"XML_ID"=>$code."%"))->GetNext();
			return $sect;
		}
		else	return false;
	}
	protected function getElsByCode($code){
		if(CModule::IncludeModule("iblock")){
			$CATALOG_ID = self::getGoodId();
			$arr = Array();
			$els = CIBlockElement::GetList(Array(),Array("IBLOCK_ID"=>4, "XML_ID"=>$code));
			while($el = $els->GetNext()){
				$arr[] = $el;
			}
			return $arr;
		}else{
			return false;
		}
	}

	protected function getTimeout(){
		return 50;
	}
	protected function getPriceType(){
		return 1;
	}
	protected function getModulePath(){
		//return $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/iarga.exchange";
		return $_SERVER['DOCUMENT_ROOT']."bitrix/modules/iarga.exchange";
	}
	protected function getGoodId(){
		return 4;
	}
	protected function is_xml($file){
		return simplexml_load_file($file);
	}
}
function transliterate($str){
	return $str;
}
?>