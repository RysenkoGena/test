<?PHP
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//echo "Модуль ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"." загружен.\r\n";
    include(dirname(__FILE__)."/pCashe/pDraw.class.php");
    include(dirname(__FILE__)."/pCashe/pImage.class.php");
    include(dirname(__FILE__)."/pCashe/pData.class.php");

//echo "\n\r<br>Начало цикла перебора\n\r";
if(isset($_GET["date"])) $date=$_GET["date"];
else $date = '2020-01-09';

 $conn = new mysqli("portal.yugkabel.ru", "portal", "portal", "ostatki", 3301);
  if ($conn->connect_error)     die("Ошибка: не удается подключиться: " . $conn->connect_error);

$query_list="SELECT d FROM products WHERE 1 GROUP BY d DESC;";
$res = $conn -> query($query_list);
echo "<form><select name=date onChange='this.form.submit()'>";
while($result = $res -> fetch_assoc()){
	if ($result["d"] == $date) $selected=" selected";
	else $selected = "";
	echo "<option value=".$result["d"].$selected.">".$result["d"]."</option>";
}
echo "</select></form>";

$query="SELECT * FROM price, products WHERE price.code = products.code AND products.d='".$date."';";
//echo $query;

  $result = $conn->query($query);
//$result2 =  $conn->query($query_price);
//if($result){
//    print 'Success! Total ' .$mysqli->affected_rows .' rows added.<br />';
//}else{
//    die('Error : ('. $mysqli->errno .') '. $mysqli->error);
//}
//if($result2) print 'Таблица price обновлена';

  echo "Количество строк:". $result->num_rows;
//$total=array();
while($res = $result -> fetch_assoc()){
	$total[0] += $res["s0"]*$res["price"];
	$total[1] += $res["s1"]*$res["price"];
	$total[2] += $res["s2"]*$res["price"];
	$total[3] += $res["s3"]*$res["price"];
	$total[4] += $res["s4"]*$res["price"];
	$total[5] += $res["s5"]*$res["price"];
	$total[6] += $res["s6"]*$res["price"];
	$total[7] += $res["s7"]*$res["price"];
	$total[8] += $res["s8"]*$res["price"];
	$total[9] += $res["s9"]*$res["price"];
}


for($i=0;$i<count($total)-1;$i++)echo "<br>". number_format($total[$i], 2, ',', ' ');
echo "<br>Сумма: ".number_format(array_sum($total), 2, ',', ' ');

$myData = new pData();
$q = "SELECT d FROM `products` WHERE 1 GROUP BY d ASC";
$res = $conn -> query($q);
while ($result = $res -> fetch_assoc()){
	$q2 = "SELECT * FROM price, products WHERE price.code = products.code AND products.d='".$result['d']."';";
	$r2 = $conn -> query($q2);
	$total = array();
	while($res2 = $r2 -> fetch_assoc()){
		$total[0] += $res2["s0"]*$res2["price"];
		$total[1] += $res2["s1"]*$res2["price"];
		$total[2] += $res2["s2"]*$res2["price"];
		$total[3] += $res2["s3"]*$res2["price"];
		$total[4] += $res2["s4"]*$res2["price"];
		$total[5] += $res2["s5"]*$res2["price"];
		$total[6] += $res2["s6"]*$res2["price"];
		$total[7] += $res2["s7"]*$res2["price"];
		$total[8] += $res2["s8"]*$res2["price"];
		$total[9] += $res2["s9"]*$res2["price"];
	}
	//	echo "<br>".array_sum($total);
	$myData -> addPoints(array_sum($total)/1000000, "Total");
	//	echo "<br>".array_sum($total)/1000000;
	$myData -> addPoints($result['d'],"Labels");
}
$unique = date("Y.m.d_H.i");
$gsFilename_Traffic = "traffic_".$unique.".png";

$myData->setSerieDescription("Labels","Days");
$myData->setAbscissa("Labels");
$myData->setAxisUnit(0,"Млн");

$serieSettings = array("R"=>229,"G"=>11,"B"=>11,"Alpha"=>100);
//$myData->setPalette("Total",$serieSettings);

$myPicture = new pImage(1350,400,$myData); // <-- Размер холста
$myPicture->setFontProperties(array("FontName"=>"fonts/calibri.ttf","FontSize"=>8));
$myPicture->setGraphArea(50,20,1230,380); // <-- Размещение графика на холсте
$myPicture->drawScale();
$myPicture->drawBestFit(array("Alpha"=>40)); // <-- Прямая статистики

$myPicture->drawLineChart();
$myPicture->drawPlotChart(array("DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>0,"Surrounding"=>-60,"BorderAlpha"=>50)); // <-- Точки на графике
$myPicture->drawLegend(700,10,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));// <-- Размещение легенды
$myPicture->Render($gsFilename_Traffic);

echo"<br><img src='".$gsFilename_Traffic."' />";
$conn->close();
?>
