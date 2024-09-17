<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$queue = ["8600", "8606", "8607", "8608", "8609", "8610", "555"];
$dezhurstvo = [
    1 => "Онежская",
    2 => "Дзержинского",
    3 => "Российская",
    0 => "КП",
];

$jsonData = file_get_contents('https://portal.yugkabel.ru/userTel.php');
$humans = json_decode($jsonData, true);

//$queue = "8600";
if(isset($_GET["queue"])) $queue = $_GET["queue"];
if($queue == "8600") $queue = ["8600", "8606", "8607", "8608", "8609", "8610"];
$queues = ["8600" => "8-800-7007400", "8601" => "200-01-01 Элтэк", "8602" => "200-02-02 Онежская", "8603" => "200-81-10 Запад", "8604" => "200-81-11 Российская", "8605" => "200-81-12 ИнМагазин"];
$sql_queues = ["8600" => "Beeline", "8601" => "8612000101", "8602" => "2000202", "8603" => "2008110", "8604" => "2008111", "8605" => "2008112"];
$strs = []; $dates = []; $strings = [];
$day[0] = "Вс";$day[1] = "Пн";$day[2] = "Вт";$day[3] = "Ср";$day[4] = "Чт";$day[5] = "Пт";$day[6] = "Сб";

$pathToDir = $_SERVER['DOCUMENT_ROOT']."/../asterisk/";

if(isset($_GET["file"]) && $_GET["file"] != "")
    $dayRecords = "/".date("Y", $_GET["file"])."/".date("m", $_GET["file"]);
else
    $dayRecords = "/".date("Y")."/".date("m");

$pathToWave = $_SERVER['DOCUMENT_ROOT']."/test/asteriskRecords".$dayRecords;

//$dir = opendir($pathToWave);
$filesRecords = [];

function ls($dir) {
    $filesRecords = [];
    $files = array_diff(scandir($dir), ['.','..']);
    foreach ($files as $file) {
        if(is_dir($dir.'/'.$file))
            $filesRecords = array_merge($filesRecords, ls($dir.'/'.$file));
        else {
            $index = substr(explode("-", $file)[5], 0, -4);
            $filesRecords[$index] = str_replace($_SERVER['DOCUMENT_ROOT'], "",$dir . '/' . $file);
        }
    }
    return $filesRecords;
}
$filesRecords = ls($pathToWave); # заполнить массив с путями к файлам записи
//lg($filesRecords);

if(isset($_GET["file"])){
    $selectedDay = date("d.m.Y", $_GET["file"]);
    $sqlDay = date("Y-m-d", $_GET["file"]);
}
else{
    $selectedDay = date("d.m.Y");
    $sqlDay = date("Y-m-d");
}

if($_GET["diapazon"] == 'on') $selectedDay = " сохраненную историю";

if(is_array($queue)) {
    $text = "8-800";
    $sql_text = "beeline";
}
else {
    $text = $queues[$queue];
    $sql_text = $sql_queues[$queue];
}
echo "<h1>ОТЧЕТ по звонкам на ".$text." за ".$selectedDay."</h1> <a href=tel/>по месяцам</a><br><br>";

$dir = opendir($pathToDir);
if($dir){
    while($el = readdir($dir)){
        if(strpos($el, "queue_") !== false) {
            $strs = array_merge($strs, file($pathToDir.$el));
        }
    }
}
else echo "ошибка открытия каталога ".$pathToDir."<br>".PHP_EOL;

foreach ($strs as $str){
    $unixData =  explode("|", $str)[0];
    //lg($unixData);
    $date = date("d.m.Y",$unixData);
    if(!in_array($date, $dates)) $dates[$unixData] = $date;
    $strings[$date][] = $str;
}
//d($strings);
krsort($dates); # тут список дат
//d($strs);
rsort($strs); # тут список всех строк со всех файлов
//d($dates);

echo "<form><select onChange=this.form.submit() name=file>";
foreach($dates as $unixDate => $date){
    if($_GET["file"] == $unixDate) $selected = " selected"; else $selected = "";
    echo "<option".$selected." value='".$unixDate."'>".$date." ".$day[date("w",$unixDate)];
}

echo "</select><select onChange=this.form.submit() name=queue>";
foreach($queues as $q => $phoneNumber){
    if($_GET["queue"] == $q) $selected = " selected"; else $selected = "";
   echo "<option".$selected." value=".$q.">".$phoneNumber;
}
if($_GET["diapazon"] == 'on') $text = 'checked';
else $text = "";
echo "</select>
    <!--<input type='checkbox' name='diapazon' ".$text." onChange=this.form.submit()> За все дни</input>-->
</form>";


$strings = $strings[$selectedDay]; # выбираем данные только за один выбранный день
$numIncoming = 0; $events = array(); $otvetil = array(); $abandons = array(); $clientNumber = array(); $timeEvents = array();
$waiting = array(); //время ожидания ответа клиенту
$operators = array(); //массив сотрудников
$duration = array(); // длительность разговора
if($_GET["diapazon"] == 'on')  $strings = $strs; // это пока убрали

$conn = new mysqli("192.168.9.6", "gena", "nolnol", "asteriskcdrdb");
if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);
//debug ($queue);
//echo $sql_text;
$query = "SELECT * FROM cdr WHERE channel LIKE '%".$sql_text."%' AND LENGTH(dst) = 3 AND DATE(calldate)='".$sqlDay."';";
$resultChannels = $conn->query($query);
$directDialCount = $resultChannels->num_rows;
while($res = $resultChannels -> fetch_assoc()){
    $direct[] = $res;
}

$queryOutGoing = "SELECT * FROM cdr WHERE dstchannel LIKE '%".$sql_text."%' AND LENGTH(cnum) = 3 AND DATE(calldate)='".$sqlDay."';";
//echo $queryOutGoing."<br>";
$resultOutGoing = $conn->query($queryOutGoing);
$OutGoingCount = $resultOutGoing->num_rows;
while($res = $resultOutGoing -> fetch_assoc()){
    $OutGoing[] = $res;
}


foreach($strings as $string){ # перебираем строки
    //lg($string); // строка типа 1692027099|1692027038.8248|8608|Frolova K.|RINGCANCELED|1837
    $items = explode("|", $string);
    if($items[2] == $queue || (is_array($queue) && in_array($items[2], $queue))){ // отбираем только нужные очереди
        //echo "<br>строка ".$items[2];
        if( $items[4] == "ENTERQUEUE"){
            //$events[] = $items[1];
            $clientNumber[$items[1]] = $items[6];
            $timeEvents[$items[1]] = $items[0];

            //echo date("H:i:s",$items[0])." входящий с ".$items[6]."<br>";
            $numIncoming++;
        }
        elseif($items[4] == "CONNECT"){
            //lg($events);
            $events[$items[2]]["CONNECT"] = $items[3];
            $otvetil[$items[3]][] = $items[1];
            $waiting[$items[1]] = $items[5];
            if(!array_key_exists($items[3], $operators)) $operators[$items[3]] = 0;
            $operators[$items[3]]++;
            //$clientNumber[$items[1]] = $items[6];
        }
        elseif($items[4] == "ABANDON"){
            //if($items[7] == 0) continue; //не учитывать спам звонки
            $abandons[] =  $items[1];
            $waiting[$items[1]] = $items[7];
        }
        elseif($items[4] == "COMPLETECALLER" || $items[4] == "COMPLETEAGENT"){
            $duration[$items[1]] = $items[6];
        }
    }
}
arsort($operators); //сортируем ответчиков по количеству принятых звонков
$otvechennyhZvonkov = array_sum($operators);

if($numIncoming == 0) echo "Звонков за этот день не поступало";
else{
    echo "Всего поступило звонков: <b>".($numIncoming + $directDialCount)."</b><br>На дежурный филиал: <b>".$numIncoming."</b><br>На выбранного сотрудника: <b>".$directDialCount."</b><br><br>";
    echo "<h2>Дежурство филиала ".$dezhurstvo[date("W", $_GET["file"])%4]."</h2>";
    echo "<h2><br>Звонки на дежурный филиал (клиент ждал ответа менеджера)</h2>";
    echo "Отвеченных звонков: <b>".$otvechennyhZvonkov;
    if ($numIncoming > 0) echo " (".round(($otvechennyhZvonkov/$numIncoming)*100, 2)."%)";
    echo "</b><br>";
    echo "<table>";
    echo "<th style='padding:5px;'>Сотрудник <th style='padding:5px;'>Кол-во принятых <th style='padding:5px;'> Медианное время ответа<th>Средний разговор";
    foreach($operators as $agent => $operator){
        //$agent = $operator;
        $event = $otvetil[$agent];
    //foreach($otvetil as $agent => $event){
        $tmp = array();// для вычисления среднего времени ответа
        $tmp2 = array();// для вычисления средней длительности разговора
        echo "<tr>";
        //echo "<table border=0>";
        //lg($event);
        foreach($event as $evnt) {
            $tmp[] = $waiting[$evnt];
            $tmp2[] = $duration[$evnt];
        }
        //debug($tmp);
        echo "<td style='padding:5px;'><span style='color:#264796; cursor: pointer;' onClick=\"ShowDetail('".$agent."')\">".$agent."</a><td style='padding:5px; text-align:center;'>".count($event)."<td style='padding:5px; text-align:center;'>".calculate_median($tmp) ." сек.<td style='padding:5px; text-align:center;'>".setMin(calculate_average($tmp2));
        echo "<tr><td colspan=3><div id='".$agent."' style='display:none; border-style:dotted;'>";
            echo "<table><th>Время<th>клиент<th>время ответа<th style='padding:0 5px;'>Запись разговора";


           // lg($filesRecords);
        foreach($event as $evnt){
            //echo "&emsp;".date("H:i:s", $timeEvents[$evnt])." ".$clientNumber[$evnt]." ждал ответа ".$waiting[$evnt]." сек.<br>";
            echo "<tr>
                    <td>".date("H:i:s", $timeEvents[$evnt]).
                "<td style='padding:0 8px;'>".$clientNumber[$evnt].
                " <td style='padding:5px; text-align:center;'> ".
                $waiting[$evnt]." сек.<td style='padding:5px; text-align:center;'>";//.setMin($duration[$evnt]);?>
            <audio controls><source src="<?=$filesRecords[$evnt]?>"></audio>
        <?php }

        echo "</table>";
        echo "</div>";
    }
    echo "</table>";

    echo "<br>Не дождались ответа: <b>".count($abandons)." (".round((count($abandons)/$numIncoming)*100, 2)."%)</b><br>";
    $date = "";
    $arrAbandos = file(__DIR__ . '/abandos.txt',FILE_IGNORE_NEW_LINES);
    foreach($abandons as $abandon){
        if($date != date("d.m.Y", explode(".",$abandon)[0])) {
            $date = date("d.m.Y", explode(".",$abandon)[0]);
            //echo explode(".",$abandon)[0]."<br>";
            echo "<b>".$date."</b><br>";
        }
        echo "&emsp;".date("H:i:s", $timeEvents[$abandon])." ".$clientNumber[$abandon]." ждал ответа ".$waiting[$abandon]." сек.<br>";
        if(!isset($_GET["file"]) && !in_array($abandon,$arrAbandos)){
            file_put_contents(__DIR__ . '/abandos.txt', $abandon."\n", FILE_APPEND);
        }
    }
}

//debug ($direct);
echo "<h2><br><br>Звонки на выбранного сотрудника (клиент набрал номер сотрудника)</h2>";
$total = 0; $ansvered=0;

foreach ($direct as $item){
    $total++;
    if($item['disposition'] == "ANSWERED") $ansvered++;
}
if($total == 0) echo "Звонков в этот день не поступало";
else {
    echo "Отвеченных звонков: <b>" . $ansvered . "(" . round($ansvered / $total * 100, 2) . "%)</b>";
    echo "<table><th style='padding:5px;'>Время <th style='padding:5px;'>Сотрудник<th style='padding:5px;'>Клиент<th>Статус<th>Запись";

    $status = ["ANSWERED" => "Отвечено", "NO ANSWER" => "Пропущено", "BUSY" => "Занято",];
    $eventIds = [];
    foreach ($direct as $item) {
        if (in_array($item["uniqueid"], $eventIds)) continue;
        if($humans[$item['dst']])
            $manager = $humans[$item['dst']];
        else $manager = $item['dst'];
        echo "<tr><td>" . substr($item['calldate'], -8) . "<td style='padding:5px; text-align:left;'>" . $manager . "<td>" . $item['src'] . "<td style='padding:5px; text-align:right;'>" . $status[$item['disposition']] . "<td style='padding:5px; text-align:center;'>";
        if ($item['disposition'] == "ANSWERED")
            echo "<audio controls><source src=" . $filesRecords[$item["uniqueid"]] . "></audio>";
        $eventIds[] = $item["uniqueid"];
    }
    echo "</table>";
}

echo "<h2><br><br>Исходящие звонки клиентам на сотовые</h2>";
if($OutGoingCount == 0) echo "Исходящих звонков в этот день не было";
else{
    echo "Исходящих звонков: <b>" . $OutGoingCount."</b>";// . "(" . round($ansvered / $total * 100, 2) . "%)</b>";
    echo "<table><th style='padding:5px;'>Время <th style='padding:5px;'>Сотрудник<th style='padding:5px;'>Клиент<th>Статус<th>Запись";

    $status = ["ANSWERED" => "Отвечено", "NO ANSWER" => "Клиент не ответил", "BUSY" => "Занято",];
    //$eventIds = [];
    foreach ($OutGoing as $item) {
        //if (in_array($item["uniqueid"], $eventIds)) continue;
        echo "<tr><td>" . substr($item['calldate'], -8) . "<td style='padding:5px; text-align:center;'>" . $humans[$item['cnum']] . "<td>" . $item['dst'] . "<td style='padding:5px; text-align:right;'>" . $status[$item['disposition']] . "<td style='padding:5px; text-align:center;'>";
        if ($item['disposition'] == "ANSWERED")
            echo "<audio controls><source src=" . $filesRecords[$item["uniqueid"]] . "></audio>";
        $eventIds[] = $item["uniqueid"];
    }
    echo "</table>";
}


function sendTMessage($text){
    echo "отправка сообщения: ".$text."\n";
    $response = array(
        'chat_id' => 1569407398,
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

function setMin($sec){
    $min = $sec/60;
    if(substr_count($min , '.')) $min  = explode('.', $min)[0];
    $sec = $sec%60;
    if (strlen($sec) == 1) $sec = "0".$sec;
    return $min.":".$sec;
}

function calculate_median($arr):float {
    $count = count($arr); //total numbers in array
    $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
    if($count % 2) { // odd number, middle is the median
        $median = $arr[$middleval];
    } else { // even number, calculate avg of 2 medians
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = (($low+$high)/2);
    }
    return round($median, 2);
}

function calculate_average($arr):float {
    $total = 0;
    $count = count($arr); //total numbers in array
    foreach ($arr as $value) {
        $total = $total + $value; // total value of array numbers
    }
    $average = ($total/$count); // get average value
    return round($average, 2);
}
?>
<script>
function ShowDetail(agent){
    console.log(agent);
    if(document.getElementById(agent).style.display === 'none')
           document.getElementById(agent).style.display = 'block';
    else document.getElementById(agent).style.display = 'none';
}
</script>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>