<?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
 /*\Bitrix\Main\Loader::includeModule("catalog");
 \Bitrix\Main\Loader::includeModule("sale");
 $arErrors = array();
 */
 /* если нужно сделать заказ с существующей корзиной */
  //$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
  /* или если нужно заказать конкретный товар минуя корзину:
  $basket = \Bitrix\Sale\Basket::create($siteId);
  foreach($arProductId as $productId=>$quantity) {
      $item = $basket->createItem('catalog', $productId);
      $item->setFields(array( // стандартный вариант, цена берется из каталога
          'QUANTITY' => $quantity,
          'PRODUCT_PROVIDER_CLASS' => \Bitrix\Catalog\Product\Basket::getDefaultProviderName(),
          'LID' => $siteId,
      ));
      $item = $basket->createItem('catalog', $productId);
      $item->setFields(array( // вариант с указанием цены вручную
          'QUANTITY' => $quantity,
          'CURRENCY' => $currency,
          'LID' => $siteId,
          'PRICE' => 500,
      ));
}
*/

$currency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
Bitrix\Main\Loader::includeModule('sale');
Bitrix\Main\Loader::includeModule('catalog');

$products = array(
    array('PRODUCT_ID' => 214665, 'NAME' => 'Товар 1', 'PRICE' => 500, 'CURRENCY' => 'RUB', 'QUANTITY' => 5)
            );
        
$basket = Bitrix\Sale\Basket::create(SITE_ID);

foreach ($products as $product)
    {
        $item = $basket->createItem("catalog", $product["PRODUCT_ID"]);
        unset($product["PRODUCT_ID"]);
        $item->setFields($product);
    }
    
$order = Bitrix\Sale\Order::create(SITE_ID, 1);
$order->setPersonTypeId(1);
$order->setBasket($basket);

$shipmentCollection = $order->getShipmentCollection();
$shipment = $shipmentCollection->createItem(
        Bitrix\Sale\Delivery\Services\Manager::getObjectById(1)
    );

$shipmentItemCollection = $shipment->getShipmentItemCollection();

/** @var Sale\BasketItem $basketItem */

foreach ($basket as $basketItem)
    {
   		$item = $shipmentItemCollection->createItem($basketItem);
        $item->setQuantity($basketItem->getQuantity());
    }

$paymentCollection = $order->getPaymentCollection();
$payment = $paymentCollection->createItem(
        Bitrix\Sale\PaySystem\Manager::getObjectById(1)
    );
$payment->setField("SUM", $order->getPrice());
$payment->setField("CURRENCY", $order->getCurrency());
    
$result = $order->save();
    if (!$result->isSuccess())
        {
            //$result->getErrors();
        }
/*
if(count($basket)<=0)
     $arErrors[]="Ваша корзина пуста";
 // если пользователь авторизован, можно просто использовать $USER->GetID()
 
 if(!!$phone && !($userId=\Partner\OrderUser::getByPhone($phone,array('EMAIL'=>$email,'FIO'=>$fio)))){
      if($ex = $APPLICATION->GetException())
         $arErrors[]="Ошибка регистрации пользователя: ".$ex->GetString();
     else
         $arErrors[]="Ошибка регистрации пользователя: ".$phone;        
}

$deliveryId = 1; // ID службы доставки, можно также использовать \Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId()
if(!$arErrors){
     if($order = \Bitrix\Sale\Order::create($siteId,$userId,$currency)){        
 
         $order->setPersonTypeId(1);    
         $order->setBasket($basket);        
         $basketSum = $order->getPrice();
        // shipment
         $shipmentCollection = $order->getShipmentCollection();
         $shipment = $shipmentCollection->createItem();
         $service = Bitrix\Sale\Delivery\Services\Manager::getById($deliveryId);
         $delivery = $service['NAME'];

         $shipment->setFields(array(
 
             'DELIVERY_ID' => $service['ID'],
 
             'DELIVERY_NAME' => $service['NAME'],
 
         ));
 
         $shipmentItemCollection = $shipment->getShipmentItemCollection();
 
         foreach ($basket as $item)
 
         {
 
             $shipmentItem = $shipmentItemCollection->createItem($item);
 
             $shipmentItem->setQuantity($item->getQuantity());
 
         }
         //shipment 
         // order properties

         $propertyCollection = $order->getPropertyCollection();
         $propertyCodeToId = array();
         foreach($propertyCollection as $propertyValue)
             $propertyCodeToId[$propertyValue->getField('CODE')] = $propertyValue->getField('ORDER_PROPS_ID');

        $propertyValue=$propertyCollection->getItemByOrderPropertyId($propertyCodeToId['FIO']);
 
         $propertyValue->setValue($fio);
 
     
 
         $propertyValue=$propertyCollection->getItemByOrderPropertyId($propertyCodeToId['PHONE']);
 
         $propertyValue->setValue($phone);
 
     
 
         $propertyValue=$propertyCollection->getItemByOrderPropertyId($propertyCodeToId['EMAIL']);
 
         $propertyValue->setValue($email);
 
             
 
         //order properties
         $order->doFinalAction(true);
 
         $result = $order->save();
 
         if($result->isSuccess())
 
         {
 
             $orderId = $order->getId();
 
         
 
             //$order = Order::load($orderId);
              $accountNumber = $order->getField('ACCOUNT_NUMBER'); // генерируемый номер заказа
 
             // можно выполнить обработчики из sale.order.ajax для обеспечения совместимости
 
             // foreach (\GetModuleEvents('sale', 'OnSaleComponentOrderOneStepComplete', true) as $arEvent) 
 
                 // \ExecuteModuleEventEx($arEvent, array($order->getId(), $order->getFieldValues(), array()));
 
         }
         else   $arErrors[] = "Ошибка создания заказа: ".implode(", ",$result->getErrorMessages());
     }
  }
  */
?>