<html>  <head> <meta http-equiv="refresh" content="66" /> </head><boby>
<?PHP

$conn = new mysqli("192.168.9.6", "gena", "nolnol", "asteriskcdrdb");
if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);
//else echo "OK<br>";
$channels = array();
$queryChannels = "SELECT dst FROM cdr WHERE LENGTH(dst) = 3 GROUP BY dst;";
$resultChannels = $conn->query($queryChannels);
while($res = $resultChannels -> fetch_assoc()){
  $channels[] = $res["dst"];
}

//debug($channels);

$query = "SELECT calldate FROM cdr WHERE calldate > DATE_SUB(NOW(), INTERVAL 365 DAY) AND disposition = 'ANSWERED' AND dcontext = 'ext-local' AND LENGTH(src) > 10 GROUP BY Year(calldate), MONTH(calldate) ORDER BY calldate ASC;";
      
      //echo $query."<br>";
      $result = $conn->query($query);
      echo "<b>Кому звонят используя добавочный номер<br>
      <a href=index.php>Статистика входящих звонков разрезе менеджеров</a><br>
      <a href=ext.php>Статистика входящих звонков разрезе входящих каналов</a><br>
      <a href=../m2.php>Отчет по дням</a></b><br><br>";
      echo "<table  border=1><tr>";
      while($res = $result -> fetch_assoc()){
          $n=0;
          echo "<td  valign = top>";
          $item = array(); 
          echo "<b>".date('m.Y', strtotime($res["calldate"]))."</b>";
          $dataStart = date('Y-m-01 00:00:00', strtotime($res["calldate"]));
          $dataEnd   = date('Y-m-t 23:59:59', strtotime($res["calldate"]));
          //$query2 = "SELECT dst, calldate FROM cdr WHERE LENGTH(dst) = 3 AND disposition = 'ANSWERED' AND dcontext = 'ext-local' AND LENGTH(src) > 10 AND calldate > '".$dataStart."' AND calldate < '".$dataEnd."';";
          $query2 = "SELECT dst, calldate FROM cdr WHERE LENGTH(dst) = 3 AND disposition = 'ANSWERED' AND did != '' AND LENGTH(src) > 10 AND calldate > '".$dataStart."' AND calldate < '".$dataEnd."';";
          //echo $query2;
          $result2 = $conn->query($query2);
          //echo mysqli_num_rows($result2);
          while($res2 = $result2 -> fetch_assoc()){
            $item["доб. ".$res2["dst"]]++;
            $rating[$res2["dst"]]++;
            $n++;
          }
          echo "<br><br>";
          //debug ($item);
          //rsort($item, SORT_NUMERIC);
          uasort($item, 'cmp');
          //debug ($item);
          foreach($item as $i => $j){
            echo "[".$i." ".$j."]<br>";
          }
          //echo "<b>Итого: ".$n."</b>";
          $itogo[] = $n;
      }
      echo "<tr>";
      foreach($itogo as $i){
        echo "<td><b>".$i."</b>";
      }
      
      echo "</table>";
      echo"<table><tr><td>";

      $maping = [
        174 => "Ареховка",
        196 => "Онежская",
        434 => "Терещенко",
        461 => "Подгирная",
        438 => "Куликов",
        445 => "Наумова",
      501 => "Савельев",
      507 => "Поцелуев",
      519 => "Российская розница",
      526 => "Савельева",
      534 => "Терещенко",
      535 => "Кирикова",
      540 => "Тарасов",
      541 => "Семилетко",
      549 => "Сарана",
      550 => "Тестовая трубка",
      556 => "Фролова",
      557 => "Тадевосян",
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
      592 => "Ареховка",
      593 => "Колодочка",
      594 => "Солопанова",
      595 => "Курипка",
      596 => "Онежская",
      599 => "Тестовая трубка"
      ];

      foreach($maping as $tel => $manager){
        echo $tel." - ".$manager."<br>";
      }
      echo "</table>";
      uasort($rating, 'cmp');
      echo "<br><b>Счетчики за 12 месяцев:</b> <br>";
      foreach($rating as $i => $j){
        echo "[".$i."] ".$maping[$i]." ".$j."<br>";
      }
      
 


      

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