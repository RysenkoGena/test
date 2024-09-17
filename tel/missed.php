<?PHP
$array = file("hangup.txt");
$sotrudnik = [];
$jsonData = file_get_contents('https://portal.yugkabel.ru/userTel.php');
$data = json_decode($jsonData, true);

foreach ($array as $string){
    if(substr($string,0, 10) != date("Y-m-d")) continue;
    $items = explode("\t", $string);
    if(strlen($items[6]) !=3 ) continue;
    $sotrudnik[$items[6]]++;
}
arsort($sotrudnik);
$total = 0;
foreach ($sotrudnik as $key=>$item){
    $total += $item;
}
$mediana = ceil($total/count($sotrudnik));

$text = "Отчет о пропущенных звонках за день:".PHP_EOL;
$text .= "Всего пропущено: ". $total.PHP_EOL.PHP_EOL;
$other = 0;
foreach ($sotrudnik as $key=>$item){
    if($data[$key]) $fio = $data[$key];
    else $fio = $key;
    if($item <= $mediana){
        $other += $item;
        continue;
    }
    $text .= $key." ".$fio.": ".$item.PHP_EOL;
}
$text .= "Остальные: ".$other;
echo "<pre>";
echo $text;
echo "</pre>";

function debug($array){
  ?><pre><?
  print_r($array);
  ?></pre><?
}
function cmp($a, $b) {
  if ($a == $b) {
      return 0;
  }
  return ($a > $b) ? -1 : 1;
}
