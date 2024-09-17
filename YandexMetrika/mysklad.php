<?php
$_SERVER["DOCUMENT_ROOT"] = __DIR__."/../..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>

<?php
//$counter = "135067";            // Укажите номер счетчика
$token = "YWRtaW5AYWRtaW4zNTIxOjEycXdhc3p4";              // Укажите OAuth-токен

$curl = curl_init("https://api.moysklad.ru/api/remap/1.2/entity/customerorder");
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic $token", "Accept-Encoding: gzip"));
curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = json_decode(curl_exec($curl));
curl_close($curl);


$rows = $result-> rows;
echo "<h1>Список заказов</h1>";

foreach ($rows as $row)
    $orders[strtotime($row->created)] = $row;

krsort($orders); # пункт 6. Сортировка

foreach ($orders as $order){
    $states = requestApi($order->state->meta->metadataHref)->states;
    break;
}

$list = "<select>";
foreach ($states as $state){
    $list .= "<option>".$state->name."</option>";
}
$list .="</select>";

//$allStatus = requestApi($orders[0]->state->meta->metadataHref);

//debug($allStatus);

echo "<table style='border-spacing: 10px; border-collapse: separate;'>";
foreach ($orders as $order){
    $status = requestApi($order->state->meta->href)->name;
    //$allStatus = requestApi($order->state->meta->metadataHref)->states;
    //https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata
    //echo "<tr><td><a href='".$order->meta->uuidHref."' target='_blank'>".$order->name."</a><td><a href='".$order->agent->meta->uuidHref."' target='_blank'>".requestApi($order->agent->meta->href)->name."</a><td>".$status.$list;?>
  <tr>
      <td><a href="<?=$order->meta->uuidHref?>" target='_blank'><?=$order->name?></a></td>
      <td><?=date("d.m.Y H:i", strtotime($order->moment))?></td>
      <td><a href='<?=$order->agent->meta->uuidHref?>' target='_blank'><?=requestApi($order->agent->meta->href)->name?></a></td>
      <td><?=requestApi($order->organization->meta->href)->name?></td>
      <td><?=number_format(($order->sum)/100, 2)?></td>
      <td><?=$status?></td>
      <td><?=date("d.m.Y H:i", strtotime($order->updated))?></td>
  </tr>
<?}
echo "</table>";

//debug($result);


function requestApi($url){
    global $token;
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic $token", "Accept-Encoding: gzip"));
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = json_decode(curl_exec($curl));
    curl_close($curl);
    return $result;
}