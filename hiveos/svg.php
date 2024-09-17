<?PHP
    $conn = new mysqli("localhost", "useryugkabel", "%%J1lY8D%VotmvL", "hiveos");
    if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);
        //for($i=0; $i < count($X); $i++){
          $query = "SELECT rig, hashrate, date FROM hashrate WHERE date > NOW() - INTERVAL 1 DAY;";
          
          //echo $query."<br>";
          $result = $conn->query($query);
          $hr = array(); $time = time();
          for($i=1440; $i > 0; $i--)
            $hr[date("H:i", $time+=60)] = [];
          //debug($hr);

          while($res = $result -> fetch_assoc()){
            //print_r(date("g.i", strtotime($res["date"])));
            $hr[date("H:i", strtotime($res["date"]))][] = $res["hashrate"];
            //break;
          }
          //echo count($hr);
          //debug($hr);
          $day = array();
          foreach($hr as $hr_m => $m){
            //echo "-<br>";
            foreach($m as $hr_rig){
              $day[$hr_m]  += $hr_rig;
            }
          }
          //debug($day);
    $conn->close();

$i =  0; $y = 0;
foreach($day as $da => $hr){
    $i++;
    if($i < 60){
        continue;
    }
    else $i = 0;
    
    $xdata[] =  $y;
    $y++;
    $ydata[] = $hr;
}
?>

<svg width="1500" height="200" border=1 style="border: 1px solid red;">
    <polyline points="
    <?php
      $x = 20; $max = max($day);
      $min = min($day);
      $point = ($max-$min)/170;

      foreach($day as $hrs){
        $x++;
        $y = 180 - round(($hrs-$min)/$point);
        echo $x.",".$y." ";
      }
      //20,20 40,25 60,40 80,120 120,140 200,180
    ?>
    " style="fill:none;stroke:black;stroke-width:1" />
    <line x1="10" y1="180" x2="1460" y2="180" style="stroke:rgb(100,100,100);stroke-width:1" />
    <line x1="20" y1="10" x2="20" y2="190" style="stroke:rgb(100,100,100);stroke-width:1" />
</svg>

<?php
function debug($array){
  ?><pre><?
  print_r($array);
  ?></pre><?
}
?>