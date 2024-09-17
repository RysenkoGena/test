<html>  <head> <meta http-equiv="refresh" content="66" /> </head><boby>
<?PHP

$conn = new mysqli("192.168.9.6", "gena", "nolnol", "asteriskcdrdb");
if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);
//else echo "OK<br>";
$channels = array();
$queryChannels = "SELECT did FROM cdr WHERE 1 GROUP BY did;";
$resultChannels = $conn->query($queryChannels);
while($res = $resultChannels -> fetch_assoc()){
  $channels[] = $res["did"];
}

//debug($channels);

$query = "SELECT calldate FROM cdr WHERE calldate > DATE_SUB(NOW(), INTERVAL 365 DAY) AND disposition = 'ANSWERED' GROUP BY Year(calldate), MONTH(calldate) ORDER BY calldate ASC;";

      
      //echo $query."<br>";
      $result = $conn->query($query);
      echo "<b>Количеcтво принятых внешний звонков, помесячно. <br>
      <a href=index.php>В разрезе менеджеров</a><br><a href=../m2.php>Отчет по дням</a></b><br><br>";
      echo "<table  border=1><tr>";
      while($res = $result -> fetch_assoc()){
          $n=0;
          echo "<td  valign = top>";
          $item = array(); 
          echo "<b>".date('m.Y', strtotime($res["calldate"]))."</b>";
          $dataStart = date('Y-m-01 00:00:00', strtotime($res["calldate"]));
          $dataEnd   = date('Y-m-t 23:59:59', strtotime($res["calldate"]));
          $query2 = "SELECT did, calldate FROM cdr WHERE disposition = 'ANSWERED' AND did != '' AND calldate > '".$dataStart."' AND calldate < '".$dataEnd."' AND dstchannel != '';";
          //echo $query2;
          $result2 = $conn->query($query2);
          //echo mysqli_num_rows($result2);
          while($res2 = $result2 -> fetch_assoc()){
            if($res2["did"] == "s") $res2["did"] = "8-800";
            $item[$res2["did"]]++;
            $rating[$res2["did"]]++;
            $n++;
          }
          echo "<br><br>";
          //debug ($item);
          //rsort($item, SORT_NUMERIC);
          uasort($item, 'cmp');
          //debug ($item);
          echo "<table>";
          foreach($item as $i => $j){
            echo "<tr><td>[".$i."] <td align=right>".$j."";
          }
          echo "</table>";
          //echo "<b>Итого: ".$n."</b>";
          $itogo[] = $n;
      }
      echo "<tr>";
      foreach($itogo as $i){
        echo "<td><b>".$i."</b>";
      }
      
      echo "</table>";
      echo"<table><tr><td>";

      echo "</table>";
      uasort($rating, 'cmp');
      echo "<br><b>Счетчики за 12 месяцев:</b> <br><table>";
      foreach($rating as $i => $j){
        echo "<tr><td>[".$i."] ".$maping[$i]."<td align=right>".$j;
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