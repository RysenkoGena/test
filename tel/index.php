<html>
<head></head>
<body>
<?PHP

$conn = new mysqli("192.168.9.6", "gena", "nolnol", "asteriskcdrdb");
if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);

/*$channels = array();
$queryChannels = "SELECT dst FROM cdr WHERE LENGTH(dst) = 3 GROUP BY dst;";
$resultChannels = $conn->query($queryChannels);
while($res = $resultChannels -> fetch_assoc()){
  $channels[] = $res["dst"];
}*/
//debug($channels);

$query = "SELECT calldate FROM cdr WHERE calldate > DATE_SUB(NOW(), INTERVAL 365 DAY) AND disposition = 'ANSWERED' AND dcontext = 'ext-local' AND LENGTH(src) > 10 GROUP BY Year(calldate), MONTH(calldate) ORDER BY calldate ASC;";
      
      //echo $query."<br>";
      $result = $conn->query($query);
      echo "<b>Количеcтво принятых внешний звонков, помесячно.<br>
      <a href=ext.php>В разрезе входящих каналов</a><br>
      <a href=donabor.php>Кому звонят используя добавочный номер</a><br>
      <a href=../m2.php>Отчет по дням</a></b><br><br>";
      echo"<table><tr><td>";
      echo "<table  border=1><tr>";
      $reaction = array();
      while($res = $result -> fetch_assoc()){
          $n=0;
          echo "<td  valign = top>";
          $item = array(); 
          echo "<b>".date('m.Y', strtotime($res["calldate"]))."</b>";
          $dataStart = date('Y-m-01 00:00:00', strtotime($res["calldate"]));
          $dataEnd   = date('Y-m-t 23:59:59', strtotime($res["calldate"]));
          $query2 = "SELECT dst, calldate, dstchannel, duration, billsec FROM cdr WHERE LENGTH(dst) <= 4 AND disposition = 'ANSWERED' AND dstchannel != '' AND (dcontext = 'ext-local' OR dcontext = 'ext-queues') AND LENGTH(src) > 10 AND calldate > '".$dataStart."' AND calldate < '".$dataEnd."' GROUP BY `linkedid`;";
          //echo $query2;
          $result2 = $conn->query($query2);
          //echo mysqli_num_rows($result2);

          while($res2 = $result2 -> fetch_assoc()){
            $react = $res2["duration"] - $res2["billsec"];
            if(strlen($res2["dst"]) == 4) $res2["dst"] = substr($res2["dstchannel"], 6, 3);
            $item["доб. ".$res2["dst"]]++;
            $rating[$res2["dst"]]++;
            $reaction[$res2["dst"]] += $react;
            $n++;
            //if($res2["dst"] == 592) echo $react;
          }

          echo "<br><br>";
          //debug ($item);
          //rsort($item, SORT_NUMERIC);
          uasort($item, 'cmp');
          //debug ($item);
          foreach($item as $i => $j){
            echo "<table width=100%><tr><td>[".$i."]<td align=right><b>".$j."</b></table>";
          }
          //echo "<b>Итого: ".$n."</b>";
          $itogo[] = $n;
      }
      echo "<tr>";
      foreach($itogo as $i){
        echo "<td><b>".$i."</b>";
      }
      
      echo "</table><td>";
      //debug ($reaction);


      $maping = [
      501 => "Савельев",
      507 => "Поцелуев",
          510 => "Гончарова",
      519 => "Российская розница",
      526 => "Савельева",
      527 => "Ванжа",
          530 => "Козенец",
      534 => "Терещенко",
      535 => "Кирикова",
      537 => "Ельдин",
      540 => "Тарасов",
      541 => "Семилетко",
      549 => "Сарана",
      550 => "Тестовая трубка",
      556 => "Фролова",
      557 => "Дергач",
      558 => "Карапетян",
      565 => "Мальцев",
      566 => "Очиченко",
      567 => "Степанова",
      571 => "Российская юрлица",
      572 => "Фоменко",
      574 => "Ареховка",
      583 => "Дацко",
      585 => "Литвак",
      587 => "Терещенко",
      588 => "Долгова",
      592 => "Ареховка",
      593 => "Колодочка",
      594 => "Солопанова",
      595 => "Курипка",
      596 => "Онежская",
      599 => "Тестовая трубка",
      497 => "Российская, общий номер",
      498 => "Сбыт, общий номер",
      494 => "Запад, общий номер"
      ];

      foreach($maping as $tel => $manager){
        echo $tel." - ".$manager."<br>";
      }
      echo "</table>";

      uasort($rating, 'cmp');
      echo "<br><b>Счетчики за 12 месяцев:</b> <br><table>";
      foreach($rating as $i => $j){
        echo "<tr><td>[".$i." ".$maping[$i]."] <td><b>".$j."</b><td> (средняя реакция <b>".round($reaction[$i]/$j, 1)."</b> сек. )<br>";
      }
      echo "</table>";
 


      

$conn->close();
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
?>
  </body>
</head>