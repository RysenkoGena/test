<?PHP
 $mapRigs = array(
  "2646853" => ["rigName" => "Farm01", "NumberOfCards" => 8],
  "5112298" => ["rigName" => "Farm02", "NumberOfCards" => 10],
  "5326653" => ["rigName" => "Farm03", "NumberOfCards" => 8],
  "5326771" => ["rigName" => "Farm04", "NumberOfCards" => 6],
  "2803639" => ["rigName" => "Farm05", "NumberOfCards" => 3],
  "2660225" => ["rigName" => "Farm06", "NumberOfCards" => 5],
  "2656611" => ["rigName" => "Farm07", "NumberOfCards" => 4],
  "5340984" => ["rigName" => "Farm09", "NumberOfCards" => 6],
  "2676150" => ["rigName" => "Farm10", "NumberOfCards" => 2],
  "2834371" => ["rigName" => "Farm11", "NumberOfCards" => 4],
  "3013341" => ["rigName" => "Farm12", "NumberOfCards" => 4], //yWyXe2JX
  "3042221" => ["rigName" => "Farm13", "NumberOfCards" => 4],
  "3056597" => ["rigName" => "Farm14", "NumberOfCards" => 3],
  "5341078" => ["rigName" => "Farm15", "NumberOfCards" => 8],
  "3101889" => ["rigName" => "Farm16", "NumberOfCards" => 4]
);
//file_put_contents(__DIR__ . '/access.txt', date("Y-m-d H:i:s")." Посетитель ".$_SERVER['REMOTE_ADDR']."\n", FILE_APPEND); //логирование

$post = file_get_contents("php://input");
if($post == ""){
  echo "<html><head><meta http-equiv=refresh content=66 /></head><boby>";
  if(isset($_GET["rig"]) && $_GET["rig"] != ""){
    $rig = $_GET["rig"];
    $get = "?rig=".$rig;
    echo "График ".$rig."</br>";
  } else $get = "";

  echo "<img src=2.php".$get."><br>";
  $conn = new mysqli("localhost", "useryugkabel", "UIV1%3)}eCc}C+v", "hiveos");
  if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);
  $query = "SELECT rig, hashrate, date, NumberOfCards FROM hashrate WHERE date > NOW() - INTERVAL 2 MINUTE GROUP BY rig ORDER BY `id` DESC;";
      //echo $query."<br>";
  $result = $conn->query($query);
  $hr = array(); $rigs = array(); $numberOfCards = 0; $hashrate = 0;
  while($res = $result -> fetch_assoc()){
    $rigs[$res["rig"]]["numberOfCards"] = $res["NumberOfCards"];
    $numberOfCards += $res["NumberOfCards"];
    $hashrate += $res["hashrate"];
    $rigs[$res["rig"]]["hashrate"] = $res["hashrate"];
    //print_r(date("g.i", strtotime($res["date"])));
    //$hr[date("H.i", strtotime($res["date"]))][] = $res["hashrate"]." ".$res["date"];
    //$summ += $res["hashrate"];
    //break;
  }
  //file_put_contents(__DIR__ . '/requests.txt', print_r($query, true)."\n", FILE_APPEND);
  $conn->close();
  $i =  0;
  foreach($day as $da => $hr){
    $i++;
    if($i < 60){
      continue;
    }
    else $i = 0;
    //$xdata[] =  (int)$da;
    $ydata[] = $hr;
  }

  $i = 0; $online = array(); //$numberOfCards = array();
  foreach($mapRigs as $rig){
    $fileTime = filemtime(__DIR__.'/stats/'.$rig["rigName"].".txt");
    if($fileTime > (time() - 5*60))    $i++;
        if((time() - $fileTime) < 180) $online[$rig["rigName"]] = "";
        else $online[$rig["rigName"]] = (time() - $fileTime);
    $rigs[$rig["rigName"]]["numberOfCardsConst"] = $rig["NumberOfCards"];
  }
  //debug($rigs);
  echo "<br><a href='/test/hiveos/'>Онлайн:</a> ". $i. "/". count($mapRigs)."<br><br>";
  echo "<table style='text-align:right; padding:10px;'><th>Риг<th>Простой, м<th>Карт, шт<th>H/r, Mh";
  $totat_hr = 0;
  foreach($online as $rig => $time){
    if($rigs[$rig]["numberOfCards"] < 1) $textColor = " style='background-color:yellow;'";
    else $textColor = "";
    if($time < 180) $time = "";
    else $time = round(($time/60));
    $cards = $rigs[$rig]["numberOfCards"] - $rigs[$rig]["numberOfCardsConst"];
    if($cards == 0) $cards = "";
    //else $cards = 
    $righr = round(($rigs[$rig]["hashrate"])/1000);
    echo "<tr".$textColor."><td><a href='?rig=".$rig."'>".$rig ."</a><td style='text-color: red;'> ".$time."<td>".$cards ."<td>".$righr;
    $totat_hr += $righr;
  }
  echo "<tr><td colspan = 1> Всего карт: ".$numberOfCards. ".<td colspan = 3> Всего хэшрейт: <b>".$totat_hr."</b></table></body></head>";

}

else { //обработка информации от ригов
  $post=json_decode($post, true);
  if(array_key_exists($post["params"]["rig_id"], $mapRigs)){
    $rig = $mapRigs[$post["params"]["rig_id"]]["rigName"];
    $numberOfCards = 0;
    foreach($post["params"]["miner_stats"]["hs"] as $hs) if($hs > 0) $numberOfCards ++;
  }  else $rig = $post["params"]["rig_id"];

  //debug ($rig);
  $conn = new mysqli("localhost", "useryugkabel", "UIV1%3)}eCc}C+v", "hiveos");
  if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);
  else echo "Подключеие к базе OK".PHP_EOL;
  
  $query="INSERT INTO hashrate (rig, hashrate, NumberOfCards) VALUES ('".$rig."', ".$post["params"]["total_khs"].", ".$numberOfCards.");";
    //file_put_contents(__DIR__ . '/requests.txt', print_r($query, true)."\n", FILE_APPEND);
  //echo $query;

  $result = $conn->query($query);

  if(date("H") == "1" && date("i") == "1"){ //очистим старые записи в БД раз в день
    $q = "DELETE FROM hashrate WHERE `date` < NOW() - INTERVAL 7 DAY;";
    $result = $conn->query($q);
  }
  
  $conn->close();

  $text = date("d.m.Y H:i:s ").$rig.": ";
    foreach($post["params"]["temp"] as $temp){
      if($temp > 75){
        sendTMessage("Перегрев! ". $temp. " ".$rig);
        echo $rig." Перегрев\n";
      }
      $text .= $temp." ";
    }
    $text .= "\n";

    //}else echo "TEST\n";
    //file_put_contents(__DIR__ . '/requests.txt', print_r($post, true)."\n", FILE_APPEND);

  if($post["params"]["total_khs"] != 0) file_put_contents(__DIR__ . '/stats/'.$rig.'.txt', $text);
  file_put_contents(__DIR__ . '/log.txt', $text, FILE_APPEND);

}
//print_r ($post);

function sendTMessage($text){
      $chats = array( 'Gena' => 1569407398  );
  echo "отправка сообщения: ".$text."\n";
  foreach($chats as $chat){
      $response = array(
          'chat_id' => $chat,
          'text' => $text
      );
      $token = '5145589614:AAGsVuSeZrXnjpHmJvL0PA-lSuDFalqsupU';
      $ch = curl_init('https://api.telegram.org/bot' . $token . '/sendMessage');  
      curl_setopt($ch, CURLOPT_POST, 1);  
      curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_exec($ch);
      curl_close($ch);
  }
}
function debug($array){
  ?><pre><?
  print_r($array);
  ?></pre><?
}
?>
