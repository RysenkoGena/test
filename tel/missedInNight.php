<?PHP

$conn = new mysqli("192.168.9.6", "gena", "nolnol", "asteriskcdrdb");
if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);

          $n=0;
          if(isset($_GET["y"]) && $_GET["y"] != "" && $_GET["y"]>2020 && $_GET["y"] <= 2100
              && isset($_GET["m"]) && $_GET["m"] != "" && $_GET["m"] > 0 && $_GET["m"]<=12)
              $date = $_GET["y"]."-".$_GET["m"]."-10 11:59:32";
          else $date = date('Y-m-01 00:00:01');
          //echo $date."<br>";
          $res["calldate"] = "2023-06-14 11:59:32";
            $res["calldate"] = $date;
          $dataStart = date('Y-m-01 00:00:00', strtotime($res["calldate"]));
          $dataEnd   = date('Y-m-t 23:59:59', strtotime($res["calldate"]));
          $query2 = "SELECT src FROM cdr WHERE LENGTH(dst) <= 4 AND disposition = 'ANSWERED' AND dstchannel != '' AND (dcontext = 'ext-local' OR dcontext = 'ext-queues') AND LENGTH(src) > 10 AND calldate > '".$dataStart."' AND calldate < '".$dataEnd."' GROUP BY `linkedid`;";
          //echo $query2."<br>";
          $result2 = $conn->query($query2);
          //echo mysqli_num_rows($result2)."<br>";

          while($res2 = $result2 -> fetch_assoc()){
            echo substr($res2["src"], -10).";";
          }

      uasort($rating, 'cmp');

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
