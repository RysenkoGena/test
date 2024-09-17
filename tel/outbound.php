<!DOCTYPE html>
<html>
    <head>
        <title>Журнал записей разговоров внешних исходящих звонков (с начала 2023 года)</title>
    </head>
<body>
<h1>Журнал записей разговоров внешних исходящих звонков</h1>
<?PHP

$conn = new mysqli("192.168.9.6", "gena", "nolnol", "asteriskcdrdb");
if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);
//else echo "OK<br>";
//echo "123";
$abonents = array();
//print_r($abonents);
$queryAbonents = "SELECT * FROM cdr WHERE src LIKE '5__' AND 'recordingfile' IS NOT NULL AND 'recordingfile' != '' group by src;";
//echo $queryAbonents;
$resultChannels = $conn->query($queryAbonents);
while($res = $resultChannels -> fetch_assoc()){
  $abonents[] = $res["src"];
  $abonentNames[$res["src"]] = $res["clid"];
}
//print_r($abonentNames);
//if(count($abonents)){
?>
<form autocomplete="off">
    <select onchange="this.form.submit()" name="abonent">
        <option>Выберите номер</option>
        <?

        foreach ($abonents as $abonent){
            if(isset($_GET["abonent"]) && $_GET["abonent"] == $abonent)
                $selected = " selected ";
            else
                $selected = "";
            ?>
           <option <?=$selected?> value=<?=$abonent?>><?=$abonentNames[$abonent]?></option>
        <?}
        ?>
    </select>
</form>

<?//}
//else echo "Внешних исходящих звонков с данного номера не совершалось";


if(isset($_GET["abonent"]) && $_GET["abonent"] != ""){
    $query = "SELECT * FROM `cdr` WHERE calldate > '2023-01-01' AND `src` LIKE '".$_GET["abonent"]."' AND LENGTH(dst) > 6 AND recordingfile IS NOT NULL AND recordingfile != '' ORDER BY calldate DESC LIMIT 1000";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {?>
        <br>Найдено записей: <?=$result->num_rows?><br><?
        echo "<table><tr><th>Дата</th><th>Запись</th><th>Куда звонили</th></tr>";
        while ($res = $result->fetch_assoc()) {
            $dayRecords = "/" . date("Y", strtotime($res["calldate"])) . "/" . date("m", strtotime($res["calldate"])) . "/" . date("d", strtotime($res["calldate"]));
            $pathToWave = "/test/asteriskRecords" . $dayRecords . "/";

            echo "<tr><td>" . date("d.m.y G:i:s", strtotime($res["calldate"])) . "</td><td><audio controls><source src=" . $pathToWave . $res["recordingfile"] . "></audio></td><td>" . $res["dst"] . "</td></tr>";
        }
        echo "</table>";
    }
    else echo "Внешних исходящих звонков с данного номера не совершалось";
}?>

  </body>
</head>