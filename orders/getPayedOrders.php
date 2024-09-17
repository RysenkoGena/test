<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/../..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

ob_end_flush(); //отключить буферизацию

$orderFilter = array('STATUS_ID' => "F");

$params = array(
    'filter' => $orderFilter,
    //'select' => array(        'ID', 'XML_ID',    ),
    'runtime' => [
        new \Bitrix\Main\Entity\ReferenceField(
            'OFFLINE',
            '\Bitrix\Sale\Internals\OrderPropsValueTable',
            array(
                '=this.ID' => 'ref.ORDER_ID',
                '=ref.CODE' => new \Bitrix\Main\DB\SqlExpression('?s', 'OF')
            )
        ),
    ]
);

$rs = \Bitrix\Sale\Internals\OrderTable::getList($params);
$count = $rs->getSelectedRowsCount();
echo $count.PHP_EOL;
$i = 0; $text = ""; $month = ""; $sum = 0; $count=0;
while($r = $rs->fetch())
{
    $i++;
    if(str_contains($r["XML_ID"], "-"))
        continue;
    //if($r["PROP__OFFLINE"] == "Y") continue;
    //var_export($r);
    //print_r($r);
    /*$ordr = \Bitrix\Sale\Order::load($r["ID"]);
    $collection = $ordr -> getPropertyCollection();
    $propertyValue = $collection->getItemByOrderPropertyId(36); //свойство "Офлайн заказ"*/
    //$r = $propertyValue->setField('VALUE', 'Y');
    //$result = $ordr->save();

    //echo $i ." из ".$count." ". $order["XML_ID"].PHP_EOL;
    //echo "№ ". $r["XML_ID"]. " ". $r["PRICE"]. " ". $r["DATE_STATUS"]->value->date.PHP_EOL;
    if($month != date("01.m.Y", $r["DATE_INSERT"]->getTimestamp())) {
        //$text .= $r["XML_ID"] . "\t" . round($r["PRICE"], 2) . "\t" . date("d.m.Y", $r["DATE_STATUS"]->getTimestamp()) . "\t" . date("d.m.Y", $r["DATE_INSERT"]->getTimestamp()) . PHP_EOL;
        $text .= $month ."\t".$sum."\t".$count.PHP_EOL;
        $sum = round($r["PRICE"], 2);
        $month = date("01.m.Y", $r["DATE_INSERT"]->getTimestamp());
        $count = 1;
    }
    else{
        $count++;
        $sum += round($r["PRICE"], 2);
    }
    //print_r($r);


    //if ($i> 10) break;
}
file_put_contents("list.csv", $text);
/*

$dbRes = \Bitrix\Sale\Order::getList(
    array(
    'select' => ["ID", "XML_ID", "PRICE", "DATE_INSERT", "STATUS_ID", "PROP__SOMEPROPERTY"],
    'filter' => [
        'XML_ID' => "%-%",
        'PROP__SOMEPROPERTY' => 'Y'
    ],
     )
);
$count = $dbRes->getSelectedRowsCount();
echo $count.PHP_EOL;
$i=0; $month = ""; $summ = 0;

while ($order = $dbRes->fetch()){
    //break;
    //print_r($order);
    //if(strlen($order["XML_ID"]) < 7) continue;
    //if($order["STATUS_ID"] != "F") continue;
    $i++;
    //echo $order["ID"];
    $ordr = \Bitrix\Sale\Order::load($order["ID"]);
    $collection = $ordr -> getPropertyCollection();
    $propertyValue = $collection->getItemByOrderPropertyId(36); //свойство "Офлайн заказ"
    //print_r($propertyValue);
    //break;
        //echo strlen($order["XML_ID"]);
    //debug($order);

    //if($month == "") $month = $order["DATE_INSERT"]->format("01.m.Y");


    //echo $order["DATE_INSERT"]->format("m.Y")." ".$order["PRICE"]." ".$order["XML_ID"]."<br>";
    //if($i++ >10) break;
    echo $i ." из ".$count." ". $order["XML_ID"].PHP_EOL;
    //$ordr = \Bitrix\Sale\Order::load($order["ID"]);
    //$collection = $ordr -> getPropertyCollection();
    //$propertyValue = $collection -> getItemByOrderPropertyId('36'); //свойство "Офлайн заказ"
    $r = $propertyValue->setField('VALUE', 'Y');
    $result = $ordr->save();
}
//echo $i;
//debug($dbRes);*/